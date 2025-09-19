<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('file_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();

            $table->longText('encrypted_aes_key');        // base64 RSA-encrypted AES key (e.g., RSA-OAEP-SHA256)
            $table->string('key_encryption_algo', 50)->default('RSA-OAEP-SHA256');
            $table->string('key_fingerprint', 64)->nullable(); // e.g., SHA-256 of recipient public key

            $table->timestamps();

            $table->unique(['file_id', 'recipient_user_id'], 'uq_file_recipient');
        });
    }

    public function down(): void {
        Schema::dropIfExists('file_keys');
    }
};
