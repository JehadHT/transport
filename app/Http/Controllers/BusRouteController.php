<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SplPriorityQueue;

class BusRouteController extends Controller
{
    /**
     * احسب أقصر مسار من محطة البداية إلى محطة النهاية
     * مع تفضيل البقاء على نفس خط الباص قدر الإمكان
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function find(Request $request)
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

        // $startId = collect($start)
        //     ->pluck('id')
        //     ->unique()
        //     ->values();

        // $endId = collect($end)
        //     ->pluck('id')
        //     ->unique()
        //     ->values();
        $startId = 1861 ;
        $endId = 1879 ;
        // عقوبة التبديل بين خطوط الباص (بالمتر)
        $switchPenalty = 300.0;

        // 1. احضار جميع المحطات (العقد) لإعادة بناء المسار لاحقاً
        $stations = DB::table('stations')
            ->select('id','latitude','longitude')
            ->get()
            ->keyBy('id');

        // 2. دالة المسافة التقديرية (Heuristic) - Haversine
        $h = function(int $u, int $v) use ($stations) {
            $from = $stations[$u];
            $to   = $stations[$v];
            $lat1 = deg2rad($from->latitude);
            $lon1 = deg2rad($from->longitude);
            $lat2 = deg2rad($to->latitude);
            $lon2 = deg2rad($to->longitude);
            $dlat = $lat2 - $lat1;
            $dlon = $lon2 - $lon1;
            $a = sin($dlat/2)**2 + cos($lat1)*cos($lat2)*sin($dlon/2)**2;
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            return 6371000 * $c; // نصف قطر الأرض بالمتر
        };

        // 3. هياكل بيانات A*
        $openSet = new SplPriorityQueue();
        $nodes   = []; // key => ['g','f','parent','station','route']

        // 4. تهيئة نقطة البداية (بدون route محدد بعد)
        $startKey = "{$startId}|";
        $nodes[$startKey] = [
            'g'       => 0.0,
            'f'       => $h($startId, $endId),
            'parent'  => null,
            'station' => $startId,
            'route'   => null,
        ];
        $openSet->insert($startKey, -$nodes[$startKey]['f']);

        // 5. حلقة البحث
        while (! $openSet->isEmpty()) {
            $currentKey = $openSet->extract();
            $current    = $nodes[$currentKey];
            list($u, $currRoute) = explode('|', $currentKey);

            // إذا وصلنا للنهاية، نبني المسار
            if ((int)$u === $endId) {
                return response()->json(
                    $this->reconstructPath($nodes, $currentKey)
                );
            }

            // جلب الحواف الصادرة من u
            $edges = DB::table('edges_noded')
                ->where('source', $u)
                ->get(['target','cost','route_id']);

            foreach ($edges as $edge) {
                $v        = $edge->target;
                $newRoute = $edge->route_id;
                // تطبيق عقوبة التبديل إن اختلف route
                $penalty = ($currRoute !== '' && $currRoute != $newRoute)
                        ? $switchPenalty
                        : 0.0;
                $tentativeG = $current['g'] + $edge->cost + $penalty;

                $childKey = "{$v}|{$newRoute}";
                $hCost    = $h($v, $endId);
                $fCost    = $tentativeG + $hCost;

                // إذا كانت هذه أول زيارة للعقدة أو وجدنا مساراً أرخص
                if (!isset($nodes[$childKey]) || $tentativeG < $nodes[$childKey]['g']) {
                    $nodes[$childKey] = [
                        'g'       => $tentativeG,
                        'f'       => $fCost,
                        'parent'  => $currentKey,
                        'station' => $v,
                        'route'   => $newRoute,
                    ];
                    $openSet->insert($childKey, -$fCost);
                }
            }
        }

        // لا مسار
        return response()->json(['message' => 'No path found'], 404);
    }

    /**
     * إعادة بناء المسار من النهاية للبداية
     *
     * @param array  $nodes
     * @param string $endKey
     * @return array
     */
    protected function reconstructPath(array $nodes, string $endKey): array
    {
        $path = [];
        $cur  = $endKey;
        while ($cur !== null) {
            $entry = $nodes[$cur];
            $path[] = [
                'station_id' => $entry['station'],
                'route_id'   => $entry['route'],
                'g_cost'     => $entry['g'],
            ];
            $cur = $entry['parent'];
        }
        return array_reverse($path);
    }
}


