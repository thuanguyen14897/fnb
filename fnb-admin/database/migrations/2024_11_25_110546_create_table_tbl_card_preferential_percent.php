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
        Schema::create('tbl_card_preferential', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('category_card_id')->index();
            $table->unsignedInteger('category_preferential_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_card_preferential');
    }
};
