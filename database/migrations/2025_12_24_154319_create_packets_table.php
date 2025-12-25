<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('packets', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->string('receipt_number')->unique();
        
        // Kolom Baru
        $table->string('sender_name');      // Nama Pengirim
        $table->string('sender_phone');     // No Telp Pengirim
        $table->text('pickup_address');     // Alamat Penjemputan
        
        $table->string('receiver_name');    // Nama Penerima
        $table->string('receiver_phone');   // No Telp Penerima
        $table->text('destination_address'); // Alamat Tujuan
        
        $table->integer('weight');
        $table->string('packet_image')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packets');
    }
};
