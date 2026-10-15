<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptAnswer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Relasi ke pengerjaan ujian.
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(
            ExamAttempt::class,
            'exam_attempt_id'
        );
    }

    /**
     * Relasi ke soal.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(
            Question::class,
            'question_id'
        )->withTrashed();
    }

    /**
     * Jawaban yang dipilih siswa.
     */
        public function studentAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'student_answer_id');
    }

    public function correctAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'correct_answer_id');
    }

    /**
     * Status jawaban.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->student_answer_id === null) {
            return 'Tidak dijawab';
        }

        return $this->is_correct
            ? 'Benar'
            : 'Salah';
    }
}
