<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Option;
use Illuminate\Http\Request;

class SystemController extends Controller
{

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $jobs = \DB::table('jobs')
            ->orderByDesc('created_at')
            ->get();
        $parserErrorMailEnabled = boolval(Option::getOptionValue('parser_error_mail_enabled'));

        return view('system.index', compact('jobs', 'parserErrorMailEnabled'));
    }
}
