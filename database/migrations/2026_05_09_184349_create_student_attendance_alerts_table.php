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
        Schema::create('student_attendance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->enum('alert_type', ['low_attendance', 'absent_streak']);
            $table->unsignedTinyInteger('threshold_value');
            $table->dateTime('sent_at');
            $table->enum('channel', ['whatsapp', 'email', 'fcm'])->default('whatsapp');
            $table->timestamps();

            $table->index(['student_id', 'alert_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance_alerts');
    }
};
