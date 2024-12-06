<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BarangJasa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'referensi',
        'nama_pekerjaan',
        'nama_kapal',
        'lokasi',
        'tanggal_mulai',
        'tanggal_selesai',
        'subtotal'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically calculate subtotal when saving or updating
        static::saving(function ($model) {
            $model->calculateSubtotal();
        });
    }

    public function calculateSubtotal(): void
    {
        // Recalculate subtotal from related UraianBarang items
        $this->subtotal = $this->uraianBarang->sum(function ($uraian) {
            return $uraian->harga_per_unit * $uraian->kuantitas;
        });
    }

    public function faktur(): HasMany
    {
        return $this->hasMany(Faktur::class);
    }

    public function uraianBarang(): HasMany
    {
        return $this->HasMany(UraianBarang::class);
    }
}
