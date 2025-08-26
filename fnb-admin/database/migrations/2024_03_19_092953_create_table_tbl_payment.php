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
        Schema::create('tbl_payment', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('reference_no')->unique();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('payment_mode_id');
            $table->unsignedInteger('created_by');
            $table->double('total');
            $table->double('payment');
            $table->string('note');
            $table->timestamps();

            $table->index('customer_id');
            $table->index('payment_mode_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payment');
    }
};
