<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    /**
     * Siswa yang mengerjakan ujian.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)
            ->withTrashed();
    }

    /**
     * Ujian.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Rekap hasil per mata pelajaran.
     */
    public function subjectResults(): HasMany
    {
        return $this->hasMany(
            ExamAttemptSubject::class,
            'exam_attempt_id'
        );
    }

    /**
     * Detail jawaban setiap soal.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(
            ExamAttemptAnswer::class,
            'exam_attempt_id'
        );
    }
}
