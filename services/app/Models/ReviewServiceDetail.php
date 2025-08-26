<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewServiceDetail extends Model
{
    use HasFactory;
    protected $table = 'tbl_review_service_detail';

    function review()
    {
        return $this->belongsTo('App\Models\ReviewService', 'review_service_id', 'id');
    }
}
