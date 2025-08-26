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
        Schema::create('tbl_transaction_alepay', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('code',50);
            $table->string('message');
            $table->text('signature');
            $table->string('transactionCode')->nullable();
            $table->unsignedInteger('transaction_kanow_id')->default(0)->index();
            $table->string('orderCode')->nullable();
            $table->double('amount')->default(0);
            $table->string('currency')->nullable();
            $table->string('buyerEmail')->nullable();
            $table->string('buyerPhone')->nullable();
            $table->string('cardNumber')->nullable();
            $table->string('buyerName')->nullable();
            $table->string('status',50)->nullable();
            $table->text('reason')->nullable();
            $table->text('description')->nullable();
            $table->string('installment',50)->nullable();
            $table->string('is3D',50)->nullable();
            $table->double('month')->default(0);
            $table->string('bankCode')->nullable();
            $table->string('bankName')->nullable();
            $table->string('method')->nullable();
            $table->string('bankType')->nullable();
            $table->double('transactionTime')->nullable();
            $table->double('successTime')->nullable();
            $table->string('bankHotline')->nullable();
            $table->double('merchantFee')->nullable();
            $table->double('payerFee')->nullable();
            $table->text('authenCode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction_alepay');
    }
};
