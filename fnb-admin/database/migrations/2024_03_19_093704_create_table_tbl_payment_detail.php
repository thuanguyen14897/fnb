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
        Schema::create('tbl_payment_detail', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('transaction_id');
            $table->double('payment');
            $table->timestamps();


            $table->index('payment_id');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payment_detail');
    }
};
