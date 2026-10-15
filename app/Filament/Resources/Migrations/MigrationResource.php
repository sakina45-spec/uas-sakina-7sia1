<?php

namespace App\Filament\Resources\Migrations;

use App\Filament\Resources\Migrations\Pages\ListMigrations;
use App\Models\Migration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MigrationResource extends Resource
{
    protected static ?string $model = Migration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Basis Data';

    protected static ?string $navigationLabel = 'Migrations';

    protected static ?string $modelLabel = 'Migrasi';

    protected static ?string $pluralModelLabel = 'Tabel Migrasi';

    protected static ?int $navigationSort = 99;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('migration')
                    ->label('Nama Migrasi')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('batch')
                    ->label('Batch')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMigrations::route('/'),
        ];
    }
}