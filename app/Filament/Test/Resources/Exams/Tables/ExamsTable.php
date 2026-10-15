<?php

namespace App\Filament\Test\Resources\Exams\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Ujian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Durasi')
                    ->suffix(' menit')
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('expired_at')
                    ->label('Berakhir')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Tidak ditentukan')
                    ->sortable(),

                TextColumn::make('threshold')
                    ->label('KKM')
                    ->suffix(' poin')
                    ->sortable(),

                TextColumn::make('is_available')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (bool $state): string =>
                            $state ? 'Tersedia' : 'Tidak Tersedia'
                    )
                    ->color(
                        fn (bool $state): string =>
                            $state ? 'success' : 'danger'
                    ),
            ])

            ->filters([])

            ->recordActions([
                Action::make('mulai')
                    ->label('Mulai Ujian')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->url(
                        fn ($record): string =>
                            url('/ujian/exams/' . $record->id . '/start')
                    ),
            ])

            ->toolbarActions([]);
    }
}