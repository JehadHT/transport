<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusRoute;
use App\Models\Pin;

class BusRouteController extends Controller
{
    // تعديل تجريبي
    public function ClosestStation(){
        
    }
    public function getShortestPathFromPins()
    {
        try {
            $lastTwoPins = Pin::latest()->take(2)->get();

            if ($lastTwoPins->count() < 2) {
                return response()->json(['error' => 'لم يتم إدخال نقطتين بعد.'], 200);
            }

            $sortedPins = $lastTwoPins->sortBy('created_at')->values();
            $start = $sortedPins[0];
            $end = $sortedPins[1];

            $routes = BusRoute::all();
            $graph = $this->buildGraph($routes);

            // 1️⃣ إيجاد أقرب عقدة لمسار الباص من نقطة البداية
            $closestStartNode = $this->findClosestNode($graph, $start->latitude, $start->longitude);

            // 2️⃣ إيجاد أقرب عقدة لمسار الباص من نقطة النهاية
            $closestEndNode = $this->findClosestNode($graph, $end->latitude, $end->longitude);

            // 3️⃣ المسار بواسطة الباص بين المحطتين
            $busPath = $this->aStar($graph, $closestStartNode, $closestEndNode);

            // 4️⃣ تحويل string إلى إحداثيات فعلية
            $startNodeCoords = $this->parseNodeKey($closestStartNode);
            $endNodeCoords = $this->parseNodeKey($closestEndNode);

            // 5️⃣ دمج المسارات: من نقطة البداية إلى أقرب محطة، ثم الباص، ثم إلى الوجهة
            $fullPath = [
                [$start->latitude, $start->longitude],
                [$startNodeCoords['lat'], $startNodeCoords['lng']],
            ];

            foreach ($busPath['path'] as $nodeKey) {
                $coord = $this->parseNodeKey($nodeKey);
                $fullPath[] = [$coord['lat'], $coord['lng']];
            }

            $fullPath[] = [$endNodeCoords['lat'], $endNodeCoords['lng']];
            $fullPath[] = [$end->latitude, $end->longitude];

            // 6️⃣ حساب المسافة الكلية التقريبية
            $totalDistance = 0;
            for ($i = 0; $i < count($fullPath) - 1; $i++) {
                $totalDistance += $this->calculateDistance(
                    ['lat' => $fullPath[$i][0], 'lng' => $fullPath[$i][1]],
                    ['lat' => $fullPath[$i + 1][0], 'lng' => $fullPath[$i + 1][1]]
                );
            }

            return response()->json([
                'path' => array_map(function ($pair) {
                    return implode(',', array_reverse($pair)); // نعكس [lat, lng] إلى [lng, lat]
                }, $fullPath),
                'distance' => $totalDistance,
            ]);
        } catch (\Exception $e) {
            // /Log::error('خطأ في المسار الكامل:', ['exception' => $e]);
            return response()->json(['error' => 'حدث خطأ أثناء الحساب.'], 500);
        }
    }


    private function buildGraph($routes)
    {
        $graph = [];
        foreach ($routes as $route) {
            $geometry = is_string($route->geometry) ? json_decode($route->geometry, true) : $route->geometry;

            if (!isset($geometry['coordinates'])) {
                continue; // تخطى هذا المسار إذا لم يكن يحتوي على إحداثيات
            }

            $coordinates = $geometry['coordinates'];

            for ($i = 0; $i < count($coordinates) - 1; $i++) {
                $from = $coordinates[$i];
                $to = $coordinates[$i + 1];

                $fromKey = implode(',', $from);
                $toKey = implode(',', $to);
                $distance = $this->calculateDistance(
                    ['lat' => $from[1], 'lng' => $from[0]],
                    ['lat' => $to[1], 'lng' => $to[0]]
                );

                $graph[$fromKey][$toKey] = $distance;
                $graph[$toKey][$fromKey] = $distance; // ثنائي الاتجاه
            }
        }
        return $graph;
    }

    private function findClosestNode($graph, $lat, $lng)
    {
        $closest = null;
        $minDistance = PHP_INT_MAX;

        foreach ($graph as $nodeKey => $neighbors) {
            [$nodeLng, $nodeLat] = explode(',', $nodeKey);
            $dist = $this->calculateDistance(
                ['lat' => $lat, 'lng' => $lng],
                ['lat' => $nodeLat, 'lng' => $nodeLng]
            );
            if ($dist < $minDistance) {
                $minDistance = $dist;
                $closest = $nodeKey;
            }
        }

        return $closest;
    }

    private function aStar($graph, $start, $goal)
    {
        $openSet = [$start];
        $cameFrom = [];

        $gScore = [$start => 0];
        $fScore = [$start => $this->heuristic($start, $goal)];

        while (!empty($openSet)) {
            usort($openSet, function ($a, $b) use ($fScore) {
                return ($fScore[$a] ?? PHP_INT_MAX) <=> ($fScore[$b] ?? PHP_INT_MAX);
            });

            $current = array_shift($openSet);
            if ($current === $goal) {
                return [
                    'path' => $this->reconstructPath($cameFrom, $current),
                    'distance' => $gScore[$goal]
                ];
            }

            foreach ($graph[$current] as $neighbor => $dist) {
                $tentative_gScore = $gScore[$current] + $dist;
                if ($tentative_gScore < ($gScore[$neighbor] ?? PHP_INT_MAX)) {
                    $cameFrom[$neighbor] = $current;
                    $gScore[$neighbor] = $tentative_gScore;
                    $fScore[$neighbor] = $gScore[$neighbor] + $this->heuristic($neighbor, $goal);
                    if (!in_array($neighbor, $openSet)) {
                        $openSet[] = $neighbor;
                    }
                }
            }
        }

        return ['path' => [], 'distance' => null]; // لا يوجد مسار
    }

    private function heuristic($a, $b)
    {
        [$lng1, $lat1] = explode(',', $a);
        [$lng2, $lat2] = explode(',', $b);
        return $this->calculateDistance(
            ['lat' => $lat1, 'lng' => $lng1],
            ['lat' => $lat2, 'lng' => $lng2]
        );
    }

    private function reconstructPath($cameFrom, $current)
    {
        $totalPath = [$current];
        while (isset($cameFrom[$current])) {
            $current = $cameFrom[$current];
            array_unshift($totalPath, $current);
        }
        return $totalPath;
    }

    private function calculateDistance($point1, $point2)
    {
        $lat1 = deg2rad($point1['lat']);
        $lon1 = deg2rad($point1['lng']);
        $lat2 = deg2rad($point2['lat']);
        $lon2 = deg2rad($point2['lng']);

        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;

        $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $radius = 6371; // km
        return $radius * $c;
    }

    private function parseNodeKey($nodeKey)
    {
        [$lng, $lat] = explode(',', $nodeKey);
        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng
        ];
    }

}