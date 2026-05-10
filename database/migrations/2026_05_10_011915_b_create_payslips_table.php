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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('working_days');
            $table->unsignedTinyInteger('present_days');
            $table->unsignedTinyInteger('absent_days');
            $table->decimal('basic_salary', 10, 2);
            $table->json('deductions')->nullable();
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('net_salary', 10, 2);
            $table->string('pdf_path')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->unique(['payroll_cycle_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
