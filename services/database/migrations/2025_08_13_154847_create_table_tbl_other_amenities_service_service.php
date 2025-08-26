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
        Schema::create('tbl_other_amenities_service_service', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('other_amenities_service_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_other_amenities_service_service');
    }
};
