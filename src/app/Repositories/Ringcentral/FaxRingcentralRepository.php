<?php

namespace App\Repositories\Ringcentral;

use App\Models\FaxModel\Fax;

use App\Helpers\Constant\LoggerConst;
use App\Helpers\Logger\LogActivityFax;
use App\Http\Requests\Fax\DownloadRequest;
use App\Http\Requests\Fax\ListFaxRequest;
use App\Http\Requests\Fax\UpdateFaxReadStatusRequest;
use App\Http\Requests\Fax\UpdateFaxUnreadStatusRequest;
use App\Repositories\Fax\FaxRepositoryInterface;
use App\Services\Ringcentral\RingcentralFax;
use App\Traits\ReplaceSpecialSymbolsTrait;
use Exception;
use Illuminate\Http\JsonResponse;

class FaxRingcentralRepository implements FaxRingcentralRepositoryInterface
{
    use ReplaceSpecialSymbolsTrait;

    protected $faxRepository;

    public function __construct(FaxRepositoryInterface $faxRepository)
    {
        $this->faxRepository = $faxRepository;
    }

    /**
     * @throws Exception
     */
    public function getListFax(ListFaxRequest $request): array
    {
        //request params
        $unread = $request->input('unread');
        $unassigned = $request->input('unassigned');
        $searchFaxByFullNamePatient = $request->input('search');

        $faxQuery = Fax::query();

        //unread filter request
        if (isset($unread) && $unread === 'false') {
            $faxQuery = $faxQuery->where('is_read', false);
        } elseif (isset($unread) && $unread === 'true') {
            $faxQuery = $faxQuery->where('is_read', true);
        }
        //unassigned filter request
        if (isset($unassigned) && $unassigned === 'false') {
            $faxQuery = $faxQuery->whereNull('patient_id')->whereNull('patient_lead_id');;
        } elseif (isset($unassigned) && $unassigned === 'true') {
            $faxQuery = $faxQuery->where(function ($query) {
                $query->where('patient_id', '>', 0)
                ->orWhere('patient_lead_id', '>', 0);
            });
        }
        //search request
        if (isset($searchFaxByFullNamePatient) && trim($searchFaxByFullNamePatient) !== '') {
            $searchFaxByFullNamePatient = $this->replaceSpecialCharacters($searchFaxByFullNamePatient);
            $faxQuery = $faxQuery->search($searchFaxByFullNamePatient);
        }

        $faxPagination = $faxQuery
            ->with(['provider', 'patient', 'patientLead'])
            ->where('direction','Inbound')
            ->orderBy('creationTime', 'desc')
            ->paginate();
        
        LogActivityFax::addToLog(LoggerConst::FAX_VIEWING);

        return $this->faxRepository->getFaxesData($faxPagination);
    }

    public function updateFaxesListReadStatus(UpdateFaxReadStatusRequest $request)
    {
        $faxes = $request->input('fax_id');
        Fax::whereIn('id', $faxes)->update(['is_read' => true]);
        foreach ($faxes as $faxId) {
            $faxData = Fax::where("id", $faxId)->first();
            //write log status about fax api
            LogActivityFax::addToLog(LoggerConst::FAX_MARKED_AS_READ, $faxId);
        }
        return new JsonResponse(['message' => "you have changed status 'is_read' to read status", 'status' => JsonResponse::HTTP_OK]);
    }

    public function updateFaxesListUnreadStatus(UpdateFaxUnreadStatusRequest $request)
    {
        $faxes = $request->input('fax_id');
        Fax::whereIn('id', $faxes)->update(['is_read' => false]);
        foreach ($faxes as $faxId) {
            $faxData = Fax::where("id", $faxId)->first();
            //write log status about fax api
            LogActivityFax::addToLog(LoggerConst::FAX_MARKED_AS_UNREAD, $faxId);
        }
        return new JsonResponse(['message' => "you have changed status 'is_read' to unread status", 'status' => JsonResponse::HTTP_OK]);
    }

    public function faxSyncList()
    {
        // page number by default
        $pageNumber = 1;
        \Bus::dispatchNow(new \App\Jobs\RingCentral\SyncCallFaxes($pageNumber));
        return new JsonResponse(['message' => "sync fax data by Ringcentral Api' ", 'status' => JsonResponse::HTTP_OK]);
    }

    public function downloadFaxPdf(DownloadRequest $request)
    {
        $pdfService = new RingcentralFax();
        return $pdfService->faxPdf($request);
    }

    public function faxViewPdf(DownloadRequest $request)
    {
        $pdfService = new RingcentralFax();
        return $pdfService->faxPdf($request);
    }
}
