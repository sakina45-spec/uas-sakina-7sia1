<?php

namespace App\Filament\Resources\ExamAttempts\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExamAttemptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Siswa')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('student.nis')
                            ->label('NIS'),
                        TextEntry::make('student.name')
                            ->label('Nama'),
                    ]),
                Section::make('Ujian')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('exam.title')
                            ->label('Ujian'),
                        TextEntry::make('started_at')
                            ->label('Mulai')
                            ->dateTime('d F Y, H:i:s'),
                        TextEntry::make('completed_at')
                            ->label('Selesai')
                            ->dateTime('d F Y, H:i:s')
                            ->placeholder('Belum selesai'),
                    ]),
                Section::make('Hasil')
                    ->columns(5)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('score')
                            ->label('Skor total')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('correct_answers')
                            ->label('Benar')
                            ->color('success'),
                        TextEntry::make('wrong_answers')
                            ->label('Salah')
                            ->color('danger'),
                        TextEntry::make('exam.threshold')
                            ->label('KKM')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('status')
                            ->label('Status')
                            ->state(fn ($record) => $record->exam
                                && (float) $record->score >= (float) $record->exam->threshold
                                    ? 'Lulus'
                                    : 'Belum lulus')
                            ->badge()
                            ->color(fn (string $state) => $state === 'Lulus' ? 'success' : 'danger'),
                    ]),
                Section::make('Rekap per mata pelajaran')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('subjectResults')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('subject_name')
                                    ->label('Mata pelajaran'),
                                TextEntry::make('correct_count')
                                    ->label('Benar')
                                    ->color('success'),
                                TextEntry::make('wrong_count')
                                    ->label('Salah')
                                    ->color('danger'),
                                TextEntry::make('score')
                                    ->label('Skor')
                                    ->numeric(decimalPlaces: 2),
                            ]),
                    ]),
            ]);
    }
}