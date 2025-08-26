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
        Schema::create('tbl_transfer_money', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->dateTime('date');
            $table->string('reference_no')->unique();
            $table->unsignedInteger('request_withdraw_money_id')->index();
            $table->unsignedInteger('payment_mode_id')->index();
            $table->unsignedInteger('customer_id')->index();
            $table->tinyInteger('type')->default(1);
            $table->double('total')->default(0);
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->double('account_number')->default(0);
            $table->string('phone_number')->nullable();
            $table->unsignedInteger('created_by')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transfer_money');
    }
};
