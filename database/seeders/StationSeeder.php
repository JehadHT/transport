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
        'latitude'=>'33.572937667496504',
        'longitude'=>'36.413511706883924'
    ]);

    Station::create([
        'name'=>'المدرسة السادسة',
        'latitude'=>'33.57402367864495',
        'longitude'=>'36.413172940067795'
    ]);

    Station::create([
        'name'=>'المدرسة المحدثة',
        'latitude'=>'33.57401945673797',
        'longitude'=>'36.41253160589355'
    ]);
    
    Station::create([
        'name'=>'بوظة صلاح',
        'latitude'=>'33.571150349360934',
        'longitude'=>'36.39814046425539'
    ]);
    
    Station::create([
        'name'=>'المسجد الكبير',
        'latitude'=>'33.570999980544755',
        'longitude'=>'36.40030547053124'
    ]);
    
    Station::create([
        'name'=>'حديقة الجلاء',
        'latitude'=>'33.57119314287736',
        'longitude'=>'36.412842062158006'
    ]);

    Station::create([
        'name'=>'مشفى حمدان',
        'latitude'=>'33.5710393258954',
        'longitude'=>'36.39923459998542'
    ]);

    Station::create([
        'name'=>'سوق الخضرة',
        'latitude'=>'33.57095698741192',
        'longitude'=>'36.40134164763248'
    ]);

    Station::create([
        'name'=>'سوق المتورات',
        'latitude'=>'33.570921882830845',
        'longitude'=>'36.40287914330591'
    ]);

    Station::create([
        'name'=>'شاورما العوافي',
        'latitude'=>'33.57101130535493',
        'longitude'=>'36.40669844047778'
    ]);

    Station::create([
        'name'=>'فروج المحبة',
        'latitude'=>'33.571063693992244',
        'longitude'=>'36.4087353815882'
    ]);

    Station::create([
        'name'=>'مدرسة الهاشمية',
        'latitude'=>'33.57112036099346',
        'longitude'=>'36.410403639602265'
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
    
    Station::create([
        'name'=>'جسر حرستا',
        'latitude'=>'33.56282835908107',
        'longitude'=>'36.360343848032585'
    ]);

    Station::create([
        'name'=>'مفرق حرستا',
        'latitude'=>'33.55876127047718',
        'longitude'=>'36.35710535039965'
    ]);

    Station::create([
        'name'=>'المشفى العسكري',
        'latitude'=>'33.57887426014362',
        'longitude'=>'36.37946859979371'
    ]);

    Station::create([
        'name'=>'مفرق الكورنيش',
        'latitude'=>'33.570835771526916',
        'longitude'=>'36.39495895684604'
    ]);

    Station::create([
        'name'=>'مطغم انجوي',
        'latitude'=>'33.57109851932266',
        'longitude'=>'36.397278676874976'
    ]);

    Station::create([
        'name'=>'موقف الدرج',
        'latitude'=>'33.538644098430495',
        'longitude'=>'36.335393946409965'
    ]);

    Station::create([
        'name'=>'موقف الكراجات',
        'latitude'=>'33.531300722844506',
        'longitude'=>'36.32482301445381'
    ]);

    Station::create([
        'name'=>'شعبة التجنيد',
        'latitude'=>'33.57665113767584',
        'longitude'=>'36.37427863785311'
    ]);

    Station::create([
        'name'=>'دوار بدران',
        'latitude'=>'33.57552789577622',
        'longitude'=>'36.395777547955646'
    ]);

    Station::create([
        'name'=>'دوار البلدية',
        'latitude'=>'33.57148164559062',
        'longitude'=>'36.39505150375717'
    ]);

    Station::create([
        'name'=>'مسجد النعسان',
        'latitude'=>'33.56970678135157',
        'longitude'=>'36.39471469008191'
    ]);

    Station::create([
        'name'=>'جسر الكورنيش',
        'latitude'=>'33.566832888714075',
        'longitude'=>'36.39255367170887'
    ]);

    Station::create([
        'name'=>'مفرق القوتلي',
        'latitude'=>'33.56449021527659',
        'longitude'=>'36.39587058754947'
    ]);

    Station::create([
        'name'=>'الأوقاف القديمة',
        'latitude'=>'33.565362324532074',
        'longitude'=>'36.395941510122725'
    ]);

    Station::create([
        'name'=>'مسجد البغدادي',
        'latitude'=>'33.5661914307188',
        'longitude'=>'36.396030127401474'
    ]);

    Station::create([
        'name'=>'شركة الفؤاد',
        'latitude'=>'33.56744657102216',
        'longitude'=>'36.396173249269395'
    ]);

    Station::create([
        'name'=>'بوظة النبلاء',
        'latitude'=>'33.568840982648055',
        'longitude'=>'36.396355544796165'
    ]);

    Station::create([
        'name'=>'محكمة دوما',
        'latitude'=>'33.57110110195519',
        'longitude'=>'36.39657952058661'
    ]);

    Station::create([
        'name'=>'موقف بوظة صلاح',
        'latitude'=>'33.57110619786019',
        'longitude'=>'36.398134556600354'
    ]);

    Station::create([
        'name'=>'مشفى البيروني',
        'latitude'=>'33.57278385238385',
        'longitude'=>'36.36778125752275'
    ]);
    
    }
}