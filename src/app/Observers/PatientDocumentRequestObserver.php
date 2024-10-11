<?php


namespace App\Observers;

use App\Helpers\HIPAALogger;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use Carbon\Carbon;

class PatientDocumentRequestObserver
{
    /**
     * @param PatientDocumentRequest $request
     * @return void
     */
    public function creating(PatientDocumentRequest $request)
    {
        $request->expiring_at = Carbon::now()->addDays(config('patient_document_request.uri_lifetime'))->toDateTimeString();
    }

    public function created(PatientDocumentRequest $request)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($request),
                'event' => 'create',
                'data' => $request->getLogData(),
                'message' => $request->getCreateLogMessage(),
            ]
        );
    }

    public function updated(PatientDocumentRequest $request)
    {
        $dirtyFields = $request->getDirtyWithOriginal();
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($request),
                'event' => 'update',
                'data' => $request->getLogData(),
                'dirty_fields' => $dirtyFields,
                'message' => $request->getUpdateLogMessage($dirtyFields),
            ]
        );
    }

    public function deleted(PatientDocumentRequest $request)
    {
        HIPAALogger::logEvent(
            [
                'collection' => class_basename($request),
                'event' => 'delete',
                'data' => $request->getLogData(),
                'message' => $request->getDeleteLogMessage(),
            ]
        );
    }
}