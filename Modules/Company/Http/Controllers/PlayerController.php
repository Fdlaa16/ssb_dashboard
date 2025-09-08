<?php

namespace Modules\Company\Http\Controllers;

use App\Helpers\Helper;
use App\Models\File;
use App\Models\Player;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        $playersQuery = Player::query()
            ->where('status', 1)
            ->with([
                'user',
                // 'clubPlayers' => function ($query) {
                //     $query->whereNotNull('category')
                //         ->with('club');
                // },
                'avatar'
            ]);

        $playersQuery->when(!empty($request->search), function ($q) use ($request) {
            $q->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nisn', 'like', '%' . $request->search . '%')
                    ->orWhere('height', 'like', '%' . $request->search . '%')
                    ->orWhere('weight', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($user) use ($request) {
                        $user->where('email', 'like', '%' . $request->search . '%');
                    })
                    // ->orWhereHas('club_players', function ($clubPlayers) use ($request) {
                    //     $clubPlayers->orWhereHas('clubs', function ($club) use ($request) {
                    //         $club->where('name', 'like', '%' . $request->search . '%');
                    //     });
                    // })
                    ->orWhereHas('sports', function ($sport) use ($request) {
                        $sport->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        });

        $playersQuery->when($request->position, function ($query) use ($request) {
            $query->where('position', $request->position);
        });

        $playersQuery->when($request->category, function ($query, $category) {
            $query->where('category', $category);
        });

        $playersQuery->when($request->status, function ($query, $status) {
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

        // $playersQuery->when($request->club_id, function ($query) use ($request) {
        //     $query->whereHas('clubs', function ($q) use ($request) {
        //         $q->where('clubs.id', $request->club_id);
        //     });
        // });

        $playersQuery->when($request->sport_id, function ($query) use ($request) {
            $query->whereHas('sports', function ($q) use ($request) {
                $q->where('id', $request->sport_id);
            });
        });

        if ($request->filled('from_date')) {
            $playersQuery->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
        }

        if ($request->filled('to_date')) {
            $playersQuery->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
        }

        $playersQuery->orderBy($orderByColumn, $orderByDirection);

        $players = $playersQuery->paginate($perPage, ['*'], 'page', $page);

        $statusCounts = DB::table('players')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->when($request->filled('from_date'), function ($q) use ($request) {
                $q->where('created_at', '>=', Helper::formatDate($request->from_date, 'Y-m-d') . ' 00:00:00');
            })
            ->when($request->filled('to_date'), function ($q) use ($request) {
                $q->where('created_at', '<=', Helper::formatDate($request->to_date, 'Y-m-d') . ' 23:59:59');
            })
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalAll = $statusCounts->sum();

        $responseTotals = [
            'all' => $totalAll,
            'active' => $statusCounts[1] ?? 0,
            'in_confirm' => $statusCounts[0] ?? 0,
            'in_active' => $statusCounts[2] ?? 0,
        ];

        return response()->json([
            'data' => $players, // ⬅️ ini adalah `LengthAwarePaginator` bawaan Laravel
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
        DB::beginTransaction();

        try {
            $postData = $request->all();
            $rules = [
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required',
                'nisn'      => 'required|unique:players,nisn',
                'phone'      => 'required|unique:players,phone',
                'name'      => 'required',
                'height'    => 'required',
                'weight'    => 'required',
                // 'club_id'   => 'required',
                'category'  => 'required',
                'position'  => 'required',
                'avatar' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'family_card' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'report_grades' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'birth_certificate' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'email.required'     => 'Email harus diisi',
                'email.email'        => 'Format email tidak valid',
                'email.unique'       => 'Email sudah digunakan',
                'password.required'  => 'Password harus diisi',
                'nisn.required'      => 'NISN harus diisi',
                'nisn.unique'        => 'NISN sudah digunakan',
                'phone.required'     => 'Nomor Telepon harus diisi',
                'phone.unique'       => 'Nomor Telepon sudah digunakan',
                'name.required'      => 'Nama harus diisi',
                'height.required'    => 'Tinggi badan harus diisi',
                'weight.required'    => 'Berat badan harus diisi',
                // 'club_id.required'   => 'Club harus diisi',
                // 'club_id.exists'     => 'Club tidak ditemukan',
                'category.required'  => 'Kategori harus diisi',
                'position.required'  => 'Posisi harus diisi',
                'avatar.image' => 'Avatar harus berupa gambar',
                'avatar.mimes' => 'Format Avatar harus jpeg, png, jpg, atau bmp',
                'avatar.max' => 'Ukuran Avatar maksimal 2MB',
                'family_card.image' => 'Kartu Keluarga harus berupa gambar',
                'family_card.mimes' => 'Format Kartu Keluarga harus jpeg, png, jpg, atau bmp',
                'family_card.max' => 'Ukuran Kartu Keluarga maksimal 2MB',
                'report_grades.image' => 'Nilai Rapot harus berupa gambar',
                'report_grades.mimes' => 'Format Nilai Rapot harus jpeg, png, jpg, atau bmp',
                'report_grades.max' => 'Ukuran Nilai Rapot maksimal 2MB',
                'birth_certificate.image' => 'Akte Kelahiran harus berupa gambar',
                'birth_certificate.mimes' => 'Format Akte Kelahiran harus jpeg, png, jpg, atau bmp',
                'birth_certificate.max' => 'Ukuran Akte Kelahiran maksimal 2MB',
            ];

            $validator = Validator::make($postData, $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {
                $user = User::create([
                    'email' => $request->email ?? '',
                    'password' => bcrypt($request->password) ?? ''
                ]);

                $player = Player::create([
                    'user_id' => $user->id ?? '',
                    'nisn' => $request->nisn ?? '',
                    'phone' => $request->phone ?? '',
                    'name' => $request->name ?? '',
                    'height' => $request->height ?? '',
                    'weight' => $request->weight ?? '',
                    'position' => $request->position ?? '',
                    'back_number' => $request->back_number ?? '',
                    'category' => $request->category ?? '',
                ]);

                $types = ['family_card', 'report_grades', 'birth_certificate'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $player->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');

                        $player->files()->where('type', $type)->delete();

                        $player->files()->create([
                            'type' => $type,
                            'name' => $fileName,
                            'original_name' => $file->getClientOriginalName(),
                            'extension' => $file->getClientOriginalExtension(),
                            'path' => "$fileDir$fileName",
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Player created successfully.',
                    'data' => $player
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
        $players = Player::query()
            ->with([
                'user',
                // 'clubPlayers.club.profile_club',
                'avatar',
                'birth_certificate',
                'family_card',
                'report_grades',
            ])
            ->find($id);

        $data = [
            'data' => $players,
        ];

        return $data;
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
                // 'clubPlayers.club.profile_club',
                'avatar',
                'birth_certificate',
                'family_card',
                'report_grades',
            ])
            ->find($id);

        $data = [
            'data' => $players,
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

    public function passwordUpdate(Request $request, $id)
    {
        DB::beginTransaction();

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        try {
            $request->validate([
                'new_password' => 'required|min:8',
                'confirm_password' => 'required',
            ]);

            $user->update([
                'password' => bcrypt($request->password),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Password updated successfully.',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui password.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
