<?php

namespace App\Http\Controllers;

use App\Models\Packet;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class PacketController extends Controller
{
    public function index() {
        try {
            $packets = Packet::with('user')->where('user_id', auth()->id())->get();
            return response()->json($packets);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve packets', 'message' => $e->getMessage()], 500);
        }
    }

    public function getAll() {
        try {
            $packets = Packet::with('user')->get();
            return response()->json($packets);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve all packets', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $packet = Packet::with('user')->where('user_id', auth()->id())->findOrFail($id);
            return response()->json($packet);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Packet not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve packet', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        try {
            $request->validate([
                'sender_name' => 'required|string|max:255',
                'sender_phone' => 'required|string|max:20',
                'pickup_address' => 'required|string',
                'receiver_name' => 'required|string|max:255',
                'receiver_phone' => 'required|string|max:20',
                'destination_address' => 'required|string',
                'weight' => 'required|integer|min:1',
                'packet_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            $path = null;
            if ($request->hasFile('packet_image')) {
                try {
                    $directory = public_path('images/packets');
                    if (!file_exists($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    $file = $request->file('packet_image');
                    $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                    $file->move($directory, $filename);
                    $path = '/images/packets/' . $filename;
                } catch (Exception $e) {
                    return response()->json(['error' => 'Failed to upload image', 'message' => $e->getMessage()], 500);
                }
            }

            $userId = auth()->id();

            $packet = Packet::create([
                'sender_name' => $request->sender_name,
                'sender_phone' => $request->sender_phone,
                'pickup_address' => $request->pickup_address,
                'receiver_name' => $request->receiver_name,
                'receiver_phone' => $request->receiver_phone,
                'destination_address' => $request->destination_address,
                'weight' => $request->weight,
                'packet_image' => $path,
                'user_id' => $userId
            ]);

            // Auto-create shipment with pending status
            Shipment::create([
                'packet_id' => $packet->id,
                'driver_id' => null,
                'status' => 'Pending'
            ]);

            return response()->json($packet, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create packet', 'message' => $e->getMessage()], 500);
        }
    }

public function update(Request $request, $id) {
    try {
        // Admin can update any packet, user can only update their own
        $query = Packet::query();
        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }
        $packet = $query->findOrFail($id);

        $request->validate([
            'sender_name' => 'sometimes|required|string|max:255',
            'sender_phone' => 'sometimes|required|string|max:20',
            'pickup_address' => 'sometimes|required|string',
            'receiver_name' => 'sometimes|required|string|max:255',
            'receiver_phone' => 'sometimes|required|string|max:20',
            'destination_address' => 'sometimes|required|string',
            'weight' => 'sometimes|required|integer|min:1',
            'packet_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $updateData = [];

        // Only update fields that are provided and not empty
        if ($request->has('sender_name') && !empty(trim($request->sender_name))) {
            $updateData['sender_name'] = trim($request->sender_name);
        }
        if ($request->has('sender_phone') && !empty(trim($request->sender_phone))) {
            $updateData['sender_phone'] = trim($request->sender_phone);
        }
        if ($request->has('pickup_address') && !empty(trim($request->pickup_address))) {
            $updateData['pickup_address'] = trim($request->pickup_address);
        }
        if ($request->has('receiver_name') && !empty(trim($request->receiver_name))) {
            $updateData['receiver_name'] = trim($request->receiver_name);
        }
        if ($request->has('receiver_phone') && !empty(trim($request->receiver_phone))) {
            $updateData['receiver_phone'] = trim($request->receiver_phone);
        }
        if ($request->has('destination_address') && !empty(trim($request->destination_address))) {
            $updateData['destination_address'] = trim($request->destination_address);
        }
        if ($request->has('weight') && $request->weight > 0) {
            $updateData['weight'] = $request->weight;
        }

        // Handle image upload separately
        if ($request->hasFile('packet_image')) {
            try {
                $directory = public_path('images/packets');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                $file = $request->file('packet_image');
                $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                $file->move($directory, $filename);
                $updateData['packet_image'] = '/images/packets/' . $filename;
            } catch (Exception $e) {
                return response()->json(['error' => 'Failed to upload image', 'message' => $e->getMessage()], 500);
            }
        }

        // Only update if there's data to update
        if (!empty($updateData)) {
            $packet->update($updateData);
            return response()->json($packet->fresh());
        } else {
            return response()->json($packet); // No changes made
        }
    } catch (ModelNotFoundException $e) {
        return response()->json(['error' => 'Packet not found'], 404);
    } catch (Exception $e) {
        return response()->json(['error' => 'Failed to update packet', 'message' => $e->getMessage()], 500);
    }
}

    public function destroy($id) {
        try {
            $packet = Packet::where('user_id', auth()->id())->findOrFail($id);
            if ($packet->packet_image) {
                $imagePath = public_path($packet->packet_image);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $packet->delete();
            return response()->json(['message' => 'Packet deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Packet not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete packet', 'message' => $e->getMessage()], 500);
        }
    }
}