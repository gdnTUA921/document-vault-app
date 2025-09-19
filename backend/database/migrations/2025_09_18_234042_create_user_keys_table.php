<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_keys', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->longText('public_key');              // PEM: -----BEGIN PUBLIC KEY-----
            $table->longText('encrypted_private_key')->nullable(); // PEM (encrypted), optional
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_keys');
    }
};
