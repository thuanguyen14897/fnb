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
        Schema::create('tbl_report_car', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->text('note');
            $table->unsignedInteger('report_id');
            $table->unsignedInteger('car_id');
            $table->timestamps();
            $table->index('report_id');
            $table->index('car_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_report_car');
    }
};
