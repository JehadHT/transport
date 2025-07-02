<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
            Schema::create('geojson_features', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // اسم العنصر إن أردت
                $table->geometry('geometry'); // الهندسة، مثال: LineString, Point, Polygon
                $table->jsonb('properties')->nullable(); // خصائص إضافية مثل اللون، الوصف، إلخ
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geojson_features');
    }
};