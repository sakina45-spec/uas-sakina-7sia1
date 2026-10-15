<?php

namespace App\Models;

use App\StudentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

class Student extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    #[Override]
    protected function casts()
    {
        return [
            'gender' => 'boolean',
            'status' => StudentStatusEnum::class,
        ];
    }

    // Relasi ke tabel users
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Rekap ujian yang pernah dikerjakan siswa
    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    // Rekap nilai per mata pelajaran
    public function subjectResults(): HasMany
    {
        return $this->hasMany(ExamAttemptSubject::class);
    }
}