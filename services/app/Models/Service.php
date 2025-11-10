<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class Service extends Model
{
    use HasFactory;

    protected $table = 'tbl_service';

    function image_store()
    {
        return $this->hasMany('App\Models\ServiceImage', 'service_id', 'id')->where('type',1);
    }

    function image_menu()
    {
        return $this->hasMany('App\Models\ServiceImage', 'service_id', 'id')->where('type',2);
    }

    function group_category_service()
    {
        return $this->belongsTo('App\Models\GroupCategoryService', 'group_category_service_id', 'id');
    }

    function category_service()
    {
        return $this->belongsTo('App\Models\CategoryService', 'category_service_id', 'id');
    }

    function other_amenities()
    {
        return $this->belongstoMany('App\Models\OtherAmenitiesService', 'tbl_other_amenities_service_service', 'service_id', 'other_amenities_service_id');
    }

    function province()
    {
        return $this->belongsTo('App\Models\Province', 'province_id', 'Id');
    }

    function ward()
    {
        return $this->belongsTo('App\Models\Ward', 'wards_id', 'Id');
    }

    function day()
    {
        return $this->hasMany('App\Models\ServiceDay', 'service_id', 'id');
    }

    function review()
    {
        return $this->hasMany('App\Models\ReviewService', 'service_id', 'id');
    }

    function favourite()
    {
        return $this->hasMany('App\Models\ServiceFavourite', 'service_id', 'id');
    }
}
