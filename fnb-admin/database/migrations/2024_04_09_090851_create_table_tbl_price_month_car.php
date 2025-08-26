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
        Schema::create('tbl_price_month_car', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('car_id')->index();
            $table->double('price');
            $table->date('date');
            $table->string('month');
            $table->unsignedInteger('time_end')->default(3);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_price_month_car');
    }
};
