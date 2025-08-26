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
        Schema::create('tbl_service', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name');
            $table->string('image')->nullable();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('group_category_service_id')->index();
            $table->unsignedInteger('category_service_id')->index();
            $table->unsignedInteger('province_id')->index();
            $table->unsignedInteger('wards_id')->index();
            $table->unsignedInteger('created_by')->default(0);
            $table->tinyInteger('type_create')->default(1);
            $table->double('price')->default(0);
            $table->string('address')->nullable();
            $table->float('latitude',10,6)->nullable();
            $table->float('longitude',10,6)->nullable();
            $table->string('name_location')->nullable();
            $table->text('detail')->nullable();
            $table->text('rules')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->tinyInteger('hot')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_service');
    }
};
