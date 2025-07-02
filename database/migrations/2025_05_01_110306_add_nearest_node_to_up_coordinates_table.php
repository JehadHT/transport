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
        Schema::table('up_coordinates', function (Blueprint $table) {
            $table->decimal('closest_latitude', 17, 15)->nullable();
            $table->decimal('closest_longitude', 17, 15)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('up_coordinates', function (Blueprint $table) {
            //
        });
    }
};