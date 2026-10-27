<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    public const PER_DAY = 'per_day';

    public const ONCE = 'once';

    /** Kode tambahan antar-jemput, otomatis dipakai bila lokasi = antar ke alamat. */
    public const CODE_DELIVERY = 'delivery';

    protected $fillable = ['code', 'name', 'description', 'price', 'charge_type', 'is_active'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function chargeLabel(): string
    {
        return $this->charge_type === self::PER_DAY ? 'per hari' : 'sekali bayar';
    }
}
