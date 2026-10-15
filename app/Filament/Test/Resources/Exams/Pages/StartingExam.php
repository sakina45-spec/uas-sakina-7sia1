<?php

namespace App\Filament\Test\Resources\Exams\Pages;

use App\Filament\Test\Resources\Exams\ExamResource;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAnswer;
use App\Models\ExamAttemptSubject;
use App\Models\Question;
use App\Models\Answer;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class StartingExam extends Page
{
    protected static string $resource = ExamResource::class;

    protected string $view =
        'filament.test.resources.exams.pages.starting-exam';

    public Exam $record;

    public ?ExamAttempt $attempt = null;

    public array $questions = [];

    public array $answers = [];

    public int $currentQuestionIndex = 0;

    public bool $examFinished = false;

    public ?string $resultMessage = null;

    public function mount(Exam $record): void
    {
        $this->record = $record;

        abort_unless($record->is_available, 404);

        $student = auth()->user()?->student;

        abort_unless($student, 403);

        // Cari ujian yang masih sedang dikerjakan
        $existingAttempt = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->where('exam_id', $record->id)
            ->whereNull('completed_at')
            ->latest('id')
            ->first();

        if ($existingAttempt) {
            $this->attempt = $existingAttempt;

            $this->loadQuestions();

            return;
        }

        // Buat percobaan ujian baru
        $this->createAttempt($student->id);

        $this->loadQuestions();
    }

    protected function createAttempt(int $studentId): void
    {
        $subjects = $this->record
            ->subjects()
            ->where('subjects.is_active', true)
            ->get();

        abort_if(
            $subjects->isEmpty(),
            404,
            'Belum ada mata pelajaran pada ujian ini.'
        );

        $totalQuestions = 0;

        foreach ($subjects as $subject) {
            $totalQuestions += (int) $subject->pivot->qty;
        }

        $this->attempt = ExamAttempt::create([
            'student_id' => $studentId,
            'exam_id' => $this->record->id,
            'subject_id' => $subjects->first()->id,
            'started_at' => now(),
            'total_questions' => $totalQuestions,
            'correct_answers' => 0,
            'wrong_answers' => 0,
            'score' => 0,
        ]);
    }

    protected function loadQuestions(): void
    {
        $subjects = $this->record
            ->subjects()
            ->where('subjects.is_active', true)
            ->get();

        $collections = [];

        foreach ($subjects as $subject) {
            $qty = (int) $subject->pivot->qty;

            if ($qty <= 0) {
                continue;
            }

            $questions = Question::query()
                ->where('subject_id', $subject->id)
                ->where('is_active', true)
                ->with([
                    'answers' => fn ($query) =>
                        $query
                            ->where('is_active', true)
                            ->orderBy('id'),
                ])
                ->orderByRaw('CRC32(CONCAT(id, ?))', [$this->attempt->id])
                ->limit($qty)
                ->get();

            foreach ($questions as $question) {
                $collections[] = [
                    'id' => $question->id,

                    'subject_id' => $subject->id,

                    'subject_name' => $subject->name,

                    // Kolom soal yang benar adalah payload
                    'payload' => $question->payload,

                    'score' => (int) $question->score,

                    'answers' => $question->answers
                        ->map(fn ($answer) => [
                            'id' => $answer->id,
                            'text' => $answer->text,
                        ])
                        ->values()
                        ->toArray(),
                ];
            }
        }

        abort_if(
            empty($collections),
            404,
            'Belum ada soal aktif untuk ujian ini.'
        );

        $this->questions = $collections;

        // Ambil jawaban yang sudah tersimpan
        $savedAnswers = ExamAttemptAnswer::query()
            ->where('exam_attempt_id', $this->attempt->id)
            ->pluck('student_answer_id', 'question_id')
            ->toArray();

        $this->answers = $savedAnswers;

        // Pastikan index soal valid
        if (
            $this->currentQuestionIndex >= count($this->questions)
        ) {
            $this->currentQuestionIndex = 0;
        }
    }

    public function selectAnswer(int $answerId): void
    {
        if ($this->examFinished || ! $this->attempt) {
            return;
        }

        $question = $this->questions[$this->currentQuestionIndex] ?? null;

        if (! $question) {
            return;
        }

        $validAnswer = collect($question['answers'])
            ->contains(fn (array $answer): bool => (int) $answer['id'] === $answerId);

        if (! $validAnswer) {
            return;
        }

        $this->answers[$question['id']] = $answerId;

        $correctAnswer = Answer::query()
            ->where('question_id', $question['id'])
            ->where('is_correct', true)
            ->where('is_active', true)
            ->first();

        ExamAttemptAnswer::updateOrCreate(
            [
                'exam_attempt_id' => $this->attempt->id,
                'question_id' => $question['id'],
            ],
            [
                'student_answer_id' => $answerId,
                'correct_answer_id' => $correctAnswer?->id,
                'is_correct' => $correctAnswer?->id === $answerId,
            ]
        );
    }

    public function checkTime(): void
    {
        if ($this->examFinished || ! $this->attempt) {
            return;
        }

        if ($this->remainingSeconds <= 0) {
            $this->finishExam();
        }
    }

    public function nextQuestion(): void
    {
        if (
            $this->currentQuestionIndex <
            count($this->questions) - 1
        ) {
            $this->currentQuestionIndex++;
        }
    }

    public function previousQuestion(): void
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
        }
    }

    public function goToQuestion(int $index): void
    {
        if (
            $index >= 0 &&
            $index < count($this->questions)
        ) {
            $this->currentQuestionIndex = $index;
        }
    }

    public function finishExam(): void
    {
        if ($this->examFinished) {
            return;
        }

        DB::transaction(function () {

            $totalCorrect = 0;
            $totalWrong = 0;
            $earnedPoints = 0;
            $maxPoints = 0;

            $subjectResults = [];

            foreach ($this->questions as $questionData) {

                $questionId = $questionData['id'];

                $studentAnswerId =
                    $this->answers[$questionId] ?? null;

                $question = Question::query()
                    ->with([
                        'answers' => fn ($query) =>
                            $query->where('is_active', true),
                    ])
                    ->find($questionId);

                if (!$question) {
                    continue;
                }

                $correctAnswer = $question->answers
                    ->firstWhere('is_correct', true);

                $isCorrect =
                    $studentAnswerId !== null &&
                    $correctAnswer &&
                    (int) $studentAnswerId ===
                    (int) $correctAnswer->id;

                if ($isCorrect) {

                    $totalCorrect++;

                    $earnedPoints +=
                        (int) $question->score;

                } else {

                    $totalWrong++;
                }

                $maxPoints +=
                    (int) $question->score;

                // Simpan jawaban siswa
                ExamAttemptAnswer::updateOrCreate(
                    [
                        'exam_attempt_id' =>
                            $this->attempt->id,

                        'question_id' =>
                            $question->id,
                    ],
                    [
                        'student_answer_id' =>
                            $studentAnswerId,

                        'correct_answer_id' =>
                            $correctAnswer?->id,

                        'is_correct' =>
                            $isCorrect,
                    ]
                );

                // Rekap per mata pelajaran
                $subjectId =
                    $question->subject_id;

                if (!isset($subjectResults[$subjectId])) {

                    $subject = $this->record
                        ->subjects()
                        ->where(
                            'subjects.id',
                            $subjectId
                        )
                        ->first();

                    $subjectResults[$subjectId] = [

                        'student_id' =>
                            $this->attempt->student_id,

                        'subject_id' =>
                            $subjectId,

                        'subject_name' =>
                            $subject?->name ??
                            'Mata Pelajaran',

                        'total_questions' => 0,

                        'correct_count' => 0,

                        'wrong_count' => 0,

                        'earned_points' => 0,

                        'max_points' => 0,

                        'score' => 0,
                    ];
                }

                $subjectResults[$subjectId]
                    ['total_questions']++;

                if ($isCorrect) {

                    $subjectResults[$subjectId]
                        ['correct_count']++;

                    $subjectResults[$subjectId]
                        ['earned_points'] +=
                        (int) $question->score;

                } else {

                    $subjectResults[$subjectId]
                        ['wrong_count']++;
                }

                $subjectResults[$subjectId]
                    ['max_points'] +=
                    (int) $question->score;
            }

            // Hitung nilai 0 - 100
            $score = $maxPoints > 0
                ? round(
                    ($earnedPoints / $maxPoints) * 100,
                    2
                )
                : 0;

            // Simpan rekap mata pelajaran
            foreach ($subjectResults as &$result) {

                $result['score'] =
                    $result['max_points'] > 0
                        ? round(
                            (
                                $result['earned_points'] /
                                $result['max_points']
                            ) * 100,
                            2
                        )
                        : 0;

                ExamAttemptSubject::updateOrCreate(
                    [
                        'exam_attempt_id' =>
                            $this->attempt->id,

                        'subject_id' =>
                            $result['subject_id'],
                    ],
                    $result
                );
            }

            // Simpan hasil ujian
            $this->attempt->update([

                'completed_at' =>
                    now(),

                'total_questions' =>
                    count($this->questions),

                'correct_answers' =>
                    $totalCorrect,

                'wrong_answers' =>
                    $totalWrong,

                'score' =>
                    $score,
            ]);

            $this->attempt->refresh();

            $this->resultMessage =
                "Ujian selesai. Nilai kamu adalah {$score}.";

            $this->examFinished = true;
        });
    }

    // Data soal yang sedang ditampilkan
    public function getCurrentQuestionProperty(): ?array
    {
        return $this->questions[
            $this->currentQuestionIndex
        ] ?? null;
    }

    public function getAnsweredCountProperty(): int
    {
        return count($this->answers);
    }

    public function getTotalQuestionsProperty(): int
    {
        return count($this->questions);
    }

    public function getRemainingQuestionsProperty(): int
    {
        return max(
            0,
            $this->totalQuestions -
            $this->answeredCount
        );
    }

    public function getRemainingSecondsProperty(): int
    {
        if (! $this->attempt) {
            return 0;
        }

        $endsAt = $this->attempt->started_at?->copy()->addMinutes((int) $this->record->duration);

        if (! $endsAt) {
            return 0;
        }

        return max(0, now()->diffInSeconds($endsAt, false));
    }

    public function getPassedProperty(): bool
    {
        if (!$this->attempt) {
            return false;
        }

        return (float) $this->attempt->score >=
            (float) $this->record->threshold;
    }

    public function getTitle(): string|Htmlable
    {
        return $this->record->title;
    }

    public function getHeading(): string|Htmlable
    {
        return $this->record->title;
    }
}
