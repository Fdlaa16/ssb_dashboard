<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\ScheduleMatchResource;
use App\Models\ScheduleMatch;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

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

        $scheduleMatchQuery->when($request->status ?? null, function ($query, $status) {
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
        //
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
        //
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
}
