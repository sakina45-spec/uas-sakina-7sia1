<?php

namespace App\Filament\Test\Resources\ExamHistories;

use App\Filament\Resources\ExamAttempts\RelationManagers\AnswersRelationManager;
use App\Filament\Resources\ExamAttempts\Schemas\ExamAttemptInfolist;
use App\Filament\Resources\ExamAttempts\Tables\ExamAttemptsTable;
use App\Filament\Test\Resources\ExamHistories\Pages\ListExamHistories;
use App\Filament\Test\Resources\ExamHistories\Pages\ViewExamHistory;
use App\Models\ExamAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExamHistoryResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;



    protected static ?string $navigationLabel = 'Riwayat Ujian';

    protected static ?string $modelLabel = 'riwayat ujian';

    protected static ?string $pluralModelLabel = 'riwayat ujian';

    // siswa hanya boleh melihat riwayat miliknya sendiri
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('student_id', auth()->user()?->student?->id ?? 0)
            ->whereNotNull('completed_at');
    }
    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return $record?->exam?->title ?? 'Riwayat ujian';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return ExamAttemptInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamAttemptsTable::configure($table, forAdmin: false);
    }

    public static function getRelations(): array
    {
        return [
            AnswersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamHistories::route('/'),
            'view' => ViewExamHistory::route('/{record}'),
        ];
    }
}