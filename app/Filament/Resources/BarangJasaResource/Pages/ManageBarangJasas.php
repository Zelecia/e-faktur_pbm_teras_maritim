<?php

namespace App\Filament\Resources\BarangJasaResource\Pages;

use App\Filament\Resources\BarangJasaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBarangJasas extends ManageRecords
{
    protected static string $resource = BarangJasaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Barang / Jasa'),
        ];
    }
}
