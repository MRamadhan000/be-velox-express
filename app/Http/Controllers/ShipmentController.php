<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Driver;
use App\Models\Packet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    public function index() {
        try {
            $shipments = Shipment::with(['driver', 'packet'])->latest()->get();
            return response()->json($shipments);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve shipments', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $shipment = Shipment::with(['driver', 'packet'])->findOrFail($id);
            return response()->json($shipment);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Shipment not found', 'message' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request) {
        try {
            $request->validate([
                'packet_id' => 'required|exists:packets,id',
                'driver_id' => 'required|exists:drivers,id',
            ]);

            // Gunakan Transaksi agar data aman jika terjadi error di tengah jalan
            return DB::transaction(function () use ($request) {
                $packet = Packet::findOrFail($request->packet_id);
                $driver = Driver::lockForUpdate()->findOrFail($request->driver_id);

                // LOGIKA STOK: Cek apakah kapasitas kurir cukup
                if ($driver->current_capacity < $packet->weight) {
                    return response()->json(['message' => 'Kapasitas Driver tidak cukup!'], 400);
                }

                // 1. Simpan Transaksi
                $shipment = Shipment::create([
                    'packet_id' => $packet->id,
                    'driver_id' => $driver->id,
                    'status' => 'Pending',
                    'shipped_at' => now(),
                ]);

                // 2. OTOMATIS: Kurangi stok kapasitas kurir
                $driver->decrement('current_capacity', $packet->weight);

                return response()->json($shipment, 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create shipment', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $shipment = Shipment::findOrFail($id);

            $request->validate([
                'status' => 'sometimes|required|in:Pending,Pickup,Delivered',
                'driver_id' => 'sometimes|nullable|exists:drivers,id',
            ]);

            $shipment->update($request->only(['status', 'driver_id']));

            return response()->json($shipment);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update shipment', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        try {
            return DB::transaction(function () use ($id) {
                $shipment = Shipment::with('packet')->findOrFail($id);
                $driver = Driver::findOrFail($shipment->driver_id);

                // LOGIKA RESTOCK: Kembalikan kapasitas saat transaksi dihapus
                $driver->increment('current_capacity', $shipment->packet->weight);

                $shipment->delete();
                return response()->json(['message' => 'Shipment dibatalkan, kapasitas kurir kembali.']);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete shipment', 'message' => $e->getMessage()], 500);
        }
    }

    // Legacy method for UUID (if needed)
    public function showByUuid($uuid) {
        try {
            $shipment = Shipment::with(['driver', 'packet'])->where('uuid', $uuid)->firstOrFail();
            return response()->json($shipment);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Shipment not found', 'message' => $e->getMessage()], 404);
        }
    }

    public function getUserShipments() {
        try {
            // 1. Get user ID from JWT token
            $userId = auth()->user()->id;
            
            // 2. Get packets belonging to the user
            $packets = Packet::where('user_id', $userId)->get();
            $packetIds = $packets->pluck('id');
            
            // 3. Get shipments for those packets
            $shipments = Shipment::whereIn('packet_id', $packetIds)
                ->with(['driver', 'packet'])
                ->latest()
                ->get();
            
            return response()->json($shipments);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve user shipments', 'message' => $e->getMessage()], 500);
        }
    }
}
