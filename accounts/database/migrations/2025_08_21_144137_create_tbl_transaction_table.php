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
        Schema::create('tbl_transaction', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('reference_no')->unique();
            $table->dateTime('date');
            $table->dateTime('date_start');
            $table->dateTime('date_end');
            $table->tinyInteger('status')->default(0);
            $table->unsignedInteger('staff_status')->index();
            $table->dateTime('date_status')->nullable();
            $table->string('note_status')->nullable();
            $table->unsignedInteger('customer_id')->index();
            $table->unsignedInteger('created_by')->index();
            $table->tinyInteger('type_created')->default(2);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction');
    }
};
