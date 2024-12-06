<?php

namespace App\Models;

use App\Policies\PenandatanganPolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Penandatangan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
    ];

    protected $policies = [
        Penandatangan::class => PenandatanganPolicy::class,
    ];

    public function faktur(): HasMany
    {
        return $this->hasMany(Faktur::class);
    }
}
