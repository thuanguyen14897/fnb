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
        Schema::create('tbl_transfer_package', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('reference_no')->unique();
            $table->dateTime('date');
            $table->string('username');
            $table->string('password');
            $table->double('total')->default(0);
            $table->double('fee')->default(0);
            $table->double('total_fee')->default(0);
            $table->double('grand_total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transfer_package');
    }
};
