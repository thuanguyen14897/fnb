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
        Schema::create('tbl_handover_record', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('reference_no')->unique();
            $table->dateTime('date');
            $table->unsignedInteger('transaction_id')->index();
            $table->unsignedInteger('customer_id')->index();
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_handover_record');
    }
};
