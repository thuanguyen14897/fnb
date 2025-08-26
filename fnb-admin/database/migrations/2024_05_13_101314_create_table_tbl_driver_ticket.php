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
        Schema::create('tbl_driver_ticket', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('reference_no')->unique();
            $table->string('point_start');
            $table->string('point_end');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_driver_ticket');
    }
};
