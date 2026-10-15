<?php

namespace App\Filament\Resources\Migrations\Pages;

use App\Filament\Resources\Migrations\MigrationResource;
use Filament\Resources\Pages\ListRecords;

class ListMigrations extends ListRecords
{
    protected static string $resource = MigrationResource::class;
}