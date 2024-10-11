<?php


namespace App\Helpers;

use App\Models\FaxModel\UserFaxLogActivity;
use Illuminate\Support\Facades\Request;

class UserFaxLog
{
    public static function addToLog($subject)
    {
    	$log = [];
    	$log['subject'] = $subject;
    	$log['url'] = Request::fullUrl();
    	$log['method'] = Request::method();
    	$log['agent'] = Request::header('user-agent');
    	$log['user_id'] = auth()->check() ? auth()->user()->id : 1;
    	UserFaxLogActivity::create($log);
    }

    public static function logActivityLists()
    {
    	return UserFaxLogActivity::latest()->paginate();
    }
}
