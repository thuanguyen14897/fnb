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
        Schema::create('tbl_setup_qa', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('category_setup_qa_id')->index();
            $table->string('name');
            $table->unsignedInteger('parent_id');
            $table->tinyInteger('type')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_setup_qa');
    }
};
