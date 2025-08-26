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
        Schema::create('tbl_transaction_route', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('transaction_id')->index();
            $table->double('total_route')->default(0);
            $table->double('duration_value')->default(0);
            $table->string('duration_text')->nullable();
            $table->float('lat_start')->nullable();
            $table->float('lng_start')->nullable();
            $table->float('lat_end')->nullable();
            $table->float('lng_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction_route');
    }
};
