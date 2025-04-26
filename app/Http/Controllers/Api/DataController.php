<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function getData()
    {
        return response()->json([
            'message' => 'تم جلب البيانات بنجاح!',
            'data' => [
                'item1' => 'قيمة 1',
                'item2' => 'قيمة 2',
            ]
        ]);
    }
}