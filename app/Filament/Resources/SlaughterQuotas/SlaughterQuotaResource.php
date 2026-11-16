<?php

namespace App\Filament\Resources\SlaughterQuotas;

use App\Filament\Resources\SlaughterQuotas\Pages\CreateSlaughterQuota;
use App\Filament\Resources\SlaughterQuotas\Pages\EditSlaughterQuota;
use App\Filament\Resources\SlaughterQuotas\Pages\ListSlaughterQuotas;
use App\Filament\Resources\SlaughterQuotas\Schemas\SlaughterQuotaForm;
use App\Filament\Resources\SlaughterQuotas\Tables\SlaughterQuotasTable;
use App\Models\SlaughterQuota;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SlaughterQuotaResource extends Resource
{
    protected static ?string $model = SlaughterQuota::class;

    protected static ?string $navigationLabel = 'Kuota Sembelih';

    protected static string|UnitEnum|null $navigationGroup = 'Operasional Kurban';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SlaughterQuotaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlaughterQuotasTable::configure($table);
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
            'index' => ListSlaughterQuotas::route('/'),
            'create' => CreateSlaughterQuota::route('/create'),
            'edit' => EditSlaughterQuota::route('/{record}/edit'),
        ];
    }
}
