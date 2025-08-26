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
        Schema::create('tbl_customer_card_alepay', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('cardLinkStatus')->nullable();
            $table->string('email')->nullable();
            $table->string('customerId')->nullable();
            $table->string('token')->nullable();
            $table->string('cardNumber')->nullable();
            $table->string('cardHolderName')->nullable();
            $table->string('cardExpireMonth')->nullable();
            $table->string('cardExpireYear')->nullable();
            $table->string('paymentMethod')->nullable();
            $table->string('bankCode')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->nullable();
            $table->string('bankType')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_customer_card_alepay');
    }
};