// namespace App\Http\Controllers;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Http\Request;
// use App\Models\BusRoute;
// use App\Models\Pin;

// class BusRouteController extends Controller
// {
//     public function ClosestStation(){
        
//     }
//     public function getShortestPathFromPins(Request $request)
//     {
//         try {
//             $feature = DB::table('geojson_features')
//             ->selectRaw('id, name, ST_AsGeoJSON(geometry) as geometry, properties')
//             ->get();

//             $routes = $feature->map(function ($route) {
//                 $geometry = json_decode($route->geometry, true);
//                 $properties = json_decode($route->properties, true);
                
//                 return (object) [
//                     'geometry' => $geometry,
//                     'properties' => $properties,
//                 ];
//             });
//             $validated = $request->validate([
//                 'startS' => 'required|array',
//                 'startS.latitude' => 'required|numeric|between:-90,90',
//                 'startS.longitude' => 'required|numeric|between:-180,180',
//                 'endS' => 'required|array',
//                 'endS.latitude' => 'required|numeric|between:-90,90',
//                 'endS.longitude' => 'required|numeric|between:-180,180',
//             ], [
//                 'startS.required' => 'يرجى تحديد نقطة البداية.',
//                 'endS.required' => 'يرجى تحديد نقطة النهاية.',
//             ]);
            
//             // dd($validated['startS']);
            
//             $startS = $validated['startS'];
//             $endS = $validated['endS'];
            
//             if (!$startS || !$endS) {
//                 return response()->json(['error' => 'الموقعين غير متوفرين.'], 400);
//             }
            
//             // استخراج الإحداثيات
//             $startLat = $startS['latitude'];
//             $startLng = $startS['longitude'];
//             $endLat = $endS['latitude'];
//             $endLng = $endS['longitude'];
//             // $routes = BusRoute::all();
//             $graph = $this->buildGraph($routes);

//             // 1️⃣ إيجاد أقرب عقدة لمسار الباص من نقطة البداية
//             $closestStartNode = $this->findClosestNode($graph, $startLat, $startLng);

//             // 2️⃣ إيجاد أقرب عقدة لمسار الباص من نقطة النهاية
//             $closestEndNode = $this->findClosestNode($graph, $endLat, $endLng);

//             // 3️⃣ المسار بواسطة الباص بين المحطتين
//             $busPath = $this->dijkstra($graph, $closestStartNode, $closestEndNode);

//             // 4️⃣ تحويل string إلى إحداثيات فعلية
//             $startNodeCoords = $this->parseNodeKey($closestStartNode);
//             $endNodeCoords = $this->parseNodeKey($closestEndNode);

//             // 5️⃣ دمج المسارات: من نقطة البداية إلى أقرب محطة، ثم الباص، ثم إلى الوجهة
//             $fullPath = [
//                 [$startLat, $startLng],
//                 [$startNodeCoords['lat'], $startNodeCoords['lng']],
//             ];

//             foreach ($busPath['path'] as $nodeKey) {
//                 $coord = $this->parseNodeKey($nodeKey);
//                 $fullPath[] = [$coord['lat'], $coord['lng']];
//             }

//             $fullPath[] = [$endNodeCoords['lat'], $endNodeCoords['lng']];
//             $fullPath[] = [$endLat, $endLng];

//             // 6️⃣ حساب المسافة الكلية التقريبية
//             $totalDistance = 0;
//             for ($i = 0; $i < count($fullPath) - 1; $i++) {
//                 $totalDistance += $this->calculateDistance(
//                     ['lat' => $fullPath[$i][0], 'lng' => $fullPath[$i][1]],
//                     ['lat' => $fullPath[$i + 1][0], 'lng' => $fullPath[$i + 1][1]]
//                 );
//             }

//             $formattedPath = array_map(function ($pair) {
//                 return implode(',', array_reverse($pair));
//             }, $fullPath);
            
    
//             return response()->json([
//                 'path' => $formattedPath,
//                 'distance' => $totalDistance
//             ]);
            
