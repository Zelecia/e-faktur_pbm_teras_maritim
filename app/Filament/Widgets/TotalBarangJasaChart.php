<?php

namespace App\Filament\Widgets;

use App\Models\UraianBarang;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class TotalBarangJasaChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Barang Jasa Terjual';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '150px';

    protected function getData(): array
    {
        // Ambil data penjualan dari beberapa bulan terakhir
        // Menentukan batas waktu yang diinginkan, misalnya 6 bulan terakhir
        $startDate = Carbon::now()->subMonths(6)->startOfMonth(); // Mulai dari 6 bulan yang lalu

        // Ambil kuantitas yang terjual dari UraianBarang untuk beberapa bulan
        $data = UraianBarang::where('created_at', '>=', $startDate)
            ->selectRaw('SUM(kuantitas) as total_kuantitas, MONTH(created_at) as bulan, YEAR(created_at) as tahun')
            ->groupBy('bulan', 'tahun')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->get();

        // Siapkan data untuk chart
        $labels = []; // Untuk menyimpan nama bulan
        $quantities = []; // Untuk menyimpan total kuantitas yang terjual

        // Ambil data dan siapkan ke dalam format yang benar
        foreach ($data as $item) {
            // Format bulan dan tahun untuk label
            $labels[] = Carbon::parse("{$item->tahun}-{$item->bulan}-01")->format('F Y'); // Format nama bulan dan tahun
            $quantities[] = $item->total_kuantitas; // Total kuantitas per bulan
        }

        // Jika tidak ada data, pastikan chart tetap tampil dengan label kosong
        if (empty($quantities)) {
            $quantities = [0];
            $labels = ['No Data Available'];
        }

        // Format data untuk chart
        return [
            'labels' => $labels, // Label yang akan ditampilkan di sumbu X
            'datasets' => [
                [
                    'label' => 'Jumlah Barang/Jasa Terjual',
                    'data' => $quantities,
                    'backgroundColor' => '#1E88E5',
                    'borderColor' => '#1E88E5',
                    'pointBackgroundColor' => '#1E88E5',
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
