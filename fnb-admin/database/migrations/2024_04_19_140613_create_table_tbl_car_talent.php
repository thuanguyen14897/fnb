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
        Schema::create('tbl_car_talent', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('car_id')->index();
            $table->tinyInteger('book_car_flash_talent')->default(0);
            $table->double('from_book_car_flash_talent')->default(0);
            $table->double('to_book_car_flash_talent')->default(0);
            $table->tinyInteger('delivery_car_talent')->default(0);
            $table->double('km_delivery_car_talent')->default(0);
            $table->double('fee_km_delivery_car_talent')->default(0);
            $table->double('free_km_delivery_car_talent')->default(0);
            $table->double('total_km_day')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_car_talent');
    }
};
