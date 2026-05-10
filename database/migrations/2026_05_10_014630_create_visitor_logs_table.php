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
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('organization')->nullable();
            $table->string('purpose');
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('check_in_at');
            $table->dateTime('check_out_at')->nullable();
            $table->string('badge_number')->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'check_in_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
