<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // uploader
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            $table->string('title', 255);
            $table->string('original_name', 255);
            $table->string('file_path', 255);           // path to encrypted blob (outside /public)
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');

            $table->char('hash', 64);                   // SHA-256 hex (ciphertext or plaintext—document your policy)
            $table->longText('ocr_text')->nullable();   // optional: extracted text for search

            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['department_id']);
            $table->index(['title']);
            $table->index(['hash']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('files');
    }
};
