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
        Schema::create('daily_work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('report_date');
            $table->text('work_summary');
            $table->json('tasks_completed')->nullable();
            $table->text('blockers')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('escalation_sent_at')->nullable();
            $table->enum('status', ['submitted', 'pending', 'escalated'])->default('pending');
            $table->timestamps();
            $table->unique(['user_id', 'report_date']);
            $table->index(['branch_id', 'report_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_work_reports');
    }
};
