<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class DriverController extends Controller
{
    public function index() {
        try {
            $drivers = Driver::all();
            return response()->json($drivers);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve drivers', 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'driver_name' => 'required|string|max:255',
                'driver_license_number' => 'required|string|unique:drivers|max:50',
                'current_capacity' => 'required|integer|min:0'
            ]);

            $driver = Driver::create($validated);
            return response()->json($driver, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create driver', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $driver = Driver::findOrFail($id);
            return response()->json($driver);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Driver not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to retrieve driver', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            $driver = Driver::findOrFail($id);

            $validated = $request->validate([
                'driver_name' => 'sometimes|required|string|max:255',
                'driver_license_number' => 'sometimes|required|string|max:50|unique:drivers,driver_license_number,' . $id,
                'current_capacity' => 'sometimes|required|integer|min:0'
            ]);

            $driver->update($validated);
            return response()->json($driver);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Driver not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update driver', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) {
        try {
            $driver = Driver::findOrFail($id);
            $driver->delete();
            return response()->json(['message' => 'Driver deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Driver not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete driver', 'message' => $e->getMessage()], 500);
        }
    }
}