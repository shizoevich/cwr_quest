<?php

namespace App\Http\Controllers\Api\Ringcentral;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fax\DownloadRequest;
use App\Http\Requests\Fax\ListFaxRequest;
use App\Http\Requests\Fax\UpdateFaxReadStatusRequest;
use App\Http\Requests\Fax\UpdateFaxUnreadStatusRequest;
use App\Repositories\Ringcentral\FaxRingcentralRepositoryInterface;

class FaxController extends Controller
{
    private $faxRepository;

    public function __construct(FaxRingcentralRepositoryInterface $faxRepository)
    {
        $this->faxRepository = $faxRepository;
    }

    public function index(ListFaxRequest $request)
    {
       return $this->faxRepository->getListFax($request);
    }
 
    public function updateReadStatus(UpdateFaxReadStatusRequest $request)
    {
        return  $this->faxRepository->updateFaxesListReadStatus($request);
    }

    public function updateUnreadStatus(UpdateFaxUnreadStatusRequest $request)
    {
        return  $this->faxRepository->updateFaxesListUnreadStatus($request);
    }

    public function faxSync()
    {
        return $this->faxRepository->faxSyncList();
    }

    public function download(DownloadRequest $request)
    {
       return  $this->faxRepository->downloadFaxPdf($request);
    }

    public function faxView(DownloadRequest $request)
    {
       return  $this->faxRepository->faxViewPdf($request);
    }

}
