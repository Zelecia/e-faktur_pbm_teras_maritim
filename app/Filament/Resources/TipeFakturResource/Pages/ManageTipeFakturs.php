<?php

namespace App\Filament\Resources\TipeFakturResource\Pages;

use App\Filament\Resources\TipeFakturResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTipeFakturs extends ManageRecords
{
    protected static string $resource = TipeFakturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Tipe Faktur'),
        ];
    }
}
