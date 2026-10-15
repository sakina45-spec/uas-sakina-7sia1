<?php

namespace App\Filament\Resources\Exams\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjects';

    protected static string|null $title = 'Pelajaran yang diujiankan';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('#')
                    ->rowIndex()
                    ->width('10px'),

                TextColumn::make('name')
                    ->label('Pelajaran')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Tersedia')
                    ->boolean(),

                TextColumn::make('pivot.qty')
                    ->label('Jumlah Soal'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Pelajaran')
                    ->preloadRecordSelect()
                    ->schema(function (AttachAction $action): array {
                        return [
                            $action->getRecordSelect(),

                            TextInput::make('qty')
                                ->label('Jumlah Soal')
                                ->numeric()
                                ->integer()
                                ->minValue(1)
                                ->required()
                                ->default(10),
                        ];
                    })
                    ->modalSubmitActionLabel('Tambah')
                    ->attachAnother(false),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Lepaskan Pelajaran'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Lepaskan Pelajaran yang Dipilih'),
                ]),
            ])
            ->modifyQueryUsing(
                fn (Builder $query) => $query->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ])
            );
    }
}