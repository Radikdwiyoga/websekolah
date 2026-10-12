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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 30);
            $table->dateTime('deadline')->nullable();
            $table->decimal('grade_weight', 5, 2)->default(100);
            $table->unsignedSmallInteger('late_deduction_percent')->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('allow_resubmit')->default(true);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'status']);
        });

        Schema::create('assignment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('order')->default(1);
            $table->string('type', 30);
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->json('correct_answers')->nullable();
            $table->decimal('points', 5, 2)->default(1);
            $table->timestamps();

            $table->index(['assignment_id', 'order']);
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->string('status', 20)->default('draft')->index();
            $table->decimal('total_score', 6, 2)->nullable();
            $table->decimal('teacher_score', 6, 2)->nullable();
            $table->text('teacher_comment')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id']);
        });

        Schema::create('assignment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignment_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(
                ['assignment_submission_id', 'assignment_question_id'],
                'assignment_answers_unique'
            );
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_year_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('score', 6, 2);
            $table->decimal('weight', 5, 2)->default(100);
            $table->string('type', 30)->default('assignment');
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'assignment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
        Schema::dropIfExists('assignment_answers');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignment_questions');
        Schema::dropIfExists('assignments');
    }
};
