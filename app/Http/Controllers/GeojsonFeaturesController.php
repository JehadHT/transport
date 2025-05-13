<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeojsonFeaturesController extends Controller
{
    public function getRoute($start, $end)
    {
        // 1. Define the Dijkstra query with explicit type casts
        $sql = <<<SQL
WITH route AS (
    SELECT d.seq, d.edge AS id_edge
    FROM pgr_dijkstra(
        'SELECT id, source, target, distance AS cost FROM bus_routes_noded'::text,
        CAST(? AS integer),
        CAST(? AS integer),
        CAST(false AS boolean)
    ) AS d
    WHERE d.edge >= 0
)
SELECT r.seq, b.geometry AS geom
FROM route r
JOIN bus_routes_noded b ON r.id_edge = b.id
ORDER BY r.seq;
SQL;

        // 2. Execute the query, passing integers to avoid ambiguity
        $rows = DB::select($sql, [(int)$start, (int)$end]);

        // 3. Build GeoJSON FeatureCollection
        $features = array_map(function($row) {
            return [
                'type' => 'Feature',
                'properties' => ['seq' => $row->seq],
                'geometry' => json_decode($row->geometry),
            ];
        }, $rows);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }


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

        // أقرب عقدة للبداية
        $startNodeRow = DB::table('edges_noded')
            ->select('source')
            ->orderByRaw("the_geom <-> ST_Transform(ST_SetSRID(ST_MakePoint(?, ?), 4326), 3857)", [
                $start['longitude'], $start['latitude']
            ])
            ->first();

        // أقرب عقدة للنهاية
        $endNodeRow = DB::table('edges_noded')
            ->select('target')
            ->orderByRaw("the_geom <-> ST_Transform(ST_SetSRID(ST_MakePoint(?, ?), 4326), 3857)", [
                $end['longitude'], $end['latitude']
            ])
            ->first();

        if (!$startNodeRow || !$endNodeRow) {
            return response()->json(['error' => 'تعذر تحديد أقرب عقدة'], 422);
        }

        $startNode = $startNodeRow->source;
        $endNode = $endNodeRow->target;

        $result = DB::select("
            SELECT seq, node, edge, cost, agg_cost, ST_AsGeoJSON(e.the_geom) as geojson
            FROM pgr_dijkstra(
                'SELECT id, source, target, cost FROM edges_noded',
                ?, ?, true
            ) AS d
            JOIN edges_noded e ON d.edge = e.id
        ", [$startNode, $endNode]);

        return response()->json(['path' => $result]);
    }
}