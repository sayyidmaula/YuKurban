<?php

namespace App\Filament\Resources\PeternakProfiles\Pages;

use App\Filament\Resources\PeternakProfiles\PeternakProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPeternakProfile extends EditRecord
{
    protected static string $resource = PeternakProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
