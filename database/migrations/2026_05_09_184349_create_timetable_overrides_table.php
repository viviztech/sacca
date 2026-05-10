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
        Schema::create('timetable_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->date('override_date');
            $table->foreignId('substitute_faculty_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->enum('status', ['cancelled', 'substituted', 'rescheduled'])->default('cancelled');
            $table->timestamps();

            $table->unique(['timetable_id', 'override_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable_overrides');
    }
};
