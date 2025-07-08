<?php

namespace Modules\Company\Http\Controllers;

use App\Helpers\Helper;
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
    public function nearestMatches(Request $request)
    {
        $orderByColumn = 'schedule_date';
        $orderByDirection = 'asc';

        if ($request->has('sort')) {
            $orderByDirection = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        }

        $jakartaTime = now()->setTimezone('Asia/Jakarta');

        $scheduleMatchQuery = ScheduleMatch::query()
            ->with([
                'firstClub.profile_club',
                'secoundClub.profile_club',
                'stadium'
            ])
            ->whereNull('deleted_at')
            ->whereRaw("CONCAT(schedule_date, ' ', schedule_start_at) > ?", [$jakartaTime->format('Y-m-d H:i:s')])
            ->orderBy('schedule_date', 'asc')
            ->orderBy('schedule_start_at', 'asc')
            ->limit(3);

        $scheduleMatchQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($query) use ($request) {
                $query->where('created_at', 'like', '%' . $request->search . '%')
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

        if (!$request->has('sort')) {
            $scheduleMatchQuery->orderBy('schedule_date', 'asc')
                ->orderBy('schedule_start_at', 'asc');
        } else {
            $scheduleMatchQuery->orderBy($orderByColumn, $orderByDirection);
        }

        $scheduleMatch = $scheduleMatchQuery->get();

        $statusCounts = DB::table('schedule_matches')
            ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) as in_active,
            SUM(CASE 
                WHEN deleted_at IS NULL AND CONCAT(schedule_date, " ", schedule_start_at) > ? 
                THEN 1 ELSE 0 
            END) as upcoming,
            SUM(CASE 
                WHEN deleted_at IS NULL AND CONCAT(schedule_date, " ", schedule_start_at) <= ? 
                THEN 1 ELSE 0 
            END) as previous
        ', [$jakartaTime->format('Y-m-d H:i:s'), $jakartaTime->format('Y-m-d H:i:s')])
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
            'upcoming' => (int) $statusCounts->upcoming,
            'previous' => (int) $statusCounts->previous,
        ];

        return response()->json([
            'data' => $scheduleMatch,
            'totals' => $responseTotals,
        ]);
    }

    public function listMatches(Request $request)
    {
        $orderByColumn = 'created_at';
        $orderByDirection = 'desc';

        if ($request->has('sort')) {
            $orderByDirection = $request->input('sort') === 'asc' ? 'asc' : 'desc';
        }

        $scheduleMatchQuery = ScheduleMatch::query();

        $jakartaTime = now()->setTimezone('Asia/Jakarta');

        if ($request->status === 'upcoming') {
            $scheduleMatchQuery->where(function ($query) use ($jakartaTime) {
                $query->whereRaw("CONCAT(schedule_date, ' ', schedule_start_at) > ?", [$jakartaTime->format('Y-m-d H:i:s')]);
            });
        } elseif ($request->status === 'previous') {
            $scheduleMatchQuery->where(function ($query) use ($jakartaTime) {
                $query->whereRaw("CONCAT(schedule_date, ' ', schedule_start_at) <= ?", [$jakartaTime->format('Y-m-d H:i:s')]);
            });
        }

        $scheduleMatchQuery->with([
            'firstClub.profile_club',
            'secoundClub.profile_club',
            'stadium'
        ]);

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
            SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) as in_active,
            SUM(CASE 
                WHEN deleted_at IS NULL AND CONCAT(schedule_date, " ", schedule_start_at) > ? 
                THEN 1 ELSE 0 
            END) as upcoming,
            SUM(CASE 
                WHEN deleted_at IS NULL AND CONCAT(schedule_date, " ", schedule_start_at) <= ? 
                THEN 1 ELSE 0 
            END) as previous
        ', [$jakartaTime->format('Y-m-d H:i:s'), $jakartaTime->format('Y-m-d H:i:s')])
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
            'upcoming' => (int) $statusCounts->upcoming,
            'previous' => (int) $statusCounts->previous,
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
        return view('company::create');
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
        return view('company::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('company::edit');
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
        //
    }
}
