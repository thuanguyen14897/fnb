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
        Schema::create('tbl_become_partner', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('province_id');
            $table->string('name');
            $table->string('phone');
            $table->string('car');
            $table->tinyInteger('type')->comment('2 chủ xe, 3 tài xế');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_become_partner');
    }
};
