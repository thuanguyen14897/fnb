<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerImage extends Model
{
    use HasFactory;

    protected $table = 'tbl_partner_image';

    function partner()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }
}
