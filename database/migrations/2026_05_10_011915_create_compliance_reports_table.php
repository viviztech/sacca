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
        Schema::create('compliance_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->enum('report_type', ['TAHDCO', 'SCDD', 'monthly_summary', 'annual']);
            $table->date('period_from');
            $table->date('period_to');
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->dateTime('generated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_reports');
    }
};
