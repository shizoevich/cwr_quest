<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 30.06.2017
 * Time: 16:00
 */

namespace App\Http\Controllers;


trait RemoveArrayItems
{
    protected function removeId($item){
        return array_filter($item, function($k) {
            return $k !== 'id';
        }, ARRAY_FILTER_USE_KEY);
    }
}