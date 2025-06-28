<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\BusRouteController;
use App\Http\Controllers\GetGeojsonController;
use App\Http\Controllers\GeojsonFeaturesController;
use App\Http\Controllers\EdgesController;
use App\Http\Controllers\MyController;
use App\Http\Controllers\ChatAIController;
use App\Http\Controllers\UpCoordinateController;
use App\Models\GetGeojson;
use App\Models\Geojson_features;
use App\Models\Station;

Route::get('/get-data', [DataController::class, 'getData']);

Route::get('/stations', function () {
    return Station::all();
});

Route::get('/routes', function () {
    $features = Geojson_features::all()->map(function ($route) {
        return [
            'type' => 'Feature',
            'geometry' => json_decode(DB::table('geojson_features')
                ->where('id', $route->id)
                ->selectRaw('ST_AsGeoJSON(geometry) as geometry')
                ->value('geometry')), // فك تشفير GeoJSON
            'properties' => json_decode($route->properties, true)
        ];
    });

    return response()->json([
        'type' => 'FeatureCollection',
        'features' => $features,
    ]);
});


Route::post('/send-to-controll', [EdgesController::class, 'getShortestPath']);
Route::post('/find-closest-point-on-route', [BusRouteController::class, 'findClosestPointOnRoute']);
Route::post('/save-pin', [UpCoordinateController::class, 'store']);
Route::put('/update-pin/{id}', [UpCoordinateController::class, 'update']);
Route::delete('/delete-pin/{id}', [UpCoordinateController::class, 'destroy'])->name('UpCoordinate.destroy');

Route::post('/find-shortest-path', [BusRouteController::class, 'findShortestPath']);
Route::post('/get-shortest-path-from-pins', [BusRouteController::class, 'getShortestPathFromPins']);
Route::get('/route/{start}/{end}', [GeojsonFeaturesController::class, 'getRoute']);
Route::post('/route', [BusRouteController::class,'find']);

Route::get('/create-topology', [MyController::class, 'createTopology']);
//api for chat
// Route::post('/chat-ai', [ChatAIController::class, 'handle']);
