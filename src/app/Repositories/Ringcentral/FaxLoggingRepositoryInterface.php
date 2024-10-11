<?php

namespace App\Repositories\Ringcentral;

use App\Http\Requests\Fax\DownloadRequest;
use App\Http\Requests\Fax\LoggingListRequest;

interface FaxLoggingRepositoryInterface
{
    public function getLoggingListOfFaxes(LoggingListRequest $request);

    public function logging(DownloadRequest $request);
}