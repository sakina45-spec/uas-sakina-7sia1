<?php

namespace App\Filament\Resources\Questions\Imports;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuestionCsvImporter
{
    public static function import(string $filePath): void
    {
        if (! file_exists($filePath)) {
            throw new RuntimeException(
                'File CSV tidak ditemukan.'
            );
        }

        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new RuntimeException(
                'File CSV tidak dapat dibuka.'
            );
        }

        try {
            $header = fgetcsv($handle);

            if ($header === false) {
                throw new RuntimeException(
                    'File CSV kosong.'
                );
            }

            // Hilangkan BOM jika file dibuat dari Excel
            $header[0] = preg_replace(
                '/^\xEF\xBB\xBF/',
                '',
                $header[0]
            );

            $header = array_map(
                fn ($value) => strtolower(trim($value)),
                $header
            );

            $requiredHeaders = [
                'subject',
                'question',
                'score',
                'option_a',
                'option_b',
                'option_c',
                'option_d',
                'correct_answer',
            ];

            foreach ($requiredHeaders as $requiredHeader) {
                if (! in_array($requiredHeader, $header, true)) {
                    throw new RuntimeException(
                        "Kolom CSV '{$requiredHeader}' tidak ditemukan."
                    );
                }
            }

            $rowNumber = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Lewati baris kosong
                if (
                    count($row) === 1 &&
                    trim((string) $row[0]) === ''
                ) {
                    continue;
                }

                if (count($row) < count($header)) {
                    throw new RuntimeException(
                        "Baris {$rowNumber} tidak lengkap."
                    );
                }

                $data = array_combine(
                    $header,
                    array_pad(
                        $row,
                        count($header),
                        ''
                    )
                );

                $subjectName = trim(
                    (string) ($data['subject'] ?? '')
                );

                $questionText = trim(
                    (string) ($data['question'] ?? '')
                );

                $score = (int) (
                    $data['score'] ?? 1
                );

                $optionA = trim(
                    (string) ($data['option_a'] ?? '')
                );

                $optionB = trim(
                    (string) ($data['option_b'] ?? '')
                );

                $optionC = trim(
                    (string) ($data['option_c'] ?? '')
                );

                $optionD = trim(
                    (string) ($data['option_d'] ?? '')
                );

                $correctAnswer = strtoupper(
                    trim(
                        (string) (
                            $data['correct_answer'] ?? ''
                        )
                    )
                );

                // Validasi
                if ($subjectName === '') {
                    throw new RuntimeException(
                        "Baris {$rowNumber}: Subject wajib diisi."
                    );
                }

                if ($questionText === '') {
                    throw new RuntimeException(
                        "Baris {$rowNumber}: Pertanyaan wajib diisi."
                    );
                }

                if (
                    $optionA === '' ||
                    $optionB === '' ||
                    $optionC === '' ||
                    $optionD === ''
                ) {
                    throw new RuntimeException(
                        "Baris {$rowNumber}: Semua pilihan A-D wajib diisi."
                    );
                }

                if (! in_array(
                    $correctAnswer,
                    ['A', 'B', 'C', 'D'],
                    true
                )) {
                    throw new RuntimeException(
                        "Baris {$rowNumber}: correct_answer harus A, B, C, atau D."
                    );
                }

                if ($score <= 0) {
                    $score = 1;
                }

                // Jika pelajaran belum ada, buat otomatis agar admin cukup upload CSV.
                $subject = Subject::query()
                    ->withTrashed()
                    ->whereRaw('LOWER(name) = ?', [strtolower($subjectName)])
                    ->first();

                if ($subject?->trashed()) {
                    $subject->restore();
                }

                $subject ??= Subject::create([
                    'name' => $subjectName,
                    'is_active' => true,
                ]);

                DB::transaction(
                    function () use (
                        $subject,
                        $score,
                        $questionText,
                        $optionA,
                        $optionB,
                        $optionC,
                        $optionD,
                        $correctAnswer
                    ): void {
                        // Buat soal
                        $question = Question::create([
                            'subject_id' => $subject->id,
                            'score' => $score,

                            // PENTING:
                            // Project ini menggunakan payload
                            // sebagai isi soal.
                            'payload' => $questionText,

                            'is_active' => true,
                        ]);

                        $options = [
                            'A' => $optionA,
                            'B' => $optionB,
                            'C' => $optionC,
                            'D' => $optionD,
                        ];

                        foreach ($options as $letter => $text) {
                            Answer::create([
                                'question_id' => $question->id,
                                'text' => $text,
                                'is_correct' =>
                                    $letter === $correctAnswer,
                                'is_active' => true,
                            ]);
                        }
                    }
                );
            }
        } finally {
            fclose($handle);
        }
    }
}