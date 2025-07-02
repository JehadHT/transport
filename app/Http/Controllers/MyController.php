<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class MyController extends Controller
{
    public function createTopology()
{
    $result = DB::select("
    SELECT * FROM pgr_createTopology(
        'bus_routes_linestring',
        0.0001,
        'geometry',
        'id',
        'source',
        'target',
        '',  -- قم بترك هذه المعامل فارغًا للتحقق من التوبولوجيا بشكل أساسي
        true
    );
");



    return response()->json([
        'message' => '✅ تم إنشاء التوبولوجيا بنجاح',
        'result' => $result
    ]);
}
}