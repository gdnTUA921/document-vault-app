<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;        // ✅ needed in me() function
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /*
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => ['required','confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        $token = auth('api')->login($user);

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'register',
            'ip_address' => $request->ip(),
            'details'    => ['ua' => $request->userAgent()],
        ]);

        return $this->respondWithToken($token, $user);
    }
    */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'login',
            'ip_address' => $request->ip(),
            'details'    => ['ua' => $request->userAgent()],
        ]);

        return $this->respondWithToken($token, $user);
    }

    public function logout(Request $request)
    {
        try {
            $user = auth('api')->user();
            auth('api')->logout(); // invalidates current token

            AuditLog::create([
                'user_id'    => $user?->id,
                'action'     => 'logout',
                'ip_address' => $request->ip(),
                'details'    => ['ua' => $request->userAgent()],
            ]);

            return response()->json(['message' => 'Logged out']);
        } catch (\Throwable $e) {
            // token missing/expired/blacklisted
            return response()->json(['message' => 'Already logged out or token invalid'], 200);
        }
    }

    public function me(Request $request)
    {
        $user = Auth::guard('api')->user();

        return response()->json([
            'id'            => $user->id,
            'first_name'    => $user->first_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'role'          => $user->role,
            'department_id' => $user->department_id,
            'created_at'    => $user->created_at,
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $newToken = auth('api')->refresh(); // requires blacklist enabled
            $user     = auth('api')->user();
            return $this->respondWithToken($newToken, $user);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token refresh failed'], 401);
        }
    }

    // --- helpers ---

    protected function respondWithToken(string $token, ?User $user = null)
    {
        $ttlMinutes = auth('api')->factory()->getTTL(); // from config/jwt.php or env
        return response()->json([
            'message'     => 'OK',
            'token'       => $token,
            'token_type'  => 'bearer',
            'expires_in'  => $ttlMinutes * 60, // seconds
            'user'        => $user ?? auth('api')->user(),
        ]);
    }
}
