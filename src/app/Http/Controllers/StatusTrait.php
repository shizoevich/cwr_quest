<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 29.06.2017
 * Time: 15:05
 */

namespace App\Http\Controllers;
use App\Status;

trait StatusTrait
{
    public function getStatus($id){
        
        $status = Status::find($id);
        
        return $status;
    }

    public function getAllStatuses(){

        $statuses = Status::all();

        return $statuses;
    }
}