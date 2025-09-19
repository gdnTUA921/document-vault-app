<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserKey;

class RsaKeyService
{
    private string $wrapKey; // symmetric protector for encrypted_private_key

    public function __construct()
    {
        // MVP: derive from APP_KEY. For production, use KMS/HSM.
        $this->wrapKey = hash('sha256', config('app.key'));
    }

    public function ensureKeypair(User $user): UserKey
    {
        $existing = UserKey::find($user->id);
        if ($existing) return $existing;
        return $this->generateFor($user);
    }

    public function generateFor(User $user): UserKey
    {
        $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($res, $privPem);
        $pub = openssl_pkey_get_details($res)['key'];

        $iv = substr($this->wrapKey, 0, 16);
        $encPriv = openssl_encrypt($privPem, 'AES-256-CBC', $this->wrapKey, 0, $iv);

        return UserKey::updateOrCreate(
            ['user_id' => $user->id],
            ['public_key' => $pub, 'encrypted_private_key' => $encPriv]
        );
    }

    public function encryptForUser(User $recipient, string $bytes): string
    {
        $uk  = UserKey::findOrFail($recipient->id);
        $pub = openssl_pkey_get_public($uk->public_key);
        openssl_public_encrypt($bytes, $wrapped, $pub, OPENSSL_PKCS1_OAEP_PADDING);
        return base64_encode($wrapped);
    }

    public function decryptForUser(User $user, string $wrappedB64): string
    {
        $uk = UserKey::findOrFail($user->id);
        $iv = substr($this->wrapKey, 0, 16);
        $privPem = openssl_decrypt($uk->encrypted_private_key, 'AES-256-CBC', $this->wrapKey, 0, $iv);

        $priv = openssl_pkey_get_private($privPem);
        openssl_private_decrypt(base64_decode($wrappedB64), $plain, $priv, OPENSSL_PKCS1_OAEP_PADDING);
        return $plain;
    }

    public function fingerprint(string $pubPem): string
    {
        return substr(hash('sha256', $pubPem), 0, 16);
    }
}
