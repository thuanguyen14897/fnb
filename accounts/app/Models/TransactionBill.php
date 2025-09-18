<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionBill extends Model
{
    use HasFactory;

    protected $table = 'tbl_transaction_bill';

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }

    function partner()
    {
        return $this->belongsTo('App\Models\Clients', 'partner_id', 'id');
    }

    function transaction()
    {
        return $this->belongsTo('App\Models\Transaction', 'transaction_id', 'id');
    }

    function transaction_day_item()
    {
        return $this->belongsTo('App\Models\TransactionDayItem', 'transaction_day_item_id', 'id');
    }

    function payment()
    {
        return $this->hasOne('App\Models\Payment', 'transaction_bill_id', 'id');
    }

}
