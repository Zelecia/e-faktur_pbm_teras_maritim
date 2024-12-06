<?php

namespace App\Filament\Widgets;

use App\Models\Faktur;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class TotalPajakChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Pajak DPP & PPN';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '150px';

    protected function getData(): array
    {
        // Ambil data DPP dan PPN untuk bulan ini (atau bisa diganti sesuai kebutuhan)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $data = Faktur::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->selectRaw('SUM(dpp) as total_dpp, SUM(ppn) as total_ppn')
            ->first();

        // Pastikan jika tidak ada data, set menjadi 0
        $totalDPP = $data->total_dpp ?? 0;
        $totalPPN = $data->total_ppn ?? 0;

        // Jika PPN berupa angka kecil (misalnya 1 berarti 1%), maka hitung total PPN dalam bentuk nominal
        $calculatedPPN = $totalDPP * ($totalPPN / 100); // Hitung nilai PPN berdasarkan persen

        // Format data untuk chart
        return [
            'labels' => ['Total DPP', 'Total PPN'], // Label untuk bagian chart
            'datasets' => [
                [
                    'label' => 'Pajak', // Label chart
                    'data' => [$totalDPP, $calculatedPPN], // Data yang ditampilkan
                    'backgroundColor' => ['#1E88E5', '#023e8a'],
                    'borderColor' => ['#fff', '#fff'],
                    'borderRadius' => 20,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
