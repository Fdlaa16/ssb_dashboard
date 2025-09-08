<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\ScheduleTrainingResource;
use App\Models\ScheduleTraining;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ScheduleTrainingController extends Controller
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

        $scheduleTrainingQuery = ScheduleTraining::query()
            ->with([
                'firstClub',
                'secoundClub',
                'stadium'
            ])
            ->withTrashed();

        $scheduleTrainingQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where('created_at', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_date', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_start_at', 'like', '%' . $request->search . '%')
                ->orWhereHas('stadium', function ($user) use ($request) {
                    $user->where('name', 'like', '%' . $request->search . '%');
                });
        });

        $scheduleTrainingQuery->when($request->status, function ($query, $status) {
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

        $scheduleTrainingQuery->when($request->stadium_id, function ($query) use ($request) {
            $query->whereHas('stadium', function ($q) use ($request) {
                $q->where('id', $request->stadium_id);
            });
        });

        $scheduleTrainingQuery->orderBy($orderByColumn, $orderByDirection);

        $scheduleTraining = $scheduleTrainingQuery->get();

        $statusCounts = DB::table('schedule_trainings')
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
            'data' => $scheduleTraining,
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
                // 'first_club_id'     => 'required',
                // 'secound_club_id'   => 'required',
                'stadium_id'        => 'required',
                'schedule_date'     => 'required',
                'schedule_start_at' => 'required',
                // 'schedule_end_at'   => 'required',
                // 'score'             => 'nullable',
            ];

            $messages = [
                // 'first_club_id.required'     => 'Club pertama harus diisi',
                // 'secound_club_id.required'   => 'Club kedua harus diisi',
                'stadium_id.required'        => 'Stadium harus diisi',
                'schedule_date.required'     => 'Schedule Date harus diisi',
                'schedule_start_at.required' => 'Schedule Start At harus diisi',
                // 'schedule_end_at.required'   => 'Schedule End At harus diisi',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $scheduleTraining = ScheduleTraining::create([
                    // 'first_club_id'     => $request->first_club_id,
                    // 'secound_club_id'   => $request->secound_club_id,
                    'stadium_id'        => $request->stadium_id,
                    'schedule_date'     => Carbon::parse($request->schedule_date)->format('Y-m-d'),
                    'schedule_start_at' => Carbon::parse($request->schedule_start_at)->format('H:i:s'),
                    // 'schedule_end_at'   => Carbon::parse($request->schedule_end_at)->format('H:i:s'),
                    // 'score'             => $request->score,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Schedule Training created successfully.',
                    'data' => $scheduleTraining,
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
        $schduleTrainings = ScheduleTraining::query()
            ->with([
                // 'firstClub',
                // 'secoundClub',
                'stadium'
            ])
            ->find($id);

        $data = [
            'data' => $schduleTrainings,
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
                // 'first_club_id'         => 'required',
                // 'secound_club_id'       => 'required',
                'stadium_id'            => 'required',
                'schedule_date'         => 'required',
                'schedule_start_at'     => 'required',
                // 'schedule_end_at'       => 'required',
                // 'first_club_score'      => 'nullable',
                // 'secound_club_score'    => 'nullable',
                // 'status'                => 'nullable',
            ];

            $messages = [
                // 'first_club_id.required'     => 'Club pertama harus diisi',
                // 'secound_club_id.required'   => 'Club kedua harus diisi',
                'stadium_id.required'        => 'Stadium harus diisi',
                'schedule_date.required'     => 'Schedule Date harus diisi',
                'schedule_start_at.required' => 'Schedule Start At harus diisi',
                // 'schedule_end_at.required'   => 'Schedule End At harus diisi',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->messages()], 422);
            }

            $scheduleTraining = ScheduleTraining::findOrFail($id);

            $scheduleTraining->update([
                // 'first_club_id'         => $request->first_club_id,
                // 'secound_club_id'       => $request->secound_club_id,
                'stadium_id'            => $request->stadium_id,
                'schedule_date'         => $request->schedule_date ? Carbon::parse($request->schedule_date)->format('Y-m-d') : null,
                'schedule_start_at'     => $request->schedule_start_at ? Carbon::parse($request->schedule_start_at)->format('H:i:s') : null,
                // 'schedule_end_at'       => $request->schedule_end_at ? Carbon::parse($request->schedule_end_at)->format('H:i:s') : null,
                // 'first_club_score'      => $request->first_club_score ?? '',
                // 'secound_club_score'    => $request->secound_club_score ?? '',
                // 'status'                => $request->status ?? '',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Schedule Training updated successfully.',
                'data' => $scheduleTraining,
            ], 200);
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
        $scheduleTraining = ScheduleTraining::withTrashed()->find($id);

        if (!$scheduleTraining) {
            return response()->json(['message' => 'Schedule Training tidak ditemukan'], 404);
        }

        $scheduleTraining->delete();

        return response()->json([
            'message' => 'Schedule Training berhasil dihapus.',
            'data' => new ScheduleTrainingResource($scheduleTraining),
        ]);
    }

    public function active(Request $request, $id)
    {
        $scheduleTraining = ScheduleTraining::withTrashed()->find($id);

        if (!$scheduleTraining) {
            return response()->json(['message' => 'Schedule Training tidak ditemukan'], 404);
        }

        $scheduleTraining->restore();

        return response()->json([
            'message' => 'Schedule Training berhasil diaktifkan.',
            'data' => new ScheduleTrainingResource($scheduleTraining),
        ]);
    }

    public function export(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';

        $scheduleTrainingQuery = ScheduleTraining::query()
            ->with(['firstClub', 'secoundClub', 'stadium'])
            ->withTrashed();

        // Filter search
        $scheduleTrainingQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where('created_at', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_date', 'like', '%' . $request->search . '%')
                ->orWhere('schedule_start_at', 'like', '%' . $request->search . '%')
                ->orWhereHas('stadium', function ($stadium) use ($request) {
                    $stadium->where('name', 'like', '%' . $request->search . '%');
                });
        });

        // Filter status (sama dengan index)
        $scheduleTrainingQuery->when($request->status, function ($query, $status) {
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

        // Filter stadium
        $scheduleTrainingQuery->when($request->stadium_id, function ($query) use ($request) {
            $query->whereHas('stadium', function ($q) use ($request) {
                $q->where('id', $request->stadium_id);
            });
        });

        $scheduleTrainingQuery->orderBy($orderByColumn, $orderByDirection);

        // --- Excel Export ---
        $exportDir = 'ScheduleTrainingExport';
        if (!is_dir(Storage::disk('public')->path($exportDir))) {
            mkdir(Storage::disk('public')->path($exportDir), 0775, true);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $headers = [
            'A1' => 'Tanggal',
            'B1' => 'Jam Mulai',
            'C1' => 'Stadium',
            'D1' => 'Status',
        ];
        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        // Isi data
        $row = 2;
        $scheduleTrainingQuery->chunk(1000, function ($matches) use ($sheet, &$row) {
            foreach ($matches as $match) {
                $statusTitle = $match->deleted_at ? 'Tidak Aktif' : 'Aktif';

                $sheet->setCellValue("A{$row}", $match->schedule_date);
                $sheet->setCellValue("B{$row}", $match->schedule_start_at);
                $sheet->setCellValue("C{$row}", $match->stadium->name ?? '-');
                $sheet->setCellValue("D{$row}", $statusTitle);

                $row++;
            }
        });

        $timestamp = now()->format('Y-m-d_H-i-s');
        $fileName = "schedule_training_export_{$timestamp}.xlsx";
        $filePath = Storage::disk('public')->path($exportDir . '/' . $fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend();
    }
}
