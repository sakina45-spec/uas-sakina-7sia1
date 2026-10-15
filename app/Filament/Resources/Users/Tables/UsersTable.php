<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('No.')  // dengan judul kolom
                    ->rowIndex()    // method penomoran baris
                    ->width(40),
                ImageColumn::make('avatar')
                    ->label(false)  // tanpa judul kolom
                    ->circular()
                    ->default(fn (User $record) =>
                        $record->getFilamentAvatarUrl()
                    )
                    ->width(40),
                TextColumn::make('name')
                    ->label('Nama lengkap')
                    ->searchable()  // dapat dicari (search)
                    ->sortable(),   // dapat diurutkan
                TextColumn::make('email')
                    ->label('Alamat email')
                    ->searchable(),
                TextColumn::make('username')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->placeholder('-')  // Teks yang ditampilkan jika null
                    ->searchable()
                    ->toggleable(),
                // IconColumn::make('is_staff')
                //     ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d F Y, H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d F Y, H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
