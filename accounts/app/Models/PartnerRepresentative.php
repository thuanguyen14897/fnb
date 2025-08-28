<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerRepresentative extends Model
{
    use HasFactory;

    protected $table = 'tbl_partner_representative_info';

    function partner()
    {
        return $this->hasOne('App\Models\Clients', 'customer_id', 'id');
    }

    function image_cccd()
    {
        return $this->hasMany('App\Models\PartnerImage', 'partner_representative', 'id')->where('type','=',1);
    }

    function image_kd()
    {
        return $this->hasMany('App\Models\PartnerImage', 'partner_representative', 'id')->where('type','=',2);
    }
}
