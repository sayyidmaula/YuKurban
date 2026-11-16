<?php

namespace App\Filament\Resources\FeeRules\Schemas;

use App\Enums\AnimalType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FeeRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('animal_type')
                    ->label('Jenis Hewan')
                    ->options([
                        'kambing' => 'Kambing',
                        'domba' => 'Domba',
                        'sapi' => 'Sapi',
                    ])
                    ->required(),
                TextInput::make('fee_slaughter')
                    ->label('Ujrah Sembelih (Rp)')
                    ->prefix('Rp')
                    ->required()
                    ->numeric(),
                TextInput::make('fee_distribution')
                    ->label('Ujrah Distribusi (Rp)')
                    ->prefix('Rp')
                    ->required()
                    ->numeric(),
                TextInput::make('commission_peternak')
                    ->label('Komisi Platform dari Peternak (Rp)')
                    ->prefix('Rp')
                    ->required()
                    ->numeric(),
            ]);
    }
}
