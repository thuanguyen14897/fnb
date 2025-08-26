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
        Schema::create('tbl_customer_class', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('category_card_id')->index();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->double('total')->default(0);
            $table->double('number_night')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_customer_class');
    }
};
