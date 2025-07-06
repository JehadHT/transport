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
