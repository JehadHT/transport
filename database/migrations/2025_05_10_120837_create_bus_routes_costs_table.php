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
        Schema::create('bus_routes_costs', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_id')->primary();
            $table->string('line_name');
            $table->unsignedInteger('costs'); // التكلفة بالليرة أو العملة المحلية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_routes_costs');
    }
};
