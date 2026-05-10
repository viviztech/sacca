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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->text('question_text');
            $table->enum('type', ['mcq', 'true_false', 'short_answer'])->default('mcq');
            $table->unsignedTinyInteger('marks')->default(1);
            $table->json('options')->nullable(); // for MCQ: [{text, is_correct}]
            $table->string('correct_answer')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
