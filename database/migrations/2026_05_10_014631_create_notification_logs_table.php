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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('channel', ['whatsapp', 'email', 'fcm'])->default('whatsapp');
            $table->string('template_name');
            $table->json('payload')->nullable();
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->string('provider_message_id')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
            $table->index(['recipient_user_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
