<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reg_year')
                    ->label('Tahun masuk'),
                TextEntry::make('nis')
                    ->label('Nomor Induk Siswa'),
                TextEntry::make('name')
                    ->label('Nama lengkap')
                    ->columnSpanFull(),
                TextEntry::make('gender')
                    ->formatStateUsing(
                        fn (bool $state) => $state
                            ? 'Laki-laki'
                            : 'Perempuan'
                    ),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Student $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
