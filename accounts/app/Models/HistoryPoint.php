<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryPoint extends Model
{
    use HasFactory;

    protected $table = 'tbl_client_point_history';

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }
}
