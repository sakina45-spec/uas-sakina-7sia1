<?php

namespace App\Filament\Resources\ExamAttempts;

use App\Filament\Resources\ExamAttempts\Pages\ListExamAttempts;
use App\Filament\Resources\ExamAttempts\Pages\ViewExamAttempt;
use App\Filament\Resources\ExamAttempts\RelationManagers\AnswersRelationManager;
use App\Filament\Resources\ExamAttempts\Schemas\ExamAttemptInfolist;
use App\Filament\Resources\ExamAttempts\Tables\ExamAttemptsTable;
use App\Models\ExamAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ExamAttemptResource extends Resource
{
    protected static ?string $model = ExamAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

   

    protected static string|UnitEnum|null $navigationGroup = 'Hasil Ujian';

    protected static ?string $navigationLabel = 'Rekap Ujian';

    protected static ?string $modelLabel = 'rekap ujian';

    protected static ?string $pluralModelLabel = 'rekap ujian';

    // rekap dibuat otomatis saat siswa mengirim jawaban, bukan diisi manual
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
        return ExamAttemptsTable::configure($table);
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
            'index' => ListExamAttempts::route('/'),
            'view' => ViewExamAttempt::route('/{record}'),
        ];
    }
        public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return $record?->exam?->title ?? 'Rekap ujian';
    }
}