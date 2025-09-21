<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserKey;
use Illuminate\Support\Facades\Log;
use App\Models\FileKey;
use Illuminate\Support\Facades\DB;

class RsaKeyService
{
    private string $wrapKey; // symmetric key to protect encrypted private key

    public function __construct()
    {
        // MVP: Derive wrapKey from APP_KEY. 
        // For production → use dedicated KMS/HSM.
        $this->wrapKey = hash('sha256', config('app.key'));
    }

    /**
     * Ensure a user has a keypair, generate if missing
     */
    public function ensureKeypair(User $user): UserKey
    {
        $existing = UserKey::find($user->id);
        if ($existing) {
            return $existing;
        }
        return $this->generateFor($user);
    }

    /**
     * Generate new RSA keypair for user and store it
     */
    public function generateFor(User $user): UserKey
    {
        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);

        openssl_pkey_export($res, $privPem);
        $pub = openssl_pkey_get_details($res)['key'];

        $iv = substr($this->wrapKey, 0, 16);
        $encPriv = openssl_encrypt($privPem, 'AES-256-CBC', $this->wrapKey, 0, $iv);

        return UserKey::updateOrCreate(
            ['user_id' => $user->id],
            [
                'public_key' => $pub,
                'encrypted_private_key' => $encPriv
            ]
        );
    }

    /**
     * Encrypt AES key (bytes) for recipient using their public key
     */
    public function encryptForUser(User $recipient, string $bytes): string
    {
        $uk  = UserKey::findOrFail($recipient->id);
        $pub = openssl_pkey_get_public($uk->public_key);

        if (!$pub) {
            throw new \RuntimeException("Invalid public key for user {$recipient->id}");
        }

        openssl_public_encrypt($bytes, $wrapped, $pub, OPENSSL_PKCS1_OAEP_PADDING);
        return base64_encode($wrapped);
    }

    /**
     * Decrypt AES key (wrappedB64) for owner using their private key
     */
    public function decryptForUser(User $user, string $wrappedB64): string
    {
        $uk = UserKey::findOrFail($user->id);
        $iv = substr($this->wrapKey, 0, 16);

        $privPem = openssl_decrypt(
            $uk->encrypted_private_key,
            'AES-256-CBC',
            $this->wrapKey,
            0,
            $iv
        );

        if (!$privPem) {
            throw new \RuntimeException("Failed to decrypt private key for user {$user->id}");
        }

        $priv = openssl_pkey_get_private($privPem);
        if (!$priv) {
            throw new \RuntimeException("Invalid private key for user {$user->id}");
        }

        $decoded = base64_decode($wrappedB64, true);
        if ($decoded === false) {
            throw new \RuntimeException("Invalid base64 input for wrapped key.");
        }

        $ok = openssl_private_decrypt($decoded, $plain, $priv, OPENSSL_PKCS1_OAEP_PADDING);
        if (!$ok) {
            throw new \RuntimeException("RSA decryption failed for user {$user->id}");
        }

        return $plain;
    }

    /**
     * Compute fingerprint of a public key
     */
    public function fingerprint(string $pubPem): string
    {
        return substr(hash('sha256', $pubPem), 0, 16);
    }

    /**
     * Rotate user's RSA keypair and re-wrap all their file AES keys
     */
    public function rotateAndRewrap(User $user): int
    {
        return DB::transaction(function () use ($user) {
            // 1) Load current (old) keys
            /** @var UserKey $uk */
            $uk = UserKey::where('user_id', $user->id)->lockForUpdate()->first();
            if (!$uk) {
                // If the user has no keypair yet, just generate one
                $this->generateFor($user);
                return 0;
            }

            // Unwrap old private key
            $iv = substr($this->wrapKey, 0, 16);
            $oldPrivPem = openssl_decrypt($uk->encrypted_private_key, 'AES-256-CBC', $this->wrapKey, 0, $iv);
            if (!$oldPrivPem) {
                throw new \RuntimeException("Failed to decrypt user's existing private key.");
            }
            $oldPriv = openssl_pkey_get_private($oldPrivPem);
            if (!$oldPriv) {
                throw new \RuntimeException("Invalid old private key PEM.");
            }

            // 2) Collect all plaintext AES keys for this owner (recipient_user_id = owner)
            $ownerKeys = FileKey::where('recipient_user_id', $user->id)
                ->lockForUpdate()
                ->get(['id','encrypted_aes_key']);

            $plaintextAesById = [];
            foreach ($ownerKeys as $fk) {
                $wrapped = base64_decode($fk->encrypted_aes_key, true);
                if ($wrapped === false) {
                    throw new \RuntimeException("file_keys.id={$fk->id}: invalid base64.");
                }
                $plain = null;
                if (!openssl_private_decrypt($wrapped, $plain, $oldPriv, OPENSSL_PKCS1_OAEP_PADDING)) {
                    throw new \RuntimeException("file_keys.id={$fk->id}: RSA decrypt failed with old key.");
                }
                $plaintextAesById[$fk->id] = $plain;
            }

            // 3) Generate the NEW keypair
            $res = openssl_pkey_new([
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            ]);
            openssl_pkey_export($res, $newPrivPem);
            $newPubPem = openssl_pkey_get_details($res)['key'];

            // Protect (wrap) new private key with APP_KEY
            $newEncPriv = openssl_encrypt($newPrivPem, 'AES-256-CBC', $this->wrapKey, 0, $iv);

            // 4) Update user_keys with the NEW pair
            $uk->public_key = $newPubPem;
            $uk->encrypted_private_key = $newEncPriv;
            $uk->save();

            // 5) Re-wrap every collected AES key with the NEW public key
            $newPub = openssl_pkey_get_public($newPubPem);
            if (!$newPub) {
                throw new \RuntimeException("New public key is invalid after rotation.");
            }

            $count = 0;
            foreach ($plaintextAesById as $fileKeyId => $plainAes) {
                $wrappedNew = null;
                if (!openssl_public_encrypt($plainAes, $wrappedNew, $newPub, OPENSSL_PKCS1_OAEP_PADDING)) {
                    throw new \RuntimeException("file_keys.id={$fileKeyId}: RSA encrypt failed with new key.");
                }
                FileKey::where('id', $fileKeyId)->update([
                    'encrypted_aes_key' => base64_encode($wrappedNew),
                ]);
                $count++;
            }

            return $count; // number of file_keys rewrapped for this user
        });
    }
}
