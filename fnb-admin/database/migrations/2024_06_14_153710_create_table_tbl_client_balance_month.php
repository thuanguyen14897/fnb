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
        Schema::create('tbl_client_balance_month', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('customer_id')->index();
            $table->double('balance')->default(0);
            $table->string('month','50');
            $table->string('year','50');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_client_balance_month');
    }
};
