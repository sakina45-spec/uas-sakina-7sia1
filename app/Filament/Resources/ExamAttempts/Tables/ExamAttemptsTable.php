<?php

namespace App\Filament\Resources\ExamAttempts\Tables;

use App\Models\Subject;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamAttemptsTable
{
    /**
     * Tabel Rekap Ujian. Dipakai di panel admin (semua siswa)
     * dan panel siswa ($forAdmin = false, tanpa kolom identitas siswa).
     */
    public static function configure(Table $table, bool $forAdmin = true): Table
    {
        return $table
            // hanya ujian yang sudah selesai dikerjakan
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->whereNotNull('completed_at')
                ->with(['student', 'exam', 'subjectResults']))
            ->defaultSort('started_at', 'desc')
            ->emptyStateHeading('Belum ada rekap ujian')
            ->columns([
                TextColumn::make('#')
                    ->rowIndex()
                    ->width(40),
                TextColumn::make('student.nis')
                    ->label('NIS')
                    ->searchable()
                    ->visible($forAdmin),
                TextColumn::make('student.name')
                    ->label('Nama siswa')
                    ->searchable()
                    ->visible($forAdmin),
                TextColumn::make('exam.title')
                    ->label('Ujian')
                    ->searchable(),
                TextColumn::make('subjectResults.subject_name')
                    ->label('Mata pelajaran')
                    ->badge(),
                TextColumn::make('started_at')
                    ->label('Tanggal ujian')
                    ->dateTime('d F Y, H:i')
                    ->sortable(),
                TextColumn::make('score')
                    ->label('Skor total')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('correct_answers')
                    ->label('Benar')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('wrong_answers')
                    ->label('Salah')
                    ->color('danger')
                    ->sortable(),
                IconColumn::make('passed')
                    ->label('Lulus')
                    ->boolean()
                    ->state(fn ($record) => $record->exam
                        && (float) $record->score >= (float) $record->exam->threshold),
            ])
            ->filters([
                SelectFilter::make('student_id')
                    ->label('Siswa')
                    ->relationship('student', 'name')
                    ->searchable()
                    ->preload()
                    ->visible($forAdmin),
                SelectFilter::make('exam_id')
                    ->label('Ujian')
                    ->relationship('exam', 'title')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('subject')
                    ->label('Mata pelajaran')
                    ->options(fn () => Subject::query()
                        ->orderBy('name')
                        ->pluck('name', 'name')
                        ->all())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->whereHas(
                            'subjectResults',
                            fn (Builder $q) => $q->where('subject_name', $data['value'])
                        )
                        : $query),
                TernaryFilter::make('passed')
                    ->label('Status kelulusan')
                    ->placeholder('Semua')
                    ->trueLabel('Lulus')
                    ->falseLabel('Belum lulus')
                    ->native(false)
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas(
                            'exam',
                            fn (Builder $q) => $q->whereColumn('exam_attempts.score', '>=', 'exams.threshold')
                        ),
                        false: fn (Builder $query) => $query->whereHas(
                            'exam',
                            fn (Builder $q) => $q->whereColumn('exam_attempts.score', '<', 'exams.threshold')
                        ),
                        blank: fn (Builder $query) => $query,
                    ),
                Filter::make('tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari tanggal'),
                        DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('started_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('started_at', '<=', $date))),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),
            ]);
    }
}