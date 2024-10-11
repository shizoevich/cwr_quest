<?php

namespace App\Repositories\Ringcentral;

use App\Http\Requests\Fax\DownloadRequest;
use App\Http\Requests\Fax\ListFaxRequest;
use App\Http\Requests\Fax\UpdateFaxReadStatusRequest;
use App\Http\Requests\Fax\UpdateFaxUnreadStatusRequest;

interface FaxRingcentralRepositoryInterface
{
    public function getListFax (ListFaxRequest $request): array;

    public function updateFaxesListReadStatus(UpdateFaxReadStatusRequest $request);

    public function updateFaxesListUnreadStatus(UpdateFaxUnreadStatusRequest $request);

    public function faxSyncList();

    public function downloadFaxPdf(DownloadRequest $request);

    public function faxViewPdf(DownloadRequest $request);
}