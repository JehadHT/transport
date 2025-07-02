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
        // database/migrations/xxxx_xx_xx_create_buses_table.php
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('سائق 1');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->double('speed')->nullable();
            $table->enum('status', ['active', 'inactive', 'offline'])->default('inactive');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
