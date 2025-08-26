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
        Schema::create('tbl_transaction_comment_file', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('comment_id')->index();
            $table->string('name_file');
            $table->string('filetype');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_transaction_comment_file');
    }
};
