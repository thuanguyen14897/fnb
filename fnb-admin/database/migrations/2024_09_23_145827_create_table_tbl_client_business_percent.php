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
        Schema::create('tbl_client_business_percent', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('business_percent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_client_business_percent');
    }
};
