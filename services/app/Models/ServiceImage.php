<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceImage extends Model
{
    use HasFactory;

    protected $table = 'tbl_service_image';

    function service()
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }
}
