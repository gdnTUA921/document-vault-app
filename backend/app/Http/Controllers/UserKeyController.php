<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RsaKeyService;

class UserKeyController extends Controller
{
    public function generate(Request $request, RsaKeyService $svc)
    {
        $user = Auth::guard('api')->user();
        $key  = $svc->ensureKeypair($user);

        return response()->json([
            'message'     => 'RSA keypair ready',
            'fingerprint' => $svc->fingerprint($key->public_key),
        ], 201);
    }

    public function rotate(Request $request, RsaKeyService $svc)
    {
        $user = Auth::guard('api')->user();

        try {
            // ⬇️ this calls the atomic rotate+rewrap logic
            $rewrapped = $svc->rotateAndRewrap($user);

            return response()->json([
                'message'          => 'RSA keypair rotated and file keys rewrapped',
                'rewrapped_count'  => $rewrapped,
                'new_fingerprint'  => $svc->fingerprint($user->userKey->public_key ?? ''),
            ], 200);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unexpected error during rotation.'], 500);
        }
    }
}
