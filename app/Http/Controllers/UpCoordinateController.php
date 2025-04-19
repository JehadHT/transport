<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\UpCoordinate;

class UpCoordinateController extends Controller
{
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'latitude' => 'required|numeric',
    //         'longitude' => 'required|numeric',
    //     ]);
    
    //     $coordinate = UpCoordinate::create($validated);
    
    //     return response()->json(['success' => true, 'id' => $coordinate->id]);
    // }
    public function store(Request $request)
{
    $coordinate = UpCoordinate::create([
        'latitude' => $request->lat,
        'longitude' => $request->lng,
    ]);

    return response()->json($coordinate, 201);
}


//     public function update(Request $request, $id)
// {
//     $validated = $request->validate([
//         'latitude' => 'required|numeric',
//         'longitude' => 'required|numeric',
//     ]);

//     $coordinate = UpCoordinate::findOrFail($id);
//     $coordinate->update($validated);

//     return response()->json(['success' => true]);
// }

public function update(Request $request, $id)
{
    $coordinate = UpCoordinate::findOrFail($id);
    $coordinate->update([
        'latitude' => $request->lat,
        'longitude' => $request->lng,
    ]);

    return response()->json($coordinate, 200);
}


//     public function destroy($id)
//     {
//         $coordinate = UpCoordinate::find($id);
//         if ($coordinate) {
//             $coordinate->delete();
//             return response()->json(['success' => true]);
//         }

//         return response()->json(['success' => false], 404);
//     }
public function destroy($id)
{
    Log::info("Delete request received for pin ID: " . $id);
    UpCoordinate::findOrFail($id)->delete();
    // UpCoordinate::destroy($id);

    return response()->json(['message' => 'Deleted successfully'], 200);
}


}