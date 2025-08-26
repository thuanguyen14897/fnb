<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryService extends Model
{
    use HasFactory;
    protected $table = 'tbl_category_service';

    function group_category_service()
    {
        return $this->belongsTo('App\Models\GroupCategoryService', 'group_category_service_id', 'id');
    }

    function other_amenities()
    {
        return $this->belongstoMany('App\Models\OtherAmenitiesService', 'tbl_other_amenities_service_category', 'category_service_id', 'other_amenities_service_id');
    }

    function service()
    {
        return $this->hasMany('App\Models\Service', 'category_service_id', 'id');
    }
}
