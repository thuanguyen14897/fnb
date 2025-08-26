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
        Schema::create('tbl_referral_level', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('parent_id');
            $table->string('referral_code');
            $table->tinyInteger('level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_referral_level');
    }
};
