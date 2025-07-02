<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConvertMultiLineToLineString extends Command
{
    
    // protected $signature = 'app:convert-multi-line-to-line-string';
    protected $signature = 'routes:convert-multiline';
    protected $description = 'تحويل MULTILINESTRING إلى LINESTRING وإنشاء جدول جديد';

    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::statement('DROP TABLE IF EXISTS bus_routes_linestring');

        // إنشاء الجدول الجديد مع تحويل MULTILINESTRING إلى LINESTRING
        DB::statement("
            CREATE TABLE bus_routes_linestring AS
            SELECT 
                row_number() OVER () AS id,
                name,
                ST_LineMerge((ST_Dump(geometry)).geom) AS geometry,
                properties,
                NOW() AS created_at,
                NOW() AS updated_at
            FROM bus_routes
            WHERE geometry IS NOT NULL
        ");

        // إضافة أعمدة source و target
        DB::statement("ALTER TABLE bus_routes_linestring ADD COLUMN source INTEGER;");
        DB::statement("ALTER TABLE bus_routes_linestring ADD COLUMN target INTEGER;");

        $this->info('✅ تم إنشاء جدول bus_routes_linestring وتحويل البيانات بنجاح.');
    
    }
}