<?php

namespace App\Models;

use App\Policies\PelangganPolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'npwp',
        'nama',
        'alamat',
        'nomor_telepon',
        'email',
    ];

    protected $policy = [
        Pelanggan::class => PelangganPolicy::class,
    ];

    public function setNomorTeleponAttribute($value)
    {
        // Jika nomor dimulai dengan "08", ubah ke "+628"
        if (strpos($value, '08') === 0) {
            $this->attributes['nomor_telepon'] = '+62' . substr($value, 1);
        }
        // Jika nomor dimulai dengan "8", tambahkan "+62"
        elseif (strpos($value, '8') === 0) {
            $this->attributes['nomor_telepon'] = '+62' . $value;
        }
        // Jika nomor sudah diawali dengan "+62", biarkan tanpa perubahan
        else {
            $this->attributes['nomor_telepon'] = $value;
        }
    }

    public function faktur(): HasMany
    {
        return $this->hasMany(Faktur::class);
    }
}
