<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_entries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('topic');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->text('additional_notes')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('subject_entries')->restrictOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index(['parent_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_entries');
    }
};
