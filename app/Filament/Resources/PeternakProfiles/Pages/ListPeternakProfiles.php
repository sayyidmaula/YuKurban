<?php

namespace App\Filament\Resources\PeternakProfiles\Pages;

use App\Filament\Resources\PeternakProfiles\PeternakProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPeternakProfiles extends ListRecords
{
    protected static string $resource = PeternakProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
