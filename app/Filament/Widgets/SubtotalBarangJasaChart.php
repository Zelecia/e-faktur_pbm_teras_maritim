<?php

namespace App\Filament\Widgets;

use App\Models\BarangJasa;
use Filament\Widgets\ChartWidget;

class SubtotalBarangJasaChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Subtotal Barang / Jasa';

    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '150px';

    // Define a static array of colors
    protected static $colorPalette = ['#1E88E5', '#023e8a', '#0077b6', '#0096c7', '#00b4d8', '#48cae4', '#90e0ef', 'ade8f4', 'caf0f8'];

    protected function getData(): array
    {
        // Fetch data and calculate subtotals
        $data = BarangJasa::with('uraianBarang')
            ->get()
            ->map(function ($barangJasa) {
                $subtotal = $barangJasa->uraianBarang->sum(fn($uraian) => $uraian->harga_per_unit * $uraian->kuantitas);

                return [
                    'nama' => $barangJasa->nama_pekerjaan,
                    'subtotal' => $subtotal,
                ];
            })
            ->take(9); // Limit to the first 9 items

        // Prepare labels and values
        $labels = [];
        $values = [];

        foreach ($data as $item) {
            $labels[] = $item['nama'];
            $values[] = $item['subtotal'];
        }

        // Assign colors, reusing from the palette if needed
        $backgroundColors = [];
        for ($i = 0; $i < count($values); $i++) {
            $backgroundColors[] = self::$colorPalette[$i % count(self::$colorPalette)];
        }

        // Return formatted data
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Subtotal',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => '#ffffff',
                    'borderRadius' => 5, // Adjust radius for bar chart
                    'barThickness' => 30, // Control bar width
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
