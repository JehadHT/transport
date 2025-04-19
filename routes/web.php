<?php

use App\Http\Controllers\BusRouteController;
use App\Http\Controllers\GetGeojsonController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\UpCoordinateController;
use App\Models\GetGeojson;
use App\Models\Station;
use App\Models\UpCoordinate;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use function PHPSTORM_META\map;

Route::get('/', function () {
    return view('welcome');
});

Route::get('map',function(){
    return view('maps.map');
})->name('map');

Route::get('maps',[GetGeojsonController::class,'show']);

Route::get('/api/stations', function () {
    return Station::all();
});


Route::get('/api/routes', function () {
    $features = GetGeojson::all()->map(function ($route) {
        // تحويل البيانات المخزنة إلى صيغة JSON
        return json_decode($route->geometry, true);
    });

    return response()->json([
        "type" => "FeatureCollection",
        "features" => $features,
    ]);
});

Route::post('/api/send-to-controll', [BusRouteController::class, 'ClosestStation']);

Route::middleware('api')->group(function () {
    Route::POST('/save-pin', [UpCoordinateController::class, 'store']);
    Route::PUT('/update-pin/{id}', [UpCoordinateController::class, 'update']);
    Route::DELETE('/delete-pin/{id}', [UpCoordinateController::class, 'destroy'])->name('UpCoordinate.destroy');
    
});

Route::post('/find-shortest-path', [BusRouteController::class, 'findShortestPath']);

// Route::get('/shortest-path-from-pins', [BusRouteController::class, 'getShortestPathFromPins']);

Route::GET('/get-shortest-path-from-pins', [BusRouteController::class, 'getShortestPathFromPins']);

// Route::post('/store-coordinates', function (Request $request) {
//     $latitude = $request->input('latitude');
//     $longitude = $request->input('longitude');
    
//     UpCoordinate::create([
//         'latitude' => $latitude, 
//         'longitude' => $longitude
//     ]);
//     return response()->json([
//         'status' => 'success',
//         'message' => 'Coordinates saved successfully',
//         'data' => [
//             'latitude' => $latitude,
//             'longitude' => $longitude
//         ]
//     ]);
// });