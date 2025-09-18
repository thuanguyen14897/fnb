<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    use HasFactory;

    protected $table = 'tbl_payment_mode';

    function payment()
    {
        return $this->hasMany('App\Models\Payment', 'payment_mode_id', 'id');
    }
}
