<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use App\Filament\Resources\Questions\Imports\QuestionCsvImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importCsv')
                ->label('Upload Soal CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('File CSV')
                        ->required()
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                        ])
                        ->disk('local')
                        ->directory('imports')
                        ->preserveFilenames(),
                ])
                ->action(function (array $data): void {
                    QuestionCsvImporter::import(
                        storage_path('app/private/' . $data['file'])
                    );
                })
                ->successNotificationTitle('Soal berhasil diimport'),
            
            CreateAction::make(),
        ];
    }
}