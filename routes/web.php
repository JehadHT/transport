<?php

use App\Http\Controllers\GetGeojsonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('map',function(){
    return view('maps.map');
})->name('map');

Route::get('maps',[GetGeojsonController::class,'show']);

Route::get('/route_result', function () {
    return view('route_result');
});
//هذا الكود لصفحة المساعد الذكي

Route::get('/chat',function(){
    return view('chat');
});

//هذا الكود للوصل بين Laravel وبين Fastapi in python
Route::post('/api/chat-ai', function (Request $request) {
    $message = $request->input('message');

    try {
        $response = Http::post('http://127.0.0.1:9000/parse', [
            'message' => $message
        ]);

        $data = $response->json();
        Log::info('رد FastAPI:', $data);

        $origin = $data['origin'] ?? null;
        $originCoords = $data['origin_coords'] ?? [null, null];
        $destination = $data['destination'] ?? null;
        $destinationCoords = $data['destination_coords'] ?? [null, null];

        if ($origin && $destination && $originCoords[0] && $destinationCoords[0]) {
            $reply = "🚍 نقطة الانطلاق: {$origin} (" . round($originCoords[0], 5) . ", " . round($originCoords[1], 5) . ")\n";
            $reply .= "📍 نقطة الوصول: {$destination} (" . round($destinationCoords[0], 5) . ", " . round($destinationCoords[1], 5) . ")";
        } else {
            $details = [];

            if (!$originCoords[0]) {
                $details[] = "⚠️ لم أستطع تحديد موقع: {$origin}";
            }

            if (!$destinationCoords[0]) {
                $details[] = "⚠️ لم أستطع تحديد موقع: {$destination}";
            }

            $reply = implode("\n", $details) . "\nيرجى تحديد المكان بدقة أو بصيغة أوضح.";
        }



    } catch (\Exception $e) {
        Log::error("❌ خطأ في الاتصال بـ FastAPI: " . $e->getMessage());
        $reply = "⚠️ حدث خطأ أثناء الاتصال بالمساعد الذكي.";
    }

    return response()->json([
        'reply' => $reply,
        'origin' => $origin,
        'origin_coords' => $originCoords,
        'destination' => $destination,
        'destination_coords' => $destinationCoords
    ]);
    
});

Route::view('/driver', 'driver');
Route::view('/maps', 'mapLoc');

