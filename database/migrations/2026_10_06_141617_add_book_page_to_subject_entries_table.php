<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subject_entries', 'book_page')) {
            Schema::table('subject_entries', function (Blueprint $table) {
                $table->unsignedInteger('book_page')->nullable();
            });
        }
    }

    public function down(): void
    {
        // The preceding connect_subjects_to_genealogy migration owns and removes this column.
    }
};
