<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'driver_name', 
        'driver_license_number', 
        'current_capacity'
    ];

    // Relasi: Satu driver bisa memiliki banyak transaksi pengiriman
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}