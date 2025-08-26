<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'tbl_transaction';

    function transaction_day()
    {
        return $this->hasMany('App\Models\TransactionDay', 'transaction_id', 'id');
    }

    function transaction_day_item()
    {
        return $this->hasMany('App\Models\TransactionDayItem', 'transaction_id', 'id');
    }

    function customer()
    {
        return $this->belongsTo('App\Models\Clients', 'customer_id', 'id');
    }
}
