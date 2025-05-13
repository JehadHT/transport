<?php

namespace App\Http\Controllers;

use App\Models\GetGeojson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetGeojsonController extends Controller
{

public function getShortestPath(Request $request)
{
    $validated = $request->validate([
        'startS' => 'required|array',
        'startS.latitude' => 'required|numeric|between:-90,90',
        'startS.longitude' => 'required|numeric|between:-180,180',
        'endS' => 'required|array',
        'endS.latitude' => 'required|numeric|between:-90,90',
        'endS.longitude' => 'required|numeric|between:-180,180',
    ], [
        'startS.required' => 'يرجى تحديد نقطة البداية.',
        'endS.required' => 'يرجى تحديد نقطة النهاية.',
    ]);
    
    dd($validated['startS']);
    
    $start = $validated['startS'];
    $end = $validated['endS'];
    
    $startNode = DB::table('vertices_noded')
    ->select('id')
    ->orderByRaw("the_geom <-> ST_Transform(ST_SetSRID(ST_MakePoint(?, ?), 4326), 3857)")
    ->limit(1)
    ->value('id', [$start['longitude'], $start['latitude']]);

$endNode = DB::table('vertices_noded')
    ->select('id')
    ->orderByRaw("the_geom <-> ST_Transform(ST_SetSRID(ST_MakePoint(?, ?), 4326), 3857)")
    ->limit(1)
    ->value('id', [$end['longitude'], $end['latitude']]);

    $result = DB::select("
        SELECT seq, node, edge, cost, agg_cost, ST_AsGeoJSON(e.the_geom) as geojson
        FROM pgr_dijkstra(
            'SELECT id, source, target, cost FROM edges_noded',
            ?, ?, true
        ) AS d
        JOIN edges_noded e ON d.edge = e.id
    ", [$startNode, $endNode]);

    // return response()->json($result);
    return response()->json([
        'path' => $result
    ]);
}


    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
        public function show()
        {
            $getGeojson = GetGeojson::all();
            return view('maps.map', compact('getGeojson')); 
        }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GetGeojson $getGeojson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GetGeojson $getGeojson)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GetGeojson $getGeojson)
    {
        //
    }
}