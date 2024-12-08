<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faktur extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipe_faktur_id',
        'pelanggan_id',
        'penandatangan_id',
        'referensi_id',
        'nomor',
        'tanggal',
        'masa',
        'tahun',
        'dpp',
        'ppn',
        'status',
        'tanggal_approval'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Jika nomor faktur belum ada, buat nomor faktur baru
            if (empty($model->nomor)) {
                $model->nomor = $model->generateNomorFaktur();
            }
            // Jika status diubah, update hanya kode status dalam nomor faktur
            elseif ($model->isDirty('status')) {
                $model->nomor = $model->updateKodeStatusInNomor();
            }
            // Jika tipe_faktur_id diubah, update hanya kode transaksi dalam nomor faktur
            elseif ($model->isDirty('tipe_faktur_id')) {
                $model->nomor = $model->updateKodeTransaksiInNomor();
            }
        });
    }

    public function updateKodeStatusInNomor(): string
    {
        $kodeStatus = $this->getKodeStatus(); // Dapatkan kode status terbaru

        // Ganti hanya bagian kode status (3 digit kedua) dalam format nomor faktur
        return preg_replace('/^(\d{3})\.\d{3}-/', "$1.{$kodeStatus}-", $this->nomor);
    }

    public function updateKodeTransaksiInNomor(): string
    {
        $kodeTransaksi = $this->getKodeTransaksi(); // Dapatkan kode transaksi terbaru

        // Ganti hanya bagian kode transaksi (3 digit pertama) dalam format nomor faktur
        return preg_replace('/^\d{3}/', $kodeTransaksi, $this->nomor);
    }

    public function generateNomorFaktur(): string
    {
        // Menentukan kode transaksi berdasarkan tipe faktur
        $kodeTransaksi = $this->getKodeTransaksi();
        // Menentukan kode status berdasarkan status faktur
        $kodeStatus = $this->getKodeStatus();
        // Mendapatkan kode tahun (dua digit terakhir tahun)
        $kodeTahun = $this->getKodeTahun();
        // Mendapatkan nomor urut faktur pajak
        $serialNumber = $this->getNomorUrutFaktur();

        // Format nomor faktur pajak sesuai format yang diinginkan
        return "{$kodeTransaksi}.{$kodeStatus}-{$kodeTahun}.{$serialNumber}";
    }

    public function getKodeTransaksi(): string
    {
        switch ($this->tipe_faktur_id) {
            case 1:
                return '010';
            case 2:
                return '020';
            case 3:
                return '030';
            case 4:
                return '040';
            case 5:
                return '050';
            case 6:
                return '060';
            case 7:
                return '070';
            case 8:
                return '080';
            case 9:
                return '090';
            default:
                return '000';
        }
    }

    public function getKodeStatus(): string
    {
        return $this->status == 1 ? '001' : '002'; // Kode status: 1 untuk diterima, 2 untuk ditolak
    }

    public function getKodeTahun(): string
    {
        return substr(now()->year, -2); // Ambil dua digit terakhir dari tahun
    }

    public function getNomorUrutFaktur(): string
    {
        // Ambil faktur terakhir untuk bulan dan tahun yang sama
        $lastFaktur = self::whereYear('tanggal', now()->year)
            ->whereMonth('tanggal', now()->month)
            ->latest('id')
            ->first();

        // Jika ada faktur sebelumnya, tambahkan 1
        $nomorUrut = $lastFaktur ? (int)substr($lastFaktur->nomor, -8) + 1 : 1;

        // Pastikan nomor urut terdiri dari 8 digit
        return str_pad($nomorUrut, 8, '0', STR_PAD_LEFT);
    }

    public function tipeFaktur(): BelongsTo
    {
        return $this->belongsTo(TipeFaktur::class, 'tipe_faktur_id', 'id');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id', 'id');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(Penandatangan::class, 'penandatangan_id', 'id');
    }

    public function referensi(): BelongsTo
    {
        return $this->belongsTo(BarangJasa::class, 'referensi_id', 'id');
    }
}
