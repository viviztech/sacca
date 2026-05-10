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
        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('lms_materials')->cascadeOnDelete();
            $table->dateTime('due_date');
            $table->unsignedSmallInteger('max_marks')->default(100);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_assignments');
    }
};
