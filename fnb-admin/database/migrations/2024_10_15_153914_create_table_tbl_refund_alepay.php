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
        Schema::create('tbl_refund_alepay', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('transaction_kanow_id')->default(0);
            $table->string('refundCode')->nullable();
            $table->string('transactionCode')->nullable();
            $table->string('orderCode')->nullable();
            $table->double('refundAmount')->default(0);
            $table->double('totalRefundToPayer')->default(0);
            $table->double('refundFee')->default(0);
            $table->string('reason')->nullable();
            $table->string('refundStatus')->nullable();
            $table->string('refundTime')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_refund_alepay');
    }
};