//         } catch (\Exception $e) {
//             return response()->json(['error' => 'حدث خطأ أثناء الحساب.'], 500);
//         }
//     }
//     private function buildGraph($routes)
//     {
//         $graph = [];
//         $allNodes = [];
    
//         // 1️⃣ استخراج كل النقاط من جميع المسارات
//         foreach ($routes as $route) {
//             $geometry = is_string($route->geometry) ? json_decode($route->geometry, true) : $route->geometry;

//             // $geometry = json_decode($route->geometry, true);
    
//             if ($geometry['type'] === 'LineString') {
//                 foreach ($geometry['coordinates'] as $coord) {
//                     $lat = $coord[0];
//                     $lng = $coord[1];
//                     $key = $this->nodeToKey(node: ['lat' => $lat, 'lng' => $lng]);
    
//                     // حفظ النقطة
//                     if (!isset($allNodes[$key])) {
//                         $allNodes[$key] = ['lat' => $lat, 'lng' => $lng];
//                     }
//                 }
//             }
//         }
    
//         // 2️⃣ ربط كل نقطة بجيرانها القريبين
//         foreach ($allNodes as $keyA => $nodeA) {
//             foreach ($allNodes as $keyB => $nodeB) {
//                 if ($keyA === $keyB) continue; // تجاهل نفس النقطة
    
//                 $distance = $this->calculateDistance($nodeA, $nodeB);
    
//                 if ($distance <= 0.15) { // لو المسافة أقل من 150 متر تقريبًا، نربطهم
//                     $graph[$keyA][] = [
//                         'node' => $keyB,
//                         'cost' => $distance,
//                     ];
//                 }
//             }
//         }
    
//         return $graph;
//     }

//     private function findClosestNode($graph, $lat, $lng)
//     {
//         $closest = null;
//         $minDistance = PHP_INT_MAX;

//         foreach ($graph as $nodeKey => $neighbors) {
//             [$nodeLng, $nodeLat] = explode(',', $nodeKey);
//             $dist = $this->calculateDistance(
//                 ['lat' => $lat, 'lng' => $lng],
//                 ['lat' => $nodeLat, 'lng' => $nodeLng]
//             );
//             if ($dist < $minDistance) {
//                 $minDistance = $dist;
//                 $closest = $nodeKey;
//             }
//         }

//         return $closest;
//     }

//     private function dijkstra($graph, $startKey, $endKey)
//     {
//         $queue = new \SplPriorityQueue();
//         $queue->insert($startKey, 0);
    
//         $distances = [$startKey => 0];
//         $previous = [];
    
//         while (!$queue->isEmpty()) {
//             $current = $queue->extract();
    
//             if ($current === $endKey) {
//                 return [
//                     'path' => $this->reconstructPath($previous, $current),
//                 ];
//             }
    
//             if (!isset($graph[$current])) continue;
    
//             foreach ($graph[$current] as $neighbor) {
//                 $alt = $distances[$current] + $neighbor['cost'];
    
//                 if (!isset($distances[$neighbor['node']]) || $alt < $distances[$neighbor['node']]) {
//                     $distances[$neighbor['node']] = $alt;
//                     $previous[$neighbor['node']] = $current;
//                     $queue->insert($neighbor['node'], -$alt); // نستخدم -alt لأن SplPriorityQueue يعطي الأولوية للأكبر
//                 }
//             }
//         }
    
//         return [
//             'path' => [], // لم يتم العثور على طريق
//         ];
//     }
    

//     // private function aStar($graph, $startKey, $endKey)
//     // {
//     //     $openSet = new \SplPriorityQueue();
//     //     $openSet->insert($startKey, 0);
    
//     //     $cameFrom = [];
//     //     $gScore = [$startKey => 0];
//     //     $fScore = [$startKey => $this->calculateDistance(
//     //         $this->parseNodeKey($startKey),
//     //         $this->parseNodeKey($endKey)
//     //     )];
    
//     //     while (!$openSet->isEmpty()) {
//     //         $current = $openSet->extract();
    
