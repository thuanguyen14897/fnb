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
        Schema::create('tbl_homepage_country_currency', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('country_currency_id');
            $table->string('country_currency_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_homepage_country_currency');
    }
};
