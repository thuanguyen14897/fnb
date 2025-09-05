<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceFavourite extends Model
{
    use HasFactory;

    protected $table = 'tbl_favourite_service';

    function service()
    {
        return $this->belongsTo('App\Models\Service', 'service_id', 'id');
    }
}
