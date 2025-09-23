<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('shared_with')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['file_id', 'shared_with'], 'uq_share');
        });
    }

    public function down(): void {
        Schema::dropIfExists('file_shares');
    }
};
