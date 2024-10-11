<?php

namespace App\Http\Controllers\Api\Ringcentral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fax\DownloadRequest;
use App\Http\Requests\Fax\LoggingListRequest;
use App\Repositories\Ringcentral\FaxLoggingRepositoryInterface;

class FaxLoggingController extends Controller
{
    private $faxLoggingRepository;

    public function __construct(FaxLoggingRepositoryInterface $faxLoggingRepository)
    {
        $this->faxLoggingRepository = $faxLoggingRepository;
    }

    public function index(LoggingListRequest $request)
    {
       return $this->faxLoggingRepository->getLoggingListOfFaxes($request);
    }

    public function logging(DownloadRequest $request)
    {
       return  $this->faxLoggingRepository->logging($request);
    }
}
