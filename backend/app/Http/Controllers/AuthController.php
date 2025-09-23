<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;        
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\RsaKeyService;

class AuthController extends Controller
{
    public function login(Request $request, RsaKeyService $rsa)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

        // ✅ Ensure user has a keypair (generate if missing)
        $rsa->ensureKeypair($user);

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
            auth('api')->logout();

            AuditLog::create([
                'user_id'    => $user?->id,
                'action'     => 'logout',
                'ip_address' => $request->ip(),
                'details'    => ['ua' => $request->userAgent()],
            ]);

            return response()->json(['message' => 'Logged out']);
        } catch (\Throwable $e) {
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

    public function refresh(Request $request, RsaKeyService $rsa)
    {
        try {
            $newToken = auth('api')->refresh();
            $user     = auth('api')->user();

            // ✅ Ensure keypair after refresh too
            $rsa->ensureKeypair($user);

            return $this->respondWithToken($newToken, $user);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token refresh failed'], 401);
        }
    }

    // ✅ Manual RSA key rotation
    public function rotateKeys(Request $request, RsaKeyService $rsa)
    {
        $user = Auth::guard('api')->user();

        try {
            $rewrapped = $rsa->rotateAndRewrap($user);

            AuditLog::create([
                'user_id'    => $user->id,
                'action'     => 'rotate_keys',
                'ip_address' => $request->ip(),
                'details'    => ['rewrapped' => $rewrapped],
            ]);

            return response()->json([
                'message'          => 'RSA keypair rotated and file keys rewrapped',
                'rewrapped_count'  => $rewrapped,
                'new_fingerprint'  => $rsa->fingerprint($user->userKey->public_key ?? ''),
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unexpected error during rotation.'], 500);
        }
    }

    // --- helpers ---

    protected function respondWithToken(string $token, ?User $user = null)
    {
        $ttlMinutes = auth('api')->factory()->getTTL();
        return response()->json([
            'message'     => 'OK',
            'token'       => $token,
            'token_type'  => 'bearer',
            'expires_in'  => $ttlMinutes * 60,
            'user'        => $user ?? auth('api')->user(),
        ]);
    }
}
