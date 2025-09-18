<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'tbl_payment';

    function payment_mode()
    {
        return $this->belongsTo('App\Models\PaymentMode', 'payment_mode_id', 'id');
    }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

    function transaction_bill()
    {
        return $this->hasOne('App\Models\TransactionBill', 'id', 'transaction_bill_id');
    }
}
