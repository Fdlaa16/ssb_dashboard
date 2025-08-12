<?php

namespace Modules\Company\Http\Controllers;

use App\Models\File;
use App\Models\Player;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function apiLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = auth()->user();

            // Optional: Check if user has company access
            if (!in_array($user->role, ['user'])) {
                Auth::logout();
                return response()->json([
                    'message' => 'Unauthorized access to company portal',
                ], 403);
            }

            // Revoke existing tokens
            $user->tokens()->delete();

            // Create new token with company scope
            $token = $user->createToken('company_auth_token', ['company'])->plainTextToken;

            return response()->json([
                'message' => 'Company login berhasil',
                'role' => $user->role,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'login_type' => 'company'
            ], 200);
        }

        return response()->json([
            'message' => 'Company login gagal, data tidak ditemukan atau salah',
        ], 401);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Company logout berhasil'
        ], 200);
    }

    public function register(Request $request)
    {
        DB::beginTransaction();

        try {
            $postData = $request->all();
            $rules = [
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required',
                'nisn'      => 'required|unique:players,nisn',
                'name'      => 'required',
                'height'    => 'required',
                'weight'    => 'required',
                // 'club_id'   => 'required',
                'category'  => 'required',
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
                'name.required'      => 'Nama harus diisi',
                'height.required'    => 'Tinggi badan harus diisi',
                'weight.required'    => 'Berat badan harus diisi',
                // 'club_id.required'   => 'Club harus diisi',
                // 'club_id.exists'     => 'Club tidak ditemukan',
                'category.required'  => 'Kategori harus diisi',
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
                    'password' => bcrypt($request->password) ?? '',
                    'role' => 'user',
                ]);

                $player = Player::create([
                    'user_id' => $user->id ?? '',
                    'nisn' => $request->nisn ?? '',
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

    public function profile(Request $request)
    {
        $user = Auth::user();
        $players = Player::where('user_id', $user->id)
            ->with(['family_card', 'report_grades', 'birth_certificate', 'avatar', 'user'])
            ->get();

        return response()->json([
            'data' => $players->first(),
        ]);
    }

    public function profileUpdate(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $player = Player::where('user_id', $user->id)
            ->with(['family_card', 'report_grades', 'birth_certificate', 'avatar', 'user'])
            ->first();

        if (!$player) {
            return response()->json(['message' => 'Player tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'email'     => 'required|email|unique:users,email,' . $player->user_id,
                'nisn'      => 'required|unique:players,nisn,' . $player->id,
                'name'      => 'required',
                'height'    => 'required',
                'weight'    => 'required',
                // 'club_id'   => 'required',
                'category'  => 'required',
                'position'  => 'required',
                'avatar.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'family_card.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'report_grades.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
                'birth_certificate.*' => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'email.required'     => 'Email harus diisi',
                'email.email'        => 'Format email tidak valid',
                'email.unique'       => 'Email sudah digunakan',
                'nisn.required'      => 'NISN harus diisi',
                'nisn.unique'        => 'NISN sudah digunakan',
                'name.required'      => 'Nama harus diisi',
                'height.required'    => 'Tinggi badan harus diisi',
                'weight.required'    => 'Berat badan harus diisi',
                // 'club_id.required'   => 'Club harus diisi',
                // 'club_id.exists'     => 'Club tidak ditemukan',
                'category.required'  => 'Kategori harus diisi',
                'position.required'  => 'Posisi harus diisi',
                'avatar.*.image' => 'Avatar harus berupa gambar',
                'avatar.*.mimes' => 'Format Avatar harus jpeg, png, jpg, atau bmp',
                'avatar.*.max' => 'Ukuran Avatar maksimal 2MB',
                'family_card.*.image' => 'Kartu Keluarga harus berupa gambar',
                'family_card.*.mimes' => 'Format Kartu Keluarga harus jpeg, png, jpg, atau bmp',
                'family_card.*.max' => 'Ukuran Kartu Keluarga maksimal 2MB',
                'report_grades.*.image' => 'Nilai Rapot harus berupa gambar',
                'report_grades.*.mimes' => 'Format Nilai Rapot harus jpeg, png, jpg, atau bmp',
                'report_grades.*.max' => 'Ukuran Nilai Rapot maksimal 2MB',
                'birth_certificate.*.image' => 'Akte Kelahiran harus berupa gambar',
                'birth_certificate.*.mimes' => 'Format Akte Kelahiran harus jpeg, png, jpg, atau bmp',
                'birth_certificate.*.max' => 'Ukuran Akte Kelahiran maksimal 2MB',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(array('errors' => $validator->messages()->toArray()), 422);
            } else {

                $updateData = [
                    'email' => $request->email,
                ];

                if (!empty($request->new_password)) {
                    $updateData['password'] = bcrypt($request->new_password);
                }

                $player->user()->update($updateData);

                $player->update([
                    'nisn'   => $request->nisn ?? '',
                    'name'   => $request->name ?? '',
                    'height' => $request->height ?? '',
                    'weight' => $request->weight ?? '',
                    'position' => $request->position ?? '',
                    'back_number' => $request->back_number ?? '',
                    'category' => $request->category ?? '',
                ]);

                $types = ['avatar', 'family_card', 'report_grades', 'birth_certificate'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $player->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $player->files()->where('type', $type)->delete();

                        $player->files()->create([
                            'type'           => $type,
                            'name'           => $fileName,
                            'original_name'  => $file->getClientOriginalName(),
                            'extension'      => $file->getClientOriginalExtension(),
                            'path'           => $fileDir . $fileName,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Player updated successfully.',
                    'data' => $player,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui player.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return view('company::index');
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
