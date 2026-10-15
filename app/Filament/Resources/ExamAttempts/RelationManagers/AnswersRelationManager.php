<?php

namespace App\Filament\Resources\ExamAttempts\RelationManagers;

use App\Models\Subject;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Detail Histori Ujian: rincian soal, jawaban siswa, kunci jawaban,
 * dan status benar/salah. Dipakai di panel admin dan panel siswa.
 */
class AnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static string|null $title = 'Detail Histori Ujian';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['question.subject', 'studentAnswer', 'correctAnswer']))
            ->defaultSort('id')
            ->paginated([10, 25, 50, 'all'])
            ->columns([
                TextColumn::make('#')
                    ->rowIndex()
                    ->width(40),
                TextColumn::make('question.subject.name')
                    ->label('Mata pelajaran')
                    ->placeholder('-')
                    ->badge(),
                TextColumn::make('question.payload')
                    ->label('Soal')
                    ->html()
                    ->wrap(),
                TextColumn::make('studentAnswer.text')
                    ->label('Jawaban siswa')
                    ->placeholder('Tidak dijawab')
                    ->wrap(),
                TextColumn::make('correctAnswer.text')
                    ->label('Kunci jawaban')
                    ->placeholder('-')
                    ->wrap(),
                TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Benar' => 'success',
                        'Salah' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('poin')
                    ->label('Poin')
                    ->state(function ($record) {
                        $max = (int) $record->question?->score;

                        return ($record->is_correct ? $max : 0) . '/' . $max;
                    }),
            ])
            ->filters([
                SelectFilter::make('subject')
                    ->label('Mata pelajaran')
                    ->options(fn () => Subject::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas(
                            'question',
                            fn (Builder $q) => $q->withTrashed()->where('subject_id', $data['value'])
                        )
                        : $query),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'benar' => 'Benar',
                        'salah' => 'Salah',
                        'kosong' => 'Tidak dijawab',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'benar' => $query->where('is_correct', true),
                        'salah' => $query->where('is_correct', false)->whereNotNull('student_answer_id'),
                        'kosong' => $query->whereNull('student_answer_id'),
                        default => $query,
                    }),
            ]);
    }
}