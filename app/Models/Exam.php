<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $guarded = [];

    protected $casts = [
        'threshold' => 'decimal:2',
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'exact_time' => 'boolean',
        'is_available' => 'boolean',
    ];

    /**
     * Relasi many-to-many dengan mata pelajaran.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class)
            ->withPivot('qty');
    }

    /**
     * Riwayat pengerjaan ujian oleh siswa.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
