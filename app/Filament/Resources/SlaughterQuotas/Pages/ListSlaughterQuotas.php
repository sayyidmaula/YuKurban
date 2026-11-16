<?php

namespace App\Filament\Resources\SlaughterQuotas\Pages;

use App\Filament\Resources\SlaughterQuotas\SlaughterQuotaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlaughterQuotas extends ListRecords
{
    protected static string $resource = SlaughterQuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
