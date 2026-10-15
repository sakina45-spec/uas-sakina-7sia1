<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\View\View;

class ExamHistoryController extends Controller
{
    /**
     * Rekap seluruh histori ujian siswa.
     */
    public function index(): View
    {
        $attempts = ExamAttempt::with([
            'student.user',
            'exam',
            'subject',
        ])
            ->latest('completed_at')
            ->get();

        return view('exam-history.index', compact('attempts'));
    }

    /**
     * Detail satu histori ujian.
     */
    public function show(ExamAttempt $attempt): View
    {
        $attempt->load([
            'student.user',
            'exam',
            'subject',
            'answers.question',
            'answers.studentAnswer',
            'answers.correctAnswer',
        ]);

        return view('exam-history.show', compact('attempt'));
    }

    /**
     * Histori ujian berdasarkan pelajaran.
     */
    public function subjects(): View
    {
        $attempts = ExamAttempt::with([
            'student.user',
            'exam',
            'subject',
        ])
            ->latest('completed_at')
            ->get()
            ->groupBy(function ($attempt) {
                return $attempt->subject_id;
            });

        return view('exam-history.subjects', compact('attempts'));
    }
}