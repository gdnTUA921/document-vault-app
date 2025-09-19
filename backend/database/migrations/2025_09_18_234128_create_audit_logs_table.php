<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 100);                 // login, logout, upload, download, delete, share, revoke, etc.
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();  // IPv4/IPv6
            $table->json('details')->nullable();           // optional: user agent, error messages, etc.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at'], 'idx_audit_user_time');
            $table->index(['action', 'created_at'], 'idx_audit_action_time');
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
