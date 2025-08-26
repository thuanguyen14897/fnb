<?php

namespace App\Libraries;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class App {

    private $options = [];
    public function __construct()
    {
        $this->init();
    }

    public function init(){
        $options = Cache::remember('options',3600, function () {
            return DB::table('tbl_options')->where('autoload',1)->get();
        });

        if (!empty($options)){
            foreach ($options as $key => $value){
                $this->options[$value->name] = $value->value;
            }
        }
    }

    public function flushCache(){
        if(Cache::has('options')){
            return Cache::flush('options');
        }
    }

    public function get_option($name)
    {

        $val  = '';
        $name = trim($name);
        if (!isset($this->options[$name])) {
            $row = DB::table('tbl_options')->where('name', $name)->get()->first();
            if ($row) {
                $val = $row->value;
            }
        } else {
            $val = $this->options[$name];
        }
        return $val;
    }
}
