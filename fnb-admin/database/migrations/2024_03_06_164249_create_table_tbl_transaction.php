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
        Schema::create('tbl_transaction', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->double('price')->default(0);
            $table->double('promotion')->default(0);
            $table->double('grand_total')->default(0);
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->unsignedInteger('car_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('promotion_id');
            $table->tinyInteger('type_promotion')->comment('1 promotion car, 2 promotion');
            $table->timestamps();
            $table->index('car_id');
            $table->index('customer_id');
            $table->index('promotion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction');
    }
};
