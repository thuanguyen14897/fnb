<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCategoryService extends Model
{
    use HasFactory;

    protected $table = 'tbl_group_category_service';

    function category_service()
    {
        return $this->hasMany('App\Models\CategoryService', 'group_category_service_id', 'id');
    }

    function service()
    {
        return $this->hasMany('App\Models\Service', 'group_category_service_id', 'id');
    }
}
