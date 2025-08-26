<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDayItem extends Model
{
    use HasFactory;

    protected $table = 'tbl_transaction_day_item';

    function transaction_day()
    {
        return $this->belongsTo('App\Models\TransactionDay', 'transaction_day_id', 'id');
    }

    function transaction()
    {
        return $this->belongsTo('App\Models\Transaction', 'transaction_id', 'id');
    }

}
