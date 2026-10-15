<?php

namespace App\Filament\Resources\Exams\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Nama Ujian')
                ->placeholder('Contoh: Ujian TKU')
                ->required()
                ->maxLength(255),

            TextInput::make('duration')
                ->label('Durasi Ujian (menit)')
                ->numeric()
                ->integer()
                ->minValue(1)
                ->required(),

            TextInput::make('threshold')
                ->label('Nilai Ambang Batas')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->default(50)
                ->required(),

            Toggle::make('exact_time')
                ->label('Gunakan Jadwal Ujian')
                ->default(true)
                ->required(),

            DateTimePicker::make('started_at')
                ->label('Jadwal Mulai Ujian')
                ->seconds(false)
                ->native(false)
                ->displayFormat('d F Y, H:i')
                ->required(),

            DateTimePicker::make('expired_at')
                ->label('Jadwal Berakhir Ujian')
                ->seconds(false)
                ->native(false)
                ->displayFormat('d F Y, H:i')
                ->required(),

            Toggle::make('is_available')
                ->label('Ujian Tersedia untuk Siswa')
                ->default(true)
                ->required(),
        ]);
    }
}