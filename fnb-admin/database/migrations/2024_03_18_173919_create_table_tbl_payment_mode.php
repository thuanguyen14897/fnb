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
        Schema::create('tbl_payment_mode', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name');
            $table->tinyInteger('type')->default(1)->comment('1 tiền mặt, 2 ngân hàng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_payment_mode');
    }
};
