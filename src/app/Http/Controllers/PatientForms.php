<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class PatientForms extends Controller {
    protected function checkButton($btnType, $val, $size = 11) {
        $radioCode = '&#9673;';
        $emptyRadioCode = '&#9675;';
//        $checkboxCode = '&#9745;';
//        $emptyCheckboxCode = '&#9744;';
        $checkbox = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24"><path d="M22 2v20h-20v-20h20zm2-2h-24v24h24v-24zm-5.541 8.409l-1.422-1.409-7.021 7.183-3.08-2.937-1.395 1.435 4.5 4.319 8.418-8.591z"/></svg>';
        $emptyCheckbox = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24"><path d="M22 2v20h-20v-20h20zm2-2h-24v24h24v-24z"/></svg>';
        switch($btnType) {
            case 'r':   //radiobutton
                if($val === 'Yes') {
                    $temp1 = $radioCode;
                    $temp2 = $emptyRadioCode;
                } else {
                    $temp1 = $emptyRadioCode;
                    $temp2 = $radioCode;
                }
                return ['yes' => $temp1, 'no' => $temp2];
            case 'c':   //checkbox
                if($val === 'Yes') {
                    return $checkbox;
                } else {
                    return $emptyCheckbox;
                }
            default:
                return [];
        }
    }

    abstract public function saveForm(Request $request);

    abstract public function mapToPdfFields(array $data);
}
