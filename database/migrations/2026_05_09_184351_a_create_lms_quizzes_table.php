<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('lms_materials')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('time_limit_minutes')->default(30);
            $table->unsignedSmallInteger('passing_marks')->default(50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_quizzes');
    }
};
