<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
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


    public function logout(Request $request)
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect('/login');
    }


    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
