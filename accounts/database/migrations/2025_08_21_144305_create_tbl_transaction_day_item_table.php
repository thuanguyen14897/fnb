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
        Schema::create('tbl_transaction_day_item', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('transaction_id')->index();
            $table->unsignedInteger('transaction_day_id')->index();
            $table->unsignedInteger('service_id')->index();
            $table->float('latitude',10,6)->nullable();
            $table->float('longitude',10,6)->nullable();
            $table->time('hour');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction_day_item');
    }
};
