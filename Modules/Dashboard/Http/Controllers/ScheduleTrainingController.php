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
use Illuminate\Support\Facades\Validator;

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
                ->orWhere('schedule_end_at', 'like', '%' . $request->search . '%')
                ->orWhere('score', 'like', '%' . $request->search . '%')
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

        $scheduleTrainingQuery->when($request->status ?? null, function ($query, $status) {
            switch ($status) {
                case 'in_confirm':
                    $query->where('status', 0);
                    break;
                case 'active':
                    $query->where('status', 1);
                    break;
                case 'in_active':
                    $query->where('status', 2);
                    break;
                case 'all':
                default:
                    break;
            }
        });

        $scheduleTrainingQuery->when($request->club_id, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('firstClub', function ($q1) use ($request) {
                    $q1->where('id', $request->club_id);
                })->orWhereHas('secoundClub', function ($q2) use ($request) {
                    $q2->where('id', $request->club_id);
                });
            });
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
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as in_active
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
                'first_club_id'     => 'required',
                'secound_club_id'   => 'required',
                'stadium_id'        => 'required',
                'schedule_date'     => 'required',
                'schedule_start_at' => 'required',
                'schedule_end_at'   => 'required',
                'score'             => 'nullable',
                'status'            => 'nullable',
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
                $scheduleTraining = ScheduleTraining::create([
                    'first_club_id'     => $request->first_club_id,
                    'secound_club_id'   => $request->secound_club_id,
                    'stadium_id'        => $request->stadium_id,
                    'schedule_date'     => Carbon::parse($request->schedule_date)->format('Y-m-d'),
                    'schedule_start_at' => Carbon::parse($request->schedule_start_at)->format('H:i:s'),
                    'schedule_end_at'   => Carbon::parse($request->schedule_end_at)->format('H:i:s'),
                    'score'             => $request->score,
                    'status'            => $request->status ?? 0,
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
                'firstClub',
                'secoundClub',
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
                'first_club_id'     => 'required',
                'secound_club_id'   => 'required',
                'stadium_id'        => 'required',
                'schedule_date'     => 'required',
                'schedule_start_at' => 'required',
                'schedule_end_at'   => 'required',
                'score'             => 'nullable',
                'status'            => 'nullable',
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
            }

            $scheduleTraining = ScheduleTraining::findOrFail($id);

            $scheduleTraining->update([
                'first_club_id'     => $request->first_club_id,
                'secound_club_id'   => $request->secound_club_id,
                'stadium_id'        => $request->stadium_id,
                'schedule_date'     => \Carbon\Carbon::parse($request->schedule_date)->format('Y-m-d'),
                'schedule_start_at' => \Carbon\Carbon::parse($request->schedule_start_at)->format('H:i:s'),
                'schedule_end_at'   => \Carbon\Carbon::parse($request->schedule_end_at)->format('H:i:s'),
                'score'             => $request->score,
                'status'            => $request->status ?? 0,
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
}
