<?php

use App\Http\Controllers\GetGeojsonController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('map',function(){
    return view('maps.map');
})->name('map');

Route::get('maps',[GetGeojsonController::class,'show']);

Route::get('/route_result', function () {
    return view('route_result');
});