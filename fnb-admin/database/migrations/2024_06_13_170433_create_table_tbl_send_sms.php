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
        Schema::create('tbl_send_sms', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('phone');
            $table->string('brand_name');
            $table->text('message');
            $table->longText('log')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->dateTime('date_send');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_send_sms');
    }
};
