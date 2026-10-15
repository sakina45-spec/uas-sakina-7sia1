<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Filament\Support\SubjectHistoryTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class SubjectHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'subjectResults';

    protected static string|null $title = 'Histori Pelajaran';

    public function table(Table $table): Table
    {
        return SubjectHistoryTable::configure($table)
            ->recordTitleAttribute('subject_name');
    }
}