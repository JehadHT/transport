<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;

class StationController extends Controller
{

    public function show()
    {
        $stations = Station::all();
        return view('maps.map', compact('stations'));
    }
    
}