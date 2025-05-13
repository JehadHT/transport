<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BusRouteCost;

class BusRoutesCostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bus_routes_costs')->truncate();
        DB::table('bus_routes_costs')->insert([
            [
                'cost_id' => 1,
                'line_name' => 'دوما',
                'costs' => 2000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cost_id' => 2,
                'line_name' => 'دمشق-دوما',
                'costs' => 5000,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
