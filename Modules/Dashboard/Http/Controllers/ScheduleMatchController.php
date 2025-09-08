<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\ScheduleMatchResource;
use App\Models\ScheduleMatch;
use App\Models\Standing;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScheduleMatchController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = 'desc';

        if ($request->has('sort')) {
            $orderByDirection = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        }

        $scheduleMatchQuery = ScheduleMatch::query()
            ->with([
                'firstClub',
                'secoundClub',
                'stadium'
            ])
            ->withTrashed();

        $scheduleMatchQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where('created_at', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_date', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_start_at', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_end_at', 'like', '%' . $request->search . '%')
                ->orWhereHas('firstClub', function ($user) use ($request) {
                    $user->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('secoundClub', function ($user) use ($request) {
                    $user->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('stadium', function ($user) use ($request) {
                    $user->where('name', 'like', '%' . $request->search . '%');
                });
        });

        $scheduleMatchQuery->when($request->status, function ($query, $status) {
            switch ($status) {
                case 'in_active':
                    $query->onlyTrashed();
                    break;
                case 'active':
                    $query->whereNull('deleted_at');
                    break;
                case 'all':
                default:
                    break;
            }
        });

        $scheduleMatchQuery->when($request->club_id, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('firstClub', function ($q1) use ($request) {
                    $q1->where('id', $request->club_id);
                })->orWhereHas('secoundClub', function ($q2) use ($request) {
                    $q2->where('id', $request->club_id);
                });
            });
        });

        $scheduleMatchQuery->when($request->stadium_id, function ($query) use ($request) {
            $query->whereHas('stadium', function ($q) use ($request) {
                $q->where('id', $request->stadium_id);
            });
        });

        $scheduleMatchQuery->orderBy($orderByColumn, $orderByDirection);

        $scheduleMatch = $scheduleMatchQuery->get();

        $statusCounts = DB::table('schedule_matches')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) as in_active
            ')
            ->when($request->filled('from_date'), function ($q) use ($request) {
                $q->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
            })
            ->when($request->filled('to_date'), function ($q) use ($request) {
                $q->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
            })
            ->first();

        $responseTotals = [
            'all' => (int) $statusCounts->total,
            'active' => (int) $statusCounts->active,
            'in_active' => (int) $statusCounts->in_active,
        ];

        return response()->json([
            'data' => $scheduleMatch,
            'totals' => $responseTotals,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('dashboard::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $postData = $request->all();
            $rules = [
                'first_club_id'     => 'required',
                'secound_club_id'   => 'required',
                'stadium_id'        => 'required',
                'schedule_date'     => 'required',
                'schedule_start_at' => 'required',
                'schedule_end_at'   => 'required',
            ];

            $messages = [
                'first_club_id.required'     => 'Club pertama harus diisi',
                'secound_club_id.required'   => 'Club kedua harus diisi',
                'stadium_id.required'        => 'Stadium harus diisi',
                'schedule_date.required'     => 'Schedule Date harus diisi',
                'schedule_start_at.required' => 'Schedule Start At harus diisi',
                'schedule_end_at.required'   => 'Schedule End At harus diisi',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $scheduleMatch = ScheduleMatch::create([
                    'first_club_id'     => $request->first_club_id ?? '',
                    'secound_club_id'   => $request->secound_club_id ?? '',
                    'stadium_id'        => $request->stadium_id ?? '',
                    'schedule_date'     => $request->schedule_date ? Carbon::parse($request->schedule_date)->format('Y-m-d') : null,
                    'schedule_start_at' => $request->schedule_start_at ? Carbon::parse($request->schedule_start_at)->format('H:i:s') : null,
                    'schedule_end_at'   => $request->schedule_end_at ? Carbon::parse($request->schedule_end_at)->format('H:i:s') : null,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Schedule Match created successfully.',
                    'data' => $scheduleMatch,
                ], 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('dashboard::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $schduleMatchs = ScheduleMatch::query()
            ->with([
                'firstClub',
                'secoundClub',
                'stadium'
            ])
            ->find($id);

        $data = [
            'data' => $schduleMatchs,
        ];

        return $data;
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $postData = $request->all();
            $rules = [
                'first_club_id'         => 'required',
                'secound_club_id'       => 'required',
                'stadium_id'            => 'required',
                'schedule_date'         => 'required',
                'schedule_start_at'     => 'required',
                'schedule_end_at'       => 'required',
                'first_club_score'      => 'nullable',
                'secound_club_score'    => 'nullable',
                'status'                => 'nullable',
            ];

            $messages = [
                'first_club_id.required'     => 'Club pertama harus diisi',
                'secound_club_id.required'   => 'Club kedua harus diisi',
                'stadium_id.required'        => 'Stadium harus diisi',
                'schedule_date.required'     => 'Schedule Date harus diisi',
                'schedule_start_at.required' => 'Schedule Start At harus diisi',
                'schedule_end_at.required'   => 'Schedule End At harus diisi',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()], 422);
            } else {

                $scheduleMatch = ScheduleMatch::findOrFail($id);

                $scheduleMatch->update([
                    'first_club_id'         => $request->first_club_id ?? '',
                    'secound_club_id'       => $request->secound_club_id ?? '',
                    'stadium_id'            => $request->stadium_id ?? '',
                    'schedule_date'         => Carbon::parse($request->schedule_date)->format('Y-m-d'),
                    'schedule_start_at'     => Carbon::parse($request->schedule_start_at)->format('H:i:s'),
                    'schedule_end_at'       => Carbon::parse($request->schedule_end_at)->format('H:i:s'),
                    'first_club_score'      => $request->first_club_score ?? '',
                    'secound_club_score'    => $request->secound_club_score ?? '',
                    'status'                => $request->status ?? '',
                ]);

                $firstScore  = (int) $request->first_club_score;
                $secondScore = (int) $request->secound_club_score;

                if (!is_null($firstScore) && !is_null($secondScore)) {
                    $firstStanding = Standing::firstOrCreate(
                        ['club_id' => $request->first_club_id],
                        [
                            'total' => 0,
                            'win' => 0,
                            'draw' => 0,
                            'lose' => 0,
                            'goal_in' => 0,
                            'goal_conceded' => 0,
                            'goal_difference' => 0,
                            'points' => 0
                        ]
                    );

                    $secondStanding = Standing::firstOrCreate(
                        ['club_id' => $request->secound_club_id],
                        [
                            'total' => 0,
                            'win' => 0,
                            'draw' => 0,
                            'lose' => 0,
                            'goal_in' => 0,
                            'goal_conceded' => 0,
                            'goal_difference' => 0,
                            'points' => 0
                        ]
                    );

                    $firstStanding->total += 1;
                    $secondStanding->total += 1;

                    $firstStanding->goal_in += $firstScore;
                    $firstStanding->goal_conceded += $secondScore;

                    $secondStanding->goal_in += $secondScore;
                    $secondStanding->goal_conceded += $firstScore;

                    $firstStanding->goal_difference = $firstStanding->goal_in - $firstStanding->goal_conceded;
                    $secondStanding->goal_difference = $secondStanding->goal_in - $secondStanding->goal_conceded;

                    if ($firstScore > $secondScore) {
                        $firstStanding->win += 1;
                        $firstStanding->points += 3;

                        $secondStanding->lose += 1;
                    } elseif ($firstScore < $secondScore) {
                        $secondStanding->win += 1;
                        $secondStanding->points += 3;

                        $firstStanding->lose += 1;
                    } else {
                        $firstStanding->draw += 1;
                        $firstStanding->points += 1;

                        $secondStanding->draw += 1;
                        $secondStanding->points += 1;
                    }

                    $firstStanding->save();
                    $secondStanding->save();
                }

                DB::commit();

                return response()->json([
                    'message' => 'Schedule Match updated successfully.',
                    'data' => $scheduleMatch,
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $scheduleMatch = ScheduleMatch::withTrashed()->find($id);

        if (!$scheduleMatch) {
            return response()->json(['message' => 'Schedule Match tidak ditemukan'], 404);
        }

        $scheduleMatch->delete();

        return response()->json([
            'message' => 'Schedule Match berhasil dihapus.',
            'data' => new ScheduleMatchResource($scheduleMatch),
        ]);
    }

    public function active(Request $request, $id)
    {
        $scheduleMatch = ScheduleMatch::withTrashed()->find($id);

        if (!$scheduleMatch) {
            return response()->json(['message' => 'Schedule Match tidak ditemukan'], 404);
        }

        $scheduleMatch->restore();

        return response()->json([
            'message' => 'Schedule Match berhasil diaktifkan.',
            'data' => new ScheduleMatchResource($scheduleMatch),
        ]);
    }

    public function export(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';

        $scheduleMatchQuery = ScheduleMatch::query()
            ->with(['firstClub', 'secoundClub', 'stadium'])
            ->withTrashed();

        // filter sama persis dengan index
        $scheduleMatchQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where('created_at', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_date', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_start_at', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_end_at', 'like', '%' . $request->search . '%')
                ->orWhereHas('firstClub', function ($club) use ($request) {
                    $club->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('secoundClub', function ($club) use ($request) {
                    $club->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('stadium', function ($stadium) use ($request) {
                    $stadium->where('name', 'like', '%' . $request->search . '%');
                });
        });

        $scheduleMatchQuery->when($request->status, function ($query, $status) {
            switch ($status) {
                case 'in_active':
                    $query->onlyTrashed();
                    break;
                case 'active':
                    $query->whereNull('deleted_at');
                    break;
            }
        });

        $scheduleMatchQuery->when($request->club_id, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('firstClub', function ($q1) use ($request) {
                    $q1->where('id', $request->club_id);
                })->orWhereHas('secoundClub', function ($q2) use ($request) {
                    $q2->where('id', $request->club_id);
                });
            });
        });

        $scheduleMatchQuery->when($request->stadium_id, function ($query) use ($request) {
            $query->whereHas('stadium', function ($q) use ($request) {
                $q->where('id', $request->stadium_id);
            });
        });

        if ($request->filled('from_date')) {
            $scheduleMatchQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }
        if ($request->filled('to_date')) {
            $scheduleMatchQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $scheduleMatchQuery->orderBy($orderByColumn, $orderByDirection);

        // --- Excel Export ---
        $exportDir = 'ScheduleMatchExport';
        if (!is_dir(Storage::disk('public')->path($exportDir))) {
            mkdir(Storage::disk('public')->path($exportDir), 0775, true);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $headers = [
            'A1' => 'Tanggal',
            'B1' => 'Jam Mulai',
            'C1' => 'Jam Selesai',
            'D1' => 'Club Pertama',
            'E1' => 'Club Kedua',
            'F1' => 'Stadium',
            'G1' => 'Skor',
            'H1' => 'Status',
        ];
        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        $statusMap = [
            'not_started'  => 'Belum Dimulai',
            'in_progress'  => 'Sedang Berlangsung',
            'finished'     => 'Selesai',
        ];

        // Isi data
        $row = 2;
        $scheduleMatchQuery->chunk(1000, function ($matches) use ($sheet, &$row, $statusMap) {
            foreach ($matches as $match) {
                $statusTitle = $statusMap[$match->status] ?? $match->status; // fallback kalau tidak ada di mapping

                $sheet->setCellValue("A{$row}", $match->schedule_date);
                $sheet->setCellValue("B{$row}", $match->schedule_start_at);
                $sheet->setCellValue("C{$row}", $match->schedule_end_at);
                $sheet->setCellValue("D{$row}", $match->firstClub->name ?? '-');
                $sheet->setCellValue("E{$row}", $match->secoundClub->name ?? '-');
                $sheet->setCellValue("F{$row}", $match->stadium->name ?? '-');
                $sheet->setCellValue("G{$row}", ($match->first_club_score ?? 0) . " - " . ($match->secound_club_score ?? 0));
                $sheet->setCellValue("H{$row}", $statusTitle); // tampilkan judul status

                $row++;
            }
        });

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "schedule_match_export_{$timestamp}.xlsx";
        $filePath = Storage::disk('public')->path($exportDir . '/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend();
    }
}
