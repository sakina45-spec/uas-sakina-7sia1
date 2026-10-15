<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\Summarizers\Average;
use Filament\Tables\Columns\Summarizers\Range;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubjectHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('attempt.exam'))
            ->defaultGroup(
                Group::make('subject_name')
                    ->label('Mata pelajaran')
                    ->collapsible()
            )
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada histori pelajaran')
            ->emptyStateDescription('Nilai akan muncul di sini setelah kamu menyelesaikan ujian.')
            ->columns([
                TextColumn::make('attempt.exam.title')
                    ->label('Ujian'),
                TextColumn::make('attempt.started_at')
                    ->label('Tanggal ujian')
                    ->dateTime('d F Y, H:i'),
                TextColumn::make('correct_count')
                    ->label('Benar')
                    ->color('success')
                    ->summarize(Sum::make()->label('Total benar')),
                TextColumn::make('wrong_count')
                    ->label('Salah')
                    ->color('danger')
                    ->summarize(Sum::make()->label('Total salah')),
                TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric(decimalPlaces: 2)
                    ->summarize([
                        Average::make()
                            ->label('Rata-rata')
                            ->numeric(decimalPlaces: 2),
                        Range::make()
                            ->label('Terendah - tertinggi'),
                    ]),
            ]);
    }
}