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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->string('destination');
            $table->string('purpose');
            $table->foreignId('coordinator_id')->constrained('users')->cascadeOnDelete();
            $table->string('permission_form_path')->nullable();
            $table->enum('status', ['planned', 'completed', 'cancelled'])->default('planned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
