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
        Schema::create('tbl_blog', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('image');
            $table->string('title');
            $table->string('descption');
            $table->string('content');
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('type')->default(1)->comment('1 tin tức,2 tuyển dụng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_blog');
    }
};
