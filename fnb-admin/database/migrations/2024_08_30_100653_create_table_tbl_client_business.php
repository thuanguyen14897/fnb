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
        Schema::create('tbl_client_business', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('customer_id')->index();
            $table->string('name_company');
            $table->string('phone_company');
            $table->string('vat_company');
            $table->string('address_company')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 chưa duyệt,1 duyệt,2 không duyệt');
            $table->unsignedInteger('staff_status')->default(0);
            $table->dateTime('date_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_client_business');
    }
};
