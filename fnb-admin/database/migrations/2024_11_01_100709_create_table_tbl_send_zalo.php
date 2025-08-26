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
        Schema::create('tbl_send_zalo', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('template_id');
            $table->text('content')->nullable();
            $table->string('send_zalo_id')->nullable();
            $table->string('event')->nullable();
            $table->text('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_send_zalo');
    }
};
