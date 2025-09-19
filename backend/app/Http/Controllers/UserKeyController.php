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
        $key  = $svc->generateFor($user);
        // NOTE: rewrapping existing FileKeys on rotate is out-of-scope for MVP.
        return response()->json([
            'message'     => 'RSA keypair rotated',
            'fingerprint' => $svc->fingerprint($key->public_key),
        ]);
    }
}
