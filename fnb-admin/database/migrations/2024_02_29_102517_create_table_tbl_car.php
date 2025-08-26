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
        Schema::create('tbl_car', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name');
            $table->unsignedInteger('company_car_id');
            $table->unsignedInteger('type_car_id');
            $table->unsignedInteger('customer_id');
            $table->text('detail');
            $table->text('rules');
            $table->double('rent_cost');
            $table->unsignedInteger('other_amenities_id');
            $table->unsignedInteger('feature_id');
            $table->index('company_car_id');
            $table->index('type_car_id');
            $table->index('customer_id');
            $table->index('other_amenities_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_car');
    }
};
