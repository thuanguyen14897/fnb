<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mavinoo\Batch\Traits\HasBatch;

class ReferralLevel extends Model
{
    use HasFactory;
    use HasBatch;

    protected $table='tbl_referral_level';

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

    function parent()
    {
        return $this->belongsTo('App\Models\Clients', 'parent_id', 'id');
    }
}
