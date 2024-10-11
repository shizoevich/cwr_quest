<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 29.06.2017
 * Time: 15:05
 */

namespace App\Http\Controllers;
use App\Office;

trait OfficeTrait
{
    public function getOffice($id){
        
        $office = Office::find($id);
        
        return $office;
    }

    public function getAllOffices(){

        $offices = Office::with('rooms')->get();

        return $offices;
    }
}