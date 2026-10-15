<?php

namespace App\Filament\Test\Pages;

use App\Filament\Support\SubjectHistoryTable;
use App\Models\ExamAttemptSubject;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class SubjectHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected string $view = 'filament.test.pages.subject-history';

    public static function getNavigationLabel(): string
    {
        return 'Histori Pelajaran';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Histori Pelajaran';
    }

    public function table(Table $table): Table
    {
        return SubjectHistoryTable::configure($table)
            ->query(fn () => ExamAttemptSubject::query()
                ->where('student_id', auth()->user()?->student?->id ?? 0));
    }
}