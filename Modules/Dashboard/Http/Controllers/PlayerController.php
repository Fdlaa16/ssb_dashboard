<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Resources\PlayerResource;
use App\Http\Resources\PlayerResources;
use App\Http\Resources\SportResource;
use App\Models\Club;
use App\Models\File;
use App\Models\Player;
use App\Models\Sport;
use App\Models\SportPlayer;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
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
        $sports = Sport::select('id', 'code', 'name')->get();

        $data = [
            'sports' => SportResource::collection($sports),
        ];

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $postData = $request->all();
        $rules = [
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'nisn' => 'required|unique:players,nisn',
            'name' => 'required',
            'height' => 'required',
            'weight' => 'required',
        ];

        $messages = [
            'nisn.required' => 'NISN harus diisi.',
            'nisn.unique' => 'NISN harus kode unik.',
            'name.required' => 'Name harus diisi.',
            'height.required' => 'Height harus diisi.',
            'weight.required' => 'Weight harus diisi.',
        ];

        $validator = Validator::make($postData, $rules, $messages);

        if ($validator->fails()) {
            return response()->json(array('errors' => $validator->messages()->toArray()), 422);
        } else {
            $user = new User();
            $user = $user->create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $player = new Player();
            $player = $player->create([
                'user_id' => $user->id,
                'nisn' => $request->nisn,
                'name' => $request->name,
                'height' => $request->height,
                'weight' => $request->weight,
            ]);

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');

                $fileObj = new File();
                $fileDir = $fileObj->getDirectory('avatar');
                $fileName = $fileObj->getFileName('avatar', $player->id, $file);

                $file->storeAs("public/$fileDir", $fileName);

                $player->files()->where('type', 'avatar')->delete();
                $player->files()->create([
                    'type' => 'avatar',
                    'name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'path' => "$fileDir$fileName",
                ]);
            }

            if ($request->hasFile('family_card')) {
                $file = $request->file('family_card');

                $fileObj = new File();
                $fileDir = $fileObj->getDirectory('family_card');
                $fileName = $fileObj->getFileName('family_card', $player->id, $file);

                $file->storeAs("public/$fileDir", $fileName);

                $player->files()->where('type', 'family_card')->delete();
                $player->files()->create([
                    'type' => 'family_card',
                    'name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'path' => "$fileDir$fileName",
                ]);
            }

            if ($request->hasFile('report_grades')) {
                $file = $request->file('report_grades');

                $fileObj = new File();
                $fileDir = $fileObj->getDirectory('report_grades');
                $fileName = $fileObj->getFileName('report_grades', $player->id, $file);

                $file->storeAs("public/$fileDir", $fileName);
                $player->files()->where('type', 'report_grades')->delete();

                $player->files()->create([
                    'type' => 'report_grades',
                    'name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'path' => "$fileDir$fileName",
                ]);
            }

            if ($request->hasFile('birth_certificate')) {
                $file = $request->file('birth_certificate');

                $fileObj = new File();
                $fileDir = $fileObj->getDirectory('birth_certificate');
                $fileName = $fileObj->getFileName('birth_certificate', $player->id, $file);

                $file->storeAs("public/$fileDir", $fileName);
                $player->files()->where('type', 'birth_certificate')->delete();

                $player->files()->create([
                    'type' => 'birth_certificate',
                    'name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'path' => "$fileDir$fileName",
                ]);
            }

            $sportPlayers = json_decode($request->sport_players, true);
            foreach ($sportPlayers as $sportPlayerData) {
                $player->sportPlayers()->create([
                    'sport_id' => $sportPlayerData['sport']['id'],
                ]);
            }

            return response()->json([
                'message' => 'Player created successfully.',
                'data' => $player->load('sportPlayers.sport')
            ], 201);
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
        $players = Player::query()
            ->with([
                'user',
                'clubs',
                'sportPlayers',
                'avatar',
                'birth_certificate',
                'family_card',
                'report_grades',
            ])
            ->find($id);

        $sports = Sport::query()
            ->select('id', 'name')
            ->get();

        $clubs = Club::query()
            ->select('id', 'name')
            ->get();

        $data = [
            'data' => $players,
            'sports' => $sports,
            'clubs' => $clubs,
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
        //
    }
}
