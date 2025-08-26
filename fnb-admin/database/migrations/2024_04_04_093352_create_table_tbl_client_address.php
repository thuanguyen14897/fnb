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
        Schema::create('tbl_client_address', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('province_id')->index();
            $table->unsignedInteger('district_id')->index();
            $table->unsignedInteger('ward_id')->index();
            $table->string('address');
            $table->string('name')->nullable();
            $table->tinyInteger('type')->default(0);
            $table->tinyInteger('default_address')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_client_address');
    }
};
