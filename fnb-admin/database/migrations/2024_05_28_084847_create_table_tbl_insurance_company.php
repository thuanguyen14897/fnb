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
        Schema::create('tbl_insurance_company', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('image');
            $table->string('name');
            $table->string('compensation_people_car')->nullable();
            $table->string('compensation_people_motobike')->nullable();
            $table->string('compensation_property_car')->nullable();
            $table->string('compensation_property_motobike')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_insurance_company');
    }
};
