<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewServiceImage extends Model
{
    use HasFactory;

    protected $table = 'tbl_review_service_image';

    function service()
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }
}
