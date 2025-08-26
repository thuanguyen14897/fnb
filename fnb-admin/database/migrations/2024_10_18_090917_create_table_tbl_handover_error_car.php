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
        Schema::create('tbl_handover_error_car', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('handover_id');
            $table->unsignedInteger('category_error_car_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_handover_error_car');
    }
};
