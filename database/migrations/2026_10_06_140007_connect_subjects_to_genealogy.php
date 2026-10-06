<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bansawali_members', function (Blueprint $table) {
            $table->unsignedInteger('root_generation')->default(1);
            $table->text('location_names')->nullable();
            $table->string('annotation', 255)->nullable();
        });
        Schema::table('subject_entries', function (Blueprint $table) {
            $table->unsignedInteger('book_page')->nullable();
            $table->foreignId('root_member_id')->nullable()->constrained('bansawali_members')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subject_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('root_member_id');
            $table->dropColumn('book_page');
        });
        Schema::table('bansawali_members', function (Blueprint $table) {
            $table->dropColumn(['root_generation', 'location_names', 'annotation']);
        });
    }
};
