<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\BusRouteController;
use App\Http\Controllers\UpCoordinateController;
use App\Models\GetGeojson;
use App\Models\Station;

Route::get('/get-data', [DataController::class, 'getData']);

Route::get('/stations', function () {
    return Station::all();
});

Route::get('/routes', function () {
    $features = GetGeojson::all()->map(function ($route) {
        return json_decode($route->geometry, true);
    });

    return response()->json([
        "type" => "FeatureCollection",
        "features" => $features,
    ]);
});

Route::post('/send-to-controll', [BusRouteController::class, 'getShortestPathFromPins']);

Route::post('/save-pin', [UpCoordinateController::class, 'store']);
Route::put('/update-pin/{id}', [UpCoordinateController::class, 'update']);
Route::delete('/delete-pin/{id}', [UpCoordinateController::class, 'destroy'])->name('UpCoordinate.destroy');

Route::post('/find-shortest-path', [BusRouteController::class, 'findShortestPath']);
Route::post('/get-shortest-path-from-pins', [BusRouteController::class, 'getShortestPathFromPins']);