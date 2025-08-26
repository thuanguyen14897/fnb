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
        Schema::create('tbl_blog_recruitment', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('title');
            $table->string('salary');
            $table->string('experience');
            $table->string('working_form');
            $table->string('degree');
            $table->string('gender');
            $table->string('quantity');
            $table->string('address');
            $table->text('descption');
            $table->text('content')->nullable();
            $table->text('job_requirement')->nullable();
            $table->text('your_benefit')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_blog_recruitment');
    }
};
