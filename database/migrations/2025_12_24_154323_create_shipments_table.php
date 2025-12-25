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
    Schema::create('shipments', function (Blueprint $table) {
        $table->id(); // Primary Key
        $table->uuid('uuid')->unique(); // Identitas Unik Tambahan
        
        // Relasi Foreign Key
        $table->foreignId('packet_id')->constrained('packets')->onDelete('cascade');
        $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
        
        $table->enum('status', ['Pending', 'Pickup', 'Delivered'])->default('Pending');
        $table->timestamp('shipped_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
