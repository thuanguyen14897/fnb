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
        Schema::create('tbl_client_balance_history', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('object_id');
            $table->string('object_type');
            $table->double('balance')->default(0);
            $table->tinyInteger('type_check')->default(1)->comment('1 tăng tiền,2 trừ tiền');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_client_balance_history');
    }
};
