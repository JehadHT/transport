<?php

namespace App\Services\Pathfinding;

use Illuminate\Support\Facades\DB;
use SplPriorityQueue;

class AStarService
{
    protected array $nodes = [];

    protected array $graph = [];

    protected float $transferPenalty;

    protected array $gScore = [];
    protected array $fScore = [];
    protected array $cameFrom = [];

    public function __construct(float $transferPenalty = 200.0)
    {
        $this->transferPenalty = $transferPenalty;
    }

    public function loadGraph(): void
    {
        // $nodes = DB::table('edges_noded')
        //     ->select(
        //         'source as id',
        //         DB::raw('ST_Y(ST_StartPoint(the_geom))::float AS lat'),
        //         DB::raw('ST_X(ST_StartPoint(the_geom))::float AS lng')
        //     )
        //     ->groupBy('source', 'lat', 'lng')
        //     ->get()
        //     ->keyBy('id')
        //     ->toArray();
        
        $nodes = DB::table('edges_noded')
        ->select(
            DB::raw('UNNEST(ARRAY[source, target]) as id'),
            DB::raw('ST_Y(ST_StartPoint(the_geom))::float AS lat'),
            DB::raw('ST_X(ST_StartPoint(the_geom))::float AS lng')
        )
        ->groupBy('id', 'lat', 'lng')
        ->get()
        ->keyBy('id')
        ->toArray();

        $edges = DB::table('edges_noded')
            ->select(
                'source',
                'target',
                'cost',
                DB::raw('ST_AsGeoJSON(the_geom) as geojson'),
                'route_id'
            )
            ->get()
            ->toArray();

        $graph = [];
        foreach ($edges as $e) {
            $graph[$e->source][] = [
                'to'       => $e->target,
                'cost'     => (float) $e->cost,
                'geojson'  => json_decode($e->geojson),
                'route_id' => $e->route_id,
            ];
        }

        $this->nodes = $nodes;
        $this->graph = $graph;
    }

    protected function heuristic(int $a, int $b): float
    {
        $lat1 = $this->nodes[$a]->lat;
        $lng1 = $this->nodes[$a]->lng;
        $lat2 = $this->nodes[$b]->lat;
        $lng2 = $this->nodes[$b]->lng;

        $dLat = ($lat2 - $lat1) * 111000;
        $dLng = ($lng2 - $lng1) * 111000 * cos(deg2rad(($lat1 + $lat2) / 2));

        return sqrt($dLat * $dLat + $dLng * $dLng);
    }

    public function findPath(int $start, int $end): array
    {
        $this->loadGraph();

        $this->gScore = [];
        $this->fScore = [];
        $this->cameFrom = [];

        $this->gScore[$start][null] = 0;
        $this->fScore[$start][null] = $this->heuristic($start, $end);

        $openSet = new SplPriorityQueue();
        $openSet->insert(['node' => $start, 'route' => null], -$this->fScore[$start][null]);

        while (!$openSet->isEmpty()) {
            $currentState = $openSet->extract();
            $current = $currentState['node'];
            $currentRoute = $currentState['route'];

            if ($current === $end) {
                return $this->reconstructPath($current, $currentRoute);
            }

            if (!isset($this->graph[$current])) {
                continue;
            }

            foreach ($this->graph[$current] as $edge) {
                $neighbor = $edge['to'];
                $neighborRoute = $edge['route_id'];

                $penalty = ($currentRoute !== null && $neighborRoute !== $currentRoute)
                    ? 50000 : 0;

                $tentativeG = $this->gScore[$current][$currentRoute] + $edge['cost'] + $penalty;

                if (!isset($this->gScore[$neighbor][$neighborRoute]) || $tentativeG < $this->gScore[$neighbor][$neighborRoute]) {
                    $this->cameFrom[$neighbor][$neighborRoute] = [
                        'from'      => $current,
                        'fromRoute' => $currentRoute,
                        'edge'      => $edge,
                    ];

                    $this->gScore[$neighbor][$neighborRoute] = $tentativeG;
                    $this->fScore[$neighbor][$neighborRoute] = $tentativeG + $this->heuristic($neighbor, $end);

                    $openSet->insert(
                        ['node' => $neighbor, 'route' => $neighborRoute],
                        -$this->fScore[$neighbor][$neighborRoute]
                    );
                }
            }
        }

        return []; // لا يوجد مسار
    }

    protected function reconstructPath(int $current, $route): array
    {
        $path = [];

        while (isset($this->cameFrom[$current][$route])) {
            $data = $this->cameFrom[$current][$route];
            $edge = $data['edge'];

            $path[] = [
                'from'     => $data['from'],
                'to'       => $edge['to'],
                'cost'     => $edge['cost'],
                'geojson'  => $edge['geojson'],
                'route_id' => $edge['route_id'],
            ];

            $current = $data['from'];
            $route = $data['fromRoute'];
        }

        return array_reverse($path);
    }
}
