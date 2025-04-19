<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Station;

class StationSeeder extends Seeder
{
    
    public function run(): void
    {
    Station::truncate();

    Station::create([
        'name'=>'الصفا',
        'latitude'=>'33.57404787293895',
        'longitude'=>'36.41155345407785'
    ]);
    Station::create([
        'name'=>'عبدالرؤوف',
        'latitude'=>'33.57401463559988',
        'longitude'=>'36.40930949920835'
    ]);
    Station::create([
        'name'=>'ساحة الشهداء',
        'latitude'=>'33.57464160900419',
        'longitude'=>'36.40597023507053'
    ]);
    Station::create([
        'name'=>'جامع الرحمة',
        'latitude'=>'33.575726016156494',
        'longitude'=>'36.399129213841206'
    ]);
    Station::create([
        'name'=>'فرن راضي',
        'latitude'=>'33.57097217547802',
        'longitude'=>'36.40483084201179'
    ]);
    
    Station::create([
        'name'=>'بوظة صلاح',
        'latitude'=>'33.57108766478697',
        'longitude'=>'36.39822430694099'
    ]);
    
    Station::create([
        'name'=>'المسجد الكبير',
        'latitude'=>'33.57100793672703',
        'longitude'=>'36.4008781429433'
    ]);
    
    Station::create([
        'name'=>'حديقة الجلاء',
        'latitude'=>'33.57127155878139',
        'longitude'=>'36.41285937887474'
    ]);

    Station::create([
        'name'=>'الهمك',
        'latitude'=>'33.495007235623',
        'longitude'=>'36.322903633118'
    ]);
    
    Station::create([
        'name'=>'جامع بلال',
        'latitude'=>'33.503613956705',
        'longitude'=>'36.317088603973'
    ]);
    
    }
}