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
        Schema::create('tbl_driver', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('fullname');
            $table->string('avatar');
            $table->string('phone');
            $table->string('email');
            $table->text('password');
            $table->date('birthday');
            $table->tinyInteger('gender')->default(0)->comment('1 nam,2 nữ,3 khác');
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_driver');
    }
};
