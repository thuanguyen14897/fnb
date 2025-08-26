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
        Schema::create('tbl_register_insurance', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->unsignedInteger('insurance_company_id');
            $table->tinyInteger('type')->default(1)->comment('1 xe máy,2 ô tô');
            $table->tinyInteger('type_motobike')->default(1);
            $table->string('year_manu')->nullable();
            $table->tinyInteger('type_business')->default(1);
            $table->tinyInteger('type_car')->default(1);
            $table->double('number_seat')->default(0);
            $table->double('payload')->default(0);
            $table->string('name_owner');
            $table->string('phone_owner');
            $table->string('identification_card_owner');
            $table->string('email_owner');
            $table->tinyInteger('type_info_car')->default(1);
            $table->string('license_plates')->nullable()->comment('biển số xe');
            $table->string('vehicle_engine_number')->nullable()->comment('Số máy xe');
            $table->string('chassis_number')->nullable()->comment('Số khung xe');
            $table->unsignedInteger('province_id');
            $table->unsignedInteger('district_id');
            $table->unsignedInteger('ward_id');
            $table->string('address');
            $table->double('grand_total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_register_insurance');
    }
};
