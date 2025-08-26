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
        Schema::create('table_tbl_driving_liscense_client', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('number_liscense');
            $table->string('fullname');
            $table->string('birthday');
            $table->string('image');
            $table->unsignedInteger('customer_id');
            $table->timestamps();
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_tbl_driving_liscense_client');
    }
};
