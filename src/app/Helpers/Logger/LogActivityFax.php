<?php


namespace App\Helpers\Logger;
use App\Models\FaxModel\UserFaxLogActivity;
use Illuminate\Support\Facades\Request;

class LogActivityFax
{
    public static function addToLog($subject, $faxId  = null, $patientId = null, $patientLeadId = null)
    {
    	$log = [];
    	$log['subject'] = $subject;
    	$log['url'] = Request::fullUrl();
    	$log['method'] = Request::method();
    	$log['ip'] = Request::ip();
    	$log['agent'] = Request::header('user-agent');
    	$log['user_id'] = auth()->check() ? auth()->user()->id : 1;
        $log['fax_id'] =  $faxId;
        $log['patient_id'] = $patientId;
        $log['patient_lead_id'] = $patientLeadId;
    	return UserFaxLogActivity::create($log);
    }
}