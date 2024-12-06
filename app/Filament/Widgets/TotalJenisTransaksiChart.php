<?php

namespace App\Filament\Widgets;

use App\Models\Faktur;
use App\Models\TipeFaktur;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TotalJenisTransaksiChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Jenis Transaksi';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '150px';

    protected function getData(): array
    {
        // Ambil data jumlah faktur per jenis transaksi
        $data = Faktur::select('tipe_faktur_id', DB::raw('count(*) as total'))
            ->groupBy('tipe_faktur_id')
            ->get();

        // Siapkan data untuk chart
        $labels = [];  // Nama jenis transaksi
        $values = [];  // Jumlah faktur

        foreach ($data as $item) {
            $tipeFaktur = TipeFaktur::find($item->tipe_faktur_id);
            $labels[] = $tipeFaktur->nama ?? 'Unknown';  // Nama jenis transaksi
            $values[] = $item->total;  // Jumlah faktur
        }

        // Format data untuk chart
        return [
            'labels' => $labels, // Label untuk setiap jenis transaksi
            'datasets' => [
                [
                    'label' => 'Jumlah Faktur',  // Label dataset
                    'data' => $values,  // Data yang ditampilkan
                    'backgroundColor' => 'rgba(30, 136, 229, 0.2)',  // Warna latar area di bawah garis
                    'borderColor' => '#1E88E5',  // Warna garis
                    'borderWidth' => 2,  // Ketebalan garis
                    'fill' => true,  // Mengisi area di bawah garis
                    'tension' => 0.3,  // Menambahkan kurva pada garis
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
