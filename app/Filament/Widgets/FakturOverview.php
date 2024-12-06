<?php

namespace App\Filament\Widgets;

use App\Models\Faktur;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FakturOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Total Faktur Tahun Ini
        $totalFakturTahunIni = Faktur::whereYear('tanggal', now()->year)->count();
        $totalFakturTahunLalu = Faktur::whereYear('tanggal', now()->year - 1)->count();

        // Persentase kenaikan/penurunan tahun
        $descriptionTahun = $totalFakturTahunLalu > 0
            ? "Total Faktur " . ($totalFakturTahunIni >= $totalFakturTahunLalu ? "Naik" : "Turun") . " " . number_format(abs((($totalFakturTahunIni - $totalFakturTahunLalu) / $totalFakturTahunLalu) * 100), 2) . "% dari tahun lalu"
            : "Total Faktur belum mengalami peningkatan";
        $iconTahun = $totalFakturTahunIni >= $totalFakturTahunLalu ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorTahun = $totalFakturTahunIni >= $totalFakturTahunLalu ? 'success' : 'danger';

        // Total Faktur Bulan Ini
        $totalFakturBulanIni = Faktur::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $totalFakturBulanLalu = Faktur::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month - 1)
            ->count();
        $descriptionBulan = $totalFakturBulanLalu > 0
            ? "Total Faktur " . ($totalFakturBulanIni >= $totalFakturBulanLalu ? "Naik" : "Turun") . " " . number_format(abs((($totalFakturBulanIni - $totalFakturBulanLalu) / $totalFakturBulanLalu) * 100), 2) . "% dari bulan lalu"
            : "Total Faktur belum mengalami peningkatan";
        $iconBulan = $totalFakturBulanIni >= $totalFakturBulanLalu ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorBulan = $totalFakturBulanIni >= $totalFakturBulanLalu ? 'success' : 'danger';

        // Total Faktur Diterima Bulan Ini
        $totalFakturDiterima = Faktur::where('status', 1)
            ->whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $totalFakturDiterimaSebelumnya = Faktur::where('status', 1)
            ->whereYear('tanggal', now()->year - 1)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $descriptionDiterima = $totalFakturDiterimaSebelumnya > 0
            ? "Total Diterima " . ($totalFakturDiterima >= $totalFakturDiterimaSebelumnya ? "Naik" : "Turun") . " " . number_format(abs((($totalFakturDiterima - $totalFakturDiterimaSebelumnya) / $totalFakturDiterimaSebelumnya) * 100), 2) . "% dari bulan lalu"
            : "Total Faktur Diterima belum mengalami peningkatan";
        $iconDiterima = $totalFakturDiterima >= $totalFakturDiterimaSebelumnya ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorDiterima = $totalFakturDiterima >= $totalFakturDiterimaSebelumnya ? 'success' : 'danger';

        // Total Faktur Ditolak Bulan Ini
        $totalFakturDitolak = Faktur::where('status', 2)
            ->whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $totalFakturDitolakSebelumnya = Faktur::where('status', 2)
            ->whereYear('tanggal', now()->year - 1)
            ->whereMonth('tanggal', now()->month)
            ->count();
        $descriptionDitolak = $totalFakturDitolakSebelumnya > 0
            ? "Total Ditolak " . ($totalFakturDitolak >= $totalFakturDitolakSebelumnya ? "Naik" : "Turun") . " " . number_format(abs((($totalFakturDitolak - $totalFakturDitolakSebelumnya) / $totalFakturDitolakSebelumnya) * 100), 2) . "% dari bulan lalu"
            : "Total Faktur Ditolak belum mengalami peningkatan";
        $iconDitolak = $totalFakturDitolak >= $totalFakturDitolakSebelumnya ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $colorDitolak = $totalFakturDitolak >= $totalFakturDitolakSebelumnya ? 'success' : 'danger';

        return [
            Stat::make('Total Faktur Tahun Ini', $totalFakturTahunIni)
                ->description($descriptionTahun)
                ->descriptionIcon($totalFakturTahunLalu > 0 ? $iconTahun : null)
                ->color($totalFakturTahunLalu > 0 ? $colorTahun : 'gray'),

            Stat::make('Total Faktur Bulan Ini', $totalFakturBulanIni)
                ->description($descriptionBulan)
                ->descriptionIcon($totalFakturBulanLalu > 0 ? $iconBulan : null)
                ->color($totalFakturBulanLalu > 0 ? $colorBulan : 'gray'),

            Stat::make('Total Faktur Diterima', $totalFakturDiterima)
                ->description($descriptionDiterima)
                ->descriptionIcon($totalFakturDiterimaSebelumnya > 0 ? $iconDiterima : null)
                ->color($totalFakturDiterimaSebelumnya > 0 ? $colorDiterima : 'gray'),

            Stat::make('Total Faktur Ditolak', $totalFakturDitolak)
                ->description($descriptionDitolak)
                ->descriptionIcon($totalFakturDitolakSebelumnya > 0 ? $iconDitolak : null)
                ->color($totalFakturDitolakSebelumnya > 0 ? $colorDitolak : 'gray'),
        ];
    }
}
