<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    use HasFactory;

    // protected $fillable = ['name', 'geometry'];
    protected $guarded = [];
    protected $casts = ['geometry'];  // 'geometry' => 'array', تم تعديل هذا السطر
    
}