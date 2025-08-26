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
        Schema::create('tbl_surcharge_car_car', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('car_id');
            $table->unsignedInteger('surcharge_car_id');
            $table->timestamps();
            $table->index('car_id');
            $table->index('surcharge_car_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_surcharge_car_car');
    }
};
