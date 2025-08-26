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
        Schema::create('tbl_transaction_info_guest', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('transaction_id')->index();
            $table->string('guest_name');
            $table->string('guest_phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction_info_guest');
    }
};
