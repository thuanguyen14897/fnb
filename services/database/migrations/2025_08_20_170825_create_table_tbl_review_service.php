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
        Schema::create('tbl_review_service', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('service_id')->index();
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('transaction_id')->index();
            $table->double('star')->default(5);
            $table->string('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_review_service');
    }
};
