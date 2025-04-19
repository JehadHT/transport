<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'geometry'];

    protected $casts = [
        'geometry' => 'array',  // تأكد من أن العمود يتم تحويله إلى مصفوفة من GeoJSON
    ];}