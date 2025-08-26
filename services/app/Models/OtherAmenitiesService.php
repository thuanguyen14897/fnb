<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtherAmenitiesService extends Model
{
    use HasFactory;

    protected $table='tbl_other_amenities_service';

    function service()
    {
        return $this->belongstoMany('App\Models\Service', 'tbl_other_amenities_service_service', 'other_amenities_service_id', 'service_id');
    }

    function category()
    {
        return $this->belongstoMany('App\Models\CategoryService', 'tbl_other_amenities_service_category', 'other_amenities_service_id', 'category_service_id');
    }
}
