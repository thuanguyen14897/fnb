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
        Schema::create('tbl_driver_service', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('category_car_detail_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_driver_service');
    }
};
