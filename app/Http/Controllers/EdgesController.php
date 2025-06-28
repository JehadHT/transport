<?php

namespace App\Http\Controllers;

use App\Models\Edges;
use Illuminate\Http\Request;
use App\Services\Pathfinding\AStarService;
use Illuminate\Support\Facades\DB;

class EdgesController extends Controller
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
    ]);

    $start = $validated['startS'];
    $end = $validated['endS'];

    // العثور على أقرب عقدة للبداية
    $startNodeRow = DB::table('edges_noded')
        ->select('source')
        ->orderByRaw("the_geom <-> ST_SetSRID(ST_MakePoint(?, ?), 4326)", [
            $start['longitude'], $start['latitude']
        ])
        ->first();

    // العثور على أقرب عقدة للنهاية
    $endNodeRow = DB::table('edges_noded')
        ->select('target')
        ->orderByRaw("the_geom <-> ST_SetSRID(ST_MakePoint(?, ?), 4326)", [
            $end['longitude'], $end['latitude']
        ])
        ->first();

    if (!$startNodeRow || !$endNodeRow) {
        return response()->json(['error' => 'تعذر تحديد أقرب عقدة'], 422);
    }

    $startNode = $startNodeRow->source;
    $endNode = $endNodeRow->target;

    // تمرير العقوبة يدويًا - مثلاً 500 ليرة عند تغيير المسار
    $aStar = new AStarService(1);

    $path = $aStar->findPath($startNode, $endNode);

    if (empty($path)) {
        return response()->json(['error' => 'لا يوجد مسار متاح بين النقطتين'], 404);
    }

    $routeIds = collect($path)->pluck('route_id')->unique()->values();

    $routesInfo = DB::table('bus_routes_costs')
        ->whereIn('cost_id', $routeIds)
        ->get(['cost_id', 'costs', 'line_name']);

    $totalCost = $routesInfo->sum('costs');
    $lineNames = $routesInfo->pluck('line_name');

    return response()->json([
        'path' => $path,
        'used_routes' => $routeIds,
        'line_name' => $lineNames,
        'total_cost' => $totalCost . ' ليرة سورية',
    ]);
}


    // public function getShortestPath(Request $request)
    // {
    //     $validated = $request->validate([
    //         'startS' => 'required|array',
    //         'startS.latitude' => 'required|numeric|between:-90,90',
    //         'startS.longitude' => 'required|numeric|between:-180,180',
    //         'endS' => 'required|array',
    //         'endS.latitude' => 'required|numeric|between:-90,90',
    //         'endS.longitude' => 'required|numeric|between:-180,180',
    //     ]);

    //     $start = $validated['startS'];
    //     $end = $validated['endS'];

    //     // أقرب عقدة للبداية
    //     $startNodeRow = DB::table('edges_noded')
    //         ->select('source')
    //         ->orderByRaw("the_geom <-> ST_SetSRID(ST_MakePoint(?, ?), 4326)", [
    //             $start['longitude'], $start['latitude']
    //         ])
    //         ->first();

    //     // أقرب عقدة للنهاية
    //     $endNodeRow = DB::table('edges_noded')
    //         ->select('target')
    //         ->orderByRaw("the_geom <-> ST_SetSRID(ST_MakePoint(?, ?), 4326)", [
    //             $end['longitude'], $end['latitude']
    //         ])
    //         ->first();

    //     if (!$startNodeRow || !$endNodeRow) {
    //         return response()->json(['error' => 'تعذر تحديد أقرب عقدة'], 422);
    //     }

    //     $startNode = $startNodeRow->source;
    //     $endNode = $endNodeRow->target;

    //     $result = DB::select("
    //         SELECT seq, node, edge , e.route_id , ST_AsGeoJSON(e.the_geom) as geojson
    //         FROM pgr_dijkstra(
    //             'SELECT id, source, target, cost FROM edges_noded',
    //             ?::BIGINT, ?::BIGINT, true
    //         ) AS d
    //         JOIN edges_noded e ON d.edge = e.id
    //     ", [$startNode, $endNode]);

    //     // استخراج route_ids الفريدة
    //     $routeIds = collect($result)
    //         ->pluck('route_id')
    //         ->unique()
    //         ->values();

    //     // حساب التكلفة الإجمالية
    //     $totalCost = DB::table('bus_routes_costs')
    //         ->whereIn('cost_id', $routeIds)
    //         ->sum('costs');

    //     $lineMap = DB::table('bus_routes_costs')
    //         ->whereIn('cost_id', $routeIds)
    //         ->pluck('line_name');


    //     return response()->json([
    //         'path' => $result,
    //         'used_routes' => $routeIds,
    //         'line_name' => $lineMap,
    //         'total_cost' => $totalCost . ' ليرة سورية',
    //     ]);
    // }
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
    public function show(Edges $edges)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Edges $edges)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Edges $edges)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Edges $edges)
    {
        //
    }
}
