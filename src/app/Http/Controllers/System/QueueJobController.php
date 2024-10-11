<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\QueueJob\BulkDelete as BulkDeleteJobsRequest;

class QueueJobController extends Controller
{

    public function bulkDelete(BulkDeleteJobsRequest $request)
    {
        \DB::table('jobs')->whereIn('id', $request->input('jobs'))->delete();
        
        return back();
    }
}
