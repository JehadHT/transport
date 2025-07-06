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
        $response = Http::post('http://127.0.0.1:9100/parse', [
            'message' => $message
        ]);

        $data = $response->json();
        Log::info('رد FastAPI:', $data);

        $reply = $data['reply'] ?? 'لا يوجد رد.';
        $results = $data['results'] ?? [];

    } catch (\Exception $e) {
        Log::error("❌ خطأ في الاتصال بـ FastAPI: " . $e->getMessage());
        return response()->json([
            'reply' => "⚠️ حدث خطأ أثناء الاتصال بالمساعد الذكي.",
            'results' => []
        ]);
    }

    return response()->json([
        'reply' => $reply,
        'results' => $results
    ]);
});


Route::view('/driver', 'driver');
Route::view('/maps', 'mapLoc');

