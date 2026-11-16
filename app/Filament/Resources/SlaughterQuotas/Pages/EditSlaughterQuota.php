<?php

namespace App\Filament\Resources\SlaughterQuotas\Pages;

use App\Filament\Resources\SlaughterQuotas\SlaughterQuotaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSlaughterQuota extends EditRecord
{
    protected static string $resource = SlaughterQuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
