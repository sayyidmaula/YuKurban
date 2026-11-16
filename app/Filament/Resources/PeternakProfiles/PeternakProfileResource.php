<?php

namespace App\Filament\Resources\PeternakProfiles;

use App\Filament\Resources\PeternakProfiles\Pages\CreatePeternakProfile;
use App\Filament\Resources\PeternakProfiles\Pages\EditPeternakProfile;
use App\Filament\Resources\PeternakProfiles\Pages\ListPeternakProfiles;
use App\Filament\Resources\PeternakProfiles\Schemas\PeternakProfileForm;
use App\Filament\Resources\PeternakProfiles\Tables\PeternakProfilesTable;
use App\Models\PeternakProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PeternakProfileResource extends Resource
{
    protected static ?string $model = PeternakProfile::class;

    protected static ?string $navigationLabel = 'Mitra Peternak';

    protected static string|UnitEnum|null $navigationGroup = 'Kemitraan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PeternakProfileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PeternakProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeternakProfiles::route('/'),
            'create' => CreatePeternakProfile::route('/create'),
            'edit' => EditPeternakProfile::route('/{record}/edit'),
        ];
    }
}
