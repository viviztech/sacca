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
        Schema::create('placement_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('placement_applications')->cascadeOnDelete();
            $table->enum('result', ['placed', 'not_placed']);
            $table->string('offer_letter_path')->nullable();
            $table->date('joining_date')->nullable();
            $table->decimal('salary_offered', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placement_outcomes');
    }
};
