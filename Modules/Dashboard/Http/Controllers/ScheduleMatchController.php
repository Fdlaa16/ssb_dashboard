<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Models\ScheduleMatch;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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

        $scheduleMatchs = ScheduleMatch::query()
            ->with([
                'firstClub',
                'secoundClub',
                'stadium'
            ])
            ->when(!empty($request->search), function ($q) use ($request) {
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
            })
            ->when($request->status ?? null, function ($query, $status) {
                if ($status == 0) {
                    $query->whereNotNull('deleted_at');
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->orderBy($orderByColumn, $orderByDirection)
            ->latest();

        if ($request->has('from_date') && !empty($request->from_date)) {
            $scheduleMatchs->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $scheduleMatchs->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $scheduleMatchs = $scheduleMatchs->paginate(10);

        return $scheduleMatchs;
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
        return view('dashboard::edit');
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
