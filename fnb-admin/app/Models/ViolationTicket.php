<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationTicket extends Model
{
    use HasFactory;

    protected $table = 'tbl_violation_ticket';

    function user()
    {
        return $this->belongsTo('App\Models\User', 'staff_id', 'id');
    }

}
