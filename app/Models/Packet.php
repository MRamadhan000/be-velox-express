<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Packet extends Model
{
    protected $fillable = [
        'uuid', 
        'receipt_number', 
        'sender_name', 
        'sender_phone',
        'pickup_address',
        'receiver_name',
        'receiver_phone',
        'destination_address',
        'weight', 
        'packet_image',
        'user_id'
    ];

    // Boot method untuk generate UUID dan Resi otomatis
    protected static function booted()
    {
        static::creating(function ($packet) {
            $packet->uuid = (string) Str::uuid();
            $packet->receipt_number = 'VLX-' . strtoupper(Str::random(10));
        });
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}