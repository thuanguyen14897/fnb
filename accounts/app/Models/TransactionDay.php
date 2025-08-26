<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDay extends Model
{
    use HasFactory;

    protected $table = 'tbl_transaction_day';

    function transaction_day_item()
    {
        return $this->hasMany('App\Models\TransactionDayItem', 'transaction_day_id', 'id');
    }

    function transaction()
    {
        return $this->belongsTo('App\Models\Transaction', 'transaction_id', 'id');
    }
}
