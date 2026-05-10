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
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->date('class_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->foreignId('marked_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('marked_at');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'timetable_id', 'class_date']);
            $table->index(['timetable_id', 'class_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};
