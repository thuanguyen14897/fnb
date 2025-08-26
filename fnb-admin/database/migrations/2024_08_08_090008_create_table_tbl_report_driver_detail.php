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
        Schema::create('tbl_report_driver_detail', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('report_driver_id')->index();
            $table->unsignedInteger('category_report_driver_id')->index();
            $table->string('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_report_driver_detail');
    }
};
