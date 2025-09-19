<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => ['required','confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        $token = auth('api')->login($user);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'register',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['user' => $user, 'token' => $token]);
    }
    */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['message'=>'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'details' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Logged in successfully', 'token' => $token, 'user' => $user]);
        //return response()->json(['message' => 'Logged in successfully']);
    }

    public function logout(Request $request)
    {
        $user = auth('api')->user();
        auth('api')->logout();

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => 'logout',
            'ip_address' => $request->ip(),
            'details' => $request->userAgent(),
        ]);

        return response()->json(['message' => 'Logged out']);
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function refresh()
    {
        return response()->json(['token' => auth('api')->refresh()]);
    }
}
