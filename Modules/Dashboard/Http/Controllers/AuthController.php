<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Models\File;
use App\Models\Structure;
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

            // Tentukan role yang diizinkan dan mapping login_type + scope
            $roleAccess = [
                'admin' => ['login_type' => 'dashboard', 'scope' => 'admin'],
                'user' => ['login_type' => 'company', 'scope' => 'company'],
            ];

            // Cek apakah role user terdaftar di roleAccess
            if (!array_key_exists($user->role, $roleAccess)) {
                Auth::logout();
                return response()->json([
                    'message' => 'Unauthorized access to portal',
                ], 403);
            }

            // Revoke existing tokens
            $user->tokens()->delete();

            // Buat token baru berdasarkan scope
            $scope = [$roleAccess[$user->role]['scope']];
            $token = $user->createToken($roleAccess[$user->role]['login_type'] . '_auth_token', $scope)->plainTextToken;

            return response()->json([
                'message' => ucfirst($roleAccess[$user->role]['login_type']) . ' login berhasil',
                'role' => $user->role,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'login_type' => $roleAccess[$user->role]['login_type']
            ], 200);
        }

        return response()->json([
            'message' => 'Login gagal, email atau password salah',
        ], 401);
    }


    public function apiLogout(Request $request)
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200);
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $structures = Structure::where('user_id', $user->id)
            ->with(['avatar', 'user'])
            ->get();

        return response()->json([
            'data' => $structures->first(),
        ]);
    }

    public function profileUpdate(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $structure = Structure::where('user_id', $user->id)
            ->with(['avatar', 'user'])
            ->first();

        if (!$structure) {
            return response()->json(['message' => 'Structure tidak ditemukan'], 404);
        }

        try {
            // Validasi data
            $rules = [
                'email'                     => 'required|email|unique:users,email,' . $structure->user_id,
                'name'                      => 'required',
                'date_of_birth'             => 'required',
                'department'                => 'required',
                'avatar.*'                  => 'image|mimes:jpeg,png,jpg,bmp|max:2048',
            ];

            $messages = [
                'email.required'            => 'Email harus diisi',
                'email.email'               => 'Format email tidak valid',
                'email.unique'              => 'Email sudah digunakan',
                'name.required'             => 'Nama harus diisi',
                'date_of_birth.required'    => 'Tanggal Lahir harus diisi',
                'department.required'       => 'Department harus diisi',
                'avatar.*.image'            => 'Avatar harus berupa gambar',
                'avatar.*.mimes'            => 'Format Avatar harus jpeg, png, jpg, atau bmp',
                'avatar.*.max'              => 'Ukuran Avatar maksimal 2MB',
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

                $structure->user()->update($updateData);

                $structure->update([
                    'name'   => $request->name ?? '',
                    'date_of_birth' => $request->date_of_birth ?? '',
                    'department' => $request->department ?? '',
                ]);

                $types = ['avatar'];
                $fileObj = new File();

                foreach ($types as $type) {
                    if ($request->hasFile($type)) {
                        $file = $request->file($type);
                        $fileDir = $fileObj->getDirectory($type);
                        $fileName = $fileObj->getFileName($type, $structure->id, $file);

                        $file->storeAs($fileDir, $fileName, 'public');
                        $structure->files()->where('type', $type)->delete();

                        $structure->files()->create([
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
                    'message' => 'Structure updated successfully.',
                    'data' => $structure,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui structure.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        return view('dashboard::index');
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
