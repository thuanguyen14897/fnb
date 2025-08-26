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
        Schema::create('tbl_discount_app', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name');
            $table->double('percent');
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('default')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_discount_app');
    }
};
