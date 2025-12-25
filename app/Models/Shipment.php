<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'packet_id', 
        'driver_id', 
        'status', 
        'shipped_at'
    ];

    // Relasi: Transaksi ini milik seorang Driver
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    // Relasi: Transaksi ini milik sebuah Paket
    public function packet(): BelongsTo
    {
        return $this->belongsTo(Packet::class);
    }
}
