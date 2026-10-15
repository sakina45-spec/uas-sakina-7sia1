<?php

use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rekap per mata pelajaran dalam satu kali ujian.
     * Dipakai untuk "Histori Pelajaran".
     */
    public function up(): void
    {
        Schema::create('exam_attempt_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExamAttempt::class)
                ->constrained()->cascadeOnDelete();
            // disalin dari exam_attempts supaya histori per siswa mudah di-query
            $table->foreignIdFor(Student::class)
                ->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subject::class)
                ->nullable()->constrained()->nullOnDelete();
            $table->string('subject_name');
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedInteger('earned_points')->default(0);
            $table->unsignedInteger('max_points')->default(0);
            $table->decimal('score', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_subjects');
    }
};