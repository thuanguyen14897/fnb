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
        Schema::create('tbl_image_driver', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('driver_id')->index();
            $table->string('name');
            $table->tinyInteger('type')->comment('1 cccd, 2 lltp,3 hạnh kiểm,4 giấy khám sức khỏe,4 hiv');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_image_driver');
    }
};
