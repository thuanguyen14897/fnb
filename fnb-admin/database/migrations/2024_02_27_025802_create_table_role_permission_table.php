<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Schema to create roles table
        Schema::create('tbl_roles', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Schema to create group permissions table
        Schema::create('tbl_group_permissions', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Schema to create permissions table
        Schema::create('tbl_permissions', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('group_permission_id');
            $table->timestamps();
        });

        // Schema to create role_users table
        Schema::create('tbl_role_user', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('user_id');

            $table->foreign('user_id')->references('id')->on('tbl_users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('role_id')->references('id')->on('tbl_roles')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'role_id']);
        });

        // Schema to create permission_role table
        Schema::create('tbl_permission_role', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('group_permission_id');

            $table->foreign('permission_id')->references('id')->on('tbl_permissions')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('tbl_roles')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('group_permission_id')->references('id')->on('tbl_group_permissions')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('tbl_user_permission', function (Blueprint $table) {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('user_id');

            $table->foreign('permission_id')->references('id')->on('tbl_permissions')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('tbl_users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['permission_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_permission_role');
        Schema::dropIfExists('tbl_role_user');
        Schema::dropIfExists('tbl_permissions');
        Schema::dropIfExists('tbl_roles');
        Schema::dropIfExists('tbl_user_permission');
        Schema::dropIfExists('tbl_group_permissions');
    }
};
