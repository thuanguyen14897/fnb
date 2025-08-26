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
        Schema::create('tbl_pay_slip', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('reference_no')->unique();
            $table->dateTime('date');
            $table->unsignedInteger('object_id')->default(0);
            $table->unsignedInteger('transaction_driver_id')->default(0);
            $table->unsignedInteger('payment_mode_id')->index();
            $table->unsignedInteger('cost_id')->index();
            $table->double('total');
            $table->unsignedInteger('created_by')->default(0);
            $table->tinyInteger('type_create')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pay_slip');
    }
};
