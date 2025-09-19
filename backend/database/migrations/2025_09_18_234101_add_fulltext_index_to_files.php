<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // MySQL 8 (InnoDB) supports FULLTEXT on varchar/text.
        Schema::table('files', function (Blueprint $table) {
            $table->fullText(['title', 'ocr_text'], 'ft_files_title_ocr');
        });
    }

    public function down(): void {
        Schema::table('files', function (Blueprint $table) {
            $table->dropFullText('ft_files_title_ocr');
        });
    }
};
