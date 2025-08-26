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
        Schema::create('tbl_transaction_driver_cancel', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('transaction_id')->index();
            $table->unsignedInteger('driver_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction_driver_cancel');
    }
};
