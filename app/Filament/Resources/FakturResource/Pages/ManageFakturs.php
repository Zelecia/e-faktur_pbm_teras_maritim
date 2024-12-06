<?php

namespace App\Filament\Resources\FakturResource\Pages;

use App\Filament\Resources\FakturResource;
use App\Filament\Resources\FakturResource\Widgets\StatsOverview;
use App\Models\TipeFaktur;
use Filament\Actions;
use Filament\Forms\Components\Builder;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;

class ManageFakturs extends ManageRecords
{
    protected static string $resource = FakturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Faktur'),
        ];
    }

    public function getTabs(): array
    {
        // Ambil semua tipe faktur
        $tipeFakturs = TipeFaktur::all();

        // Membuat array untuk tabs
        $tabs = [
            'Tampilkan Semua' => Tab::make(),
        ];

        // Tambahkan tab untuk setiap tipe faktur
        foreach ($tipeFakturs as $tipeFaktur) {
            $tabs[$tipeFaktur->nama] = Tab::make()
                // Modifikasi query di sini, pastikan Anda menggunakan query yang benar
                ->modifyQueryUsing(function ($query) use ($tipeFaktur) {
                    return $query->where('tipe_faktur_id', $tipeFaktur->id);
                });
        }

        return $tabs;
    }
}
