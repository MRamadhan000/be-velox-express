<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PacketController;
use App\Http\Controllers\ShipmentController;

/*
|--------------------------------------------------------------------------
| API Routes - VeloxExpress
|--------------------------------------------------------------------------
*/

// Auth routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
    Route::middleware('jwt.auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/admin/logout', [AuthController::class, 'adminLogout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Protected routes
Route::middleware('jwt.auth')->group(function () {
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('packets/all', [PacketController::class, 'getAll']);
        // Add more admin routes here
    });

    // User routes (admin and user can access)
    Route::post('packets', [PacketController::class, 'store']);
    Route::get('packets', [PacketController::class, 'index']);
    Route::get('packets/{packet}', [PacketController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::put('packets/{packet}', [PacketController::class, 'update']);
        Route::delete('packets/{packet}', [PacketController::class, 'destroy']);
    });

    // Endpoint untuk Master Data Driver (Admin only for mutations)
    Route::get('drivers', [DriverController::class, 'index']);
    Route::get('drivers/{driver}', [DriverController::class, 'show']);
    Route::middleware('role:admin')->group(function () {
        Route::post('drivers', [DriverController::class, 'store']);
        Route::put('drivers/{driver}', [DriverController::class, 'update']);
        Route::delete('drivers/{driver}', [DriverController::class, 'destroy']);
    });

    // Endpoint untuk Transaksi Shipment (Admin only for mutations)
    Route::get('shipments/user', [ShipmentController::class, 'getUserShipments']);
    Route::get('shipments', [ShipmentController::class, 'index']);
    Route::get('shipments/{shipment}', [ShipmentController::class, 'show']);
    Route::get('/shipments/v/{uuid}', [ShipmentController::class, 'showByUuid']);
    Route::middleware('role:admin')->group(function () {
        Route::post('shipments', [ShipmentController::class, 'store']);
        Route::put('shipments/{shipment}', [ShipmentController::class, 'update']);
        Route::delete('shipments/{shipment}', [ShipmentController::class, 'destroy']);
    });
});

/* Catatan: apiResource akan otomatis membuat route:
  - GET    /api/drivers (index)
  - POST   /api/drivers (store)
  - GET    /api/drivers/{id} (show)
  - PUT    /api/drivers/{id} (update)
  - DELETE /api/drivers/{id} (destroy)
  Berlaku juga untuk packets dan shipments.
*/