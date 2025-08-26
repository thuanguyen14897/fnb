<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationStaff extends Model
{
    use HasFactory;

    protected $table = 'tbl_notification_staff';

    function notification()
    {
        return $this->belongsTo('App\Models\Notification', 'notification_id', 'id');
    }
}
