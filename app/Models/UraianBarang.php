<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class UraianBarang extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'barang_jasa_id',
        'nama',
        'harga_per_unit',
        'kuantitas',
        'created_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($uraianBarang) {
            $uraianBarang->barangJasa->calculateSubtotal();
            $uraianBarang->barangJasa->save();
        });

        static::deleted(function ($uraianBarang) {
            $uraianBarang->barangJasa->calculateSubtotal();
            $uraianBarang->barangJasa->save();
        });
    }

    public function barangJasa(): BelongsTo
    {
        return $this->BelongsTo(BarangJasa::class, 'barang_jasa_id', 'id');
    }
}
