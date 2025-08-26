<?php namespace App\Helpers;
use Illuminate\Support\Facades\DB;

class AppHelper
{
    public static function lang($message = null, $key = 'message') {
        return trans($key . '.' . $message);
    }

    public static function get_datatables_language_array()
    {
        $lang = [
            'emptyTable'        => preg_replace("/{(\d+)}/", static::lang('dt_entries'), static::lang('dt_empty_table')),
            'info'              => preg_replace("/{(\d+)}/", static::lang('dt_entries'), static::lang('dt_info')),
            'infoEmpty'         => preg_replace("/{(\d+)}/", static::lang('dt_entries'), static::lang('dt_info_empty')),
            'infoFiltered'      => preg_replace("/{(\d+)}/", static::lang('dt_entries'), static::lang('dt_info_filtered')),
            'lengthMenu'        => '_MENU_',
            'loadingRecords'    => static::lang('dt_loading_records'),
            'processing'        => '<div class="dt-loader"></div>',
            'search'            => '<div class="input-group"><span class="input-group-addon"><span class="fa fa-search"></span></span>',
            'searchPlaceholder' => static::lang('dt_search'),
            'zeroRecords'       => static::lang('dt_zero_records'),
            'paginate'          => [
                'first'    => static::lang('dt_paginate_first'),
                'last'     => static::lang('dt_paginate_last'),
                'next'     => static::lang('dt_paginate_next'),
                'previous' => static::lang('dt_paginate_previous'),
            ],
            'aria' => [
                'sortAscending'  => static::lang('dt_sort_ascending'),
                'sortDescending' => static::lang('dt_sort_descending'),
            ],
        ];

        return $lang;
    }
}
