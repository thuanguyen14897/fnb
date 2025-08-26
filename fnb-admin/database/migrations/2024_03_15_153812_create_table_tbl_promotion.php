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
        Schema::create('tbl_promotion', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name');
            $table->string('code');
            $table->tinyInteger('type')->default(0)->comment('0 phần trăm,1 tiền mặt');
            $table->double('percent');
            $table->double('cash');
            $table->double('money_max')->default(0);
            $table->tinyInteger('indefinite')->default(0)->comment('1 vô thời hạn');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->tinyInteger('type_car')->default(0)->comment('Loại tự lái, có tài');
            $table->string('detail')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_promotion');
    }
};