//     //         if ($current === $endKey) {
//     //             return [
//     //                 'path' => $this->reconstructPath($cameFrom, $current),
//     //             ];
//     //         }
    
//     //         if (!isset($graph[$current])) continue;
    
//     //         foreach ($graph[$current] as $neighbor) {
//     //             $tentativeGScore = $gScore[$current] + $neighbor['cost'];
    
//     //             if (!isset($gScore[$neighbor['node']]) || $tentativeGScore < $gScore[$neighbor['node']]) {
//     //                 $cameFrom[$neighbor['node']] = $current;
//     //                 $gScore[$neighbor['node']] = $tentativeGScore;
//     //                 $fScore[$neighbor['node']] = $tentativeGScore + $this->calculateDistance(
//     //                     $this->parseNodeKey($neighbor['node']),
//     //                     $this->parseNodeKey($endKey)
//     //                 );
//     //                 $openSet->insert($neighbor['node'], -$fScore[$neighbor['node']]);
//     //             }
//     //         }
//     //     }
    
//     //     return [
//     //         'path' => [], // لم يتم العثور على طريق
//     //     ];
//     // }
    

//     private function heuristic($a, $b)
//     {
//         [$lng1, $lat1] = explode(',', $a);
//         [$lng2, $lat2] = explode(',', $b);
//         return $this->calculateDistance(
//             ['lat' => $lat1, 'lng' => $lng1],
//             ['lat' => $lat2, 'lng' => $lng2]
//         );
//     }

//     private function reconstructPath($cameFrom, $current)
//     {
//         $totalPath = [$current];
//         while (isset($cameFrom[$current])) {
//             $current = $cameFrom[$current];
//             array_unshift($totalPath, $current);
//         }
//         return $totalPath;
//     }

//     private function calculateDistance($point1, $point2)
//     {
//         $lat1 = deg2rad($point1['lat']);
//         $lon1 = deg2rad($point1['lng']);
//         $lat2 = deg2rad($point2['lat']);
//         $lon2 = deg2rad($point2['lng']);

//         $dlat = $lat2 - $lat1;
//         $dlon = $lon2 - $lon1;

//         $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
//         $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

//         $radius = 6371; // km
//         return $radius * $c;
//     }

//     private function nodeToKey($node)
// {
//     return $node['lat'] . ',' . $node['lng'];
// }

//     private function parseNodeKey($nodeKey)
//     {
//         [$lng, $lat] = explode(',', $nodeKey);
//         return [
//             'lat' => (float) $lat,
//             'lng' => (float) $lng
//         ];
//     }
//     public function findClosestPointOnRoute(Request $request)
//     {
//         // 1. استلام الإحداثيات من المستخدم
//         $latitude = $request->input('latitude');
//         $longitude = $request->input('longitude');
    
//         // 2. إيجاد أقرب مسار (LineString)
//         $nearestRoute = DB::table('geojson_features')
//             ->select('id', 'geometry')
//             ->orderByRaw("geometry <-> ST_SetSRID(ST_MakePoint(?, ?), 4326)", [$longitude, $latitude])
//             ->limit(1)
//             ->first();
    
//         if (!$nearestRoute) {
//             return response()->json(['error' => 'No route found.'], 404);
//         }
    
//         // 3. حساب أقرب نقطة على هذا المسار
//         $closestPoint = DB::table('geojson_features')
//             ->selectRaw("
//                 ST_AsText(
//                     ST_ClosestPoint(
//                         geometry,
//                         ST_SetSRID(ST_MakePoint(?, ?), 4326)
//                     )
//                 ) AS closest_point
//             ", [$longitude, $latitude])
//             ->where('id', $nearestRoute->id)
//             ->first();
    
//         // 4. تحويل POINT(lon lat) إلى array
//         if ($closestPoint && preg_match('/POINT\(([-\d\.]+) ([-\d\.]+)\)/', $closestPoint->closest_point, $matches)) {
//             return response()->json([
//                 'closest_longitude' => $matches[1],
//                 'closest_latitude'  => $matches[2],
//                 'route_id' => $nearestRoute->id,
//             ]);
//         }
    
//         return response()->json(['error' => 'Could not extract closest point.'], 500);
//     }
// }