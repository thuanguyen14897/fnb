<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewService extends Model
{
    use HasFactory;
    protected $table = 'tbl_review_service';

    function detail()
    {
        return $this->hasMany('App\Models\ReviewServiceDetail', 'review_service_id', 'id');
    }

    function service()
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }

    function image()
    {
        return $this->hasMany('App\Models\ReviewServiceImage', 'review_service_id', 'id');
    }

}
