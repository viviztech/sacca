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
        Schema::create('grooming_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('inspected_by')->constrained('users')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->boolean('uniform_ok')->default(false);
            $table->boolean('hair_ok')->default(false);
            $table->boolean('nails_ok')->default(false);
            $table->boolean('shoes_ok')->default(false);
            $table->boolean('id_card_ok')->default(false);
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'inspection_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grooming_inspections');
    }
};
