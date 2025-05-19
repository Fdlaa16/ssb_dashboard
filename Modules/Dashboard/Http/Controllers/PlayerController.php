<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Player;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;

class PlayerController extends Controller
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

        $players = Player::query()
            ->with([
                'user',
                'clubs',
                'sports',
            ])
            ->when(!empty($request->search), function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nisn', 'like', '%' . $request->search . '%')
                    ->orWhere('height', 'like', '%' . $request->search . '%')
                    ->orWhere('weight', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($user) use ($request) {
                        $user->where('email', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('clubs', function ($club) use ($request) {
                        $club->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('sports', function ($sport) use ($request) {
                        $sport->where('name', 'like', '%' . $request->search . '%');
                    });
            })
            ->when($request->status ?? null, function ($query, $status) {
                if ($status == 0) {
                    $query->onlyTrashed();
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->when($request->club_id, function ($query) use ($request) {
                $query->whereHas('clubs', function ($q) use ($request) {
                    $q->where('id', $request->club_id);
                });
            })
            ->when($request->sport_id, function ($query) use ($request) {
                $query->whereHas('sports', function ($q) use ($request) {
                    $q->where('id', $request->sport_id);
                });
            })
            ->orderBy($orderByColumn, $orderByDirection)
            ->withTrashed();

        if ($request->has('from_date') && !empty($request->from_date)) {
            $players->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }
        if ($request->has('to_date') && !empty($request->to_date)) {
            $players->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $players = $players->get();

        return response()->json([
            'data' => $players,
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
