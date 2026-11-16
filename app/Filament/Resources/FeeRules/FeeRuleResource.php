<?php

namespace App\Filament\Resources\FeeRules;

use App\Filament\Resources\FeeRules\Pages\CreateFeeRule;
use App\Filament\Resources\FeeRules\Pages\EditFeeRule;
use App\Filament\Resources\FeeRules\Pages\ListFeeRules;
use App\Filament\Resources\FeeRules\Schemas\FeeRuleForm;
use App\Filament\Resources\FeeRules\Tables\FeeRulesTable;
use App\Models\FeeRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FeeRuleResource extends Resource
{
    protected static ?string $model = FeeRule::class;

    protected static ?string $navigationLabel = 'Aturan Biaya & Komisi';

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return FeeRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeeRulesTable::configure($table);
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
            'index' => ListFeeRules::route('/'),
            'create' => CreateFeeRule::route('/create'),
            'edit' => EditFeeRule::route('/{record}/edit'),
        ];
    }
}
