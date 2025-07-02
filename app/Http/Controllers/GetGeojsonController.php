<?php

namespace App\Http\Controllers;

use App\Models\GetGeojson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetGeojsonController extends Controller
{

public function getShortestPath()
{
    // جلب آخر نقطتين من جدول النقاط
    $lastTwo = DB::table('up_coordinates')
        ->orderByDesc('id')
        ->limit(2)
        ->get();

    if ($lastTwo->count() < 2) {
        return response()->json(['error' => 'يجب وضع نقطتي بداية ونهاية'], 400);
    }
    $startPoint = "SRID=4326;POINT({$lastTwo[1]->closest_longitude} {$lastTwo[1]->closest_latitude})";
    $endPoint   = "SRID=4326;POINT({$lastTwo[0]->closest_longitude} {$lastTwo[0]->closest_latitude})";
    
    // جلب أقرب عقدة (node) إلى كل نقطة
    $startNode = DB::table('bus_network_edges_vertices_pgr')
    ->orderByRaw("the_geom <-> ST_GeomFromText(?, 4326)", [$startPoint])
    ->value('id');
    
    $endNode = DB::table('bus_network_edges_vertices_pgr')
    ->orderByRaw("the_geom <-> ST_GeomFromText(?, 4326)", [$endPoint])
    ->value('id');
    
    dd($startNode,$endNode);
    if ($startNode === null || $endNode === null) {
        return response()->json(['error' => 'تعذر العثور على أقرب عقدة'], 404);
    }

    // تنفيذ خوارزمية Dijkstra لجلب المسار
    $results = DB::select("
        SELECT ST_AsGeoJSON(ST_LineMerge(ST_Union(geom))) as geojson
        FROM (
            SELECT e.geom
            FROM pgr_dijkstra(
                'SELECT id, source, target, cost FROM bus_network_edges',
                ?::BIGINT, ?::BIGINT, directed := true
            ) AS d
            JOIN bus_network_edges AS e ON d.edge = e.id
        ) AS route
    ", [$startNode, $endNode]);

    if (empty($results) || !$results[0]->geojson) {
        return response()->json(['error' => 'لم يتم العثور على مسار بين النقطتين'], 404);
    }

    return response()->json(['path' => json_decode($results[0]->geojson)]);
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