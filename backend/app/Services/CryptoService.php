<?php

namespace App\Services;

class CryptoService
{
    public function makeAesKey(int $bytes = 32): string { return random_bytes($bytes); } // AES-256
    public function makeIv(int $bytes = 16): string     { return random_bytes($bytes); }  // 16-byte IV

    // We store IV + CIPHERTEXT in the file so no IV column is needed.
    public function encryptRaw(string $plain, string $key, string $iv): string
    {
        $cipher = openssl_encrypt($plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return $iv . $cipher; // [IV|CIPHERTEXT]
    }

    public function decryptRaw(string $ivCipher, string $key): string|false
    {
        $iv     = substr($ivCipher, 0, 16);
        $cipher = substr($ivCipher, 16);
        return openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    public function sha256(string $bytes): string { return hash('sha256', $bytes); }
}
