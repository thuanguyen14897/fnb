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
        Schema::create('tbl_insurance_fee_calculation', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('type_business');
            $table->unsignedInteger('type_car');
            $table->double('number_seat')->default(0);
            $table->double('payload')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_insurance_fee_calculation');
    }
};
