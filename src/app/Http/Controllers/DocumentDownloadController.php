<?php

namespace App\Http\Controllers;

use App\PatientDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DocumentDownloadController extends Controller
{


    public function index($documentName){

        $documentData = PatientDocument::find('aws_document_name');
//        $downloadAttempts;
//        $downloadPeriod;

        return view('document')->with(compact('documentName'));
    }

    public function getMandrillLog() {
        $log = \Illuminate\Support\Facades\DB::select(\Illuminate\Support\Facades\DB::raw("
            select patient_document_shared.recipient, shared_document_statuses.status, patient_document_shared.patient_documents_id, patient_document_shared.document_model,
            (select count(*) from patient_document_download_info where patient_document_download_info.patient_document_shared_id = patient_document_shared.id) as download_count,
            patient_document_shared.created_at
            from patient_document_shared
            join patient_document_shared_logs on patient_document_shared_logs.patient_document_shared_id = patient_document_shared.id
            join shared_document_statuses on shared_document_statuses.id = patient_document_shared_logs.shared_document_statuses_id
            where shared_document_methods_id = 1 and recipient like '%kp.org' and status != 'queued'
            order by created_at
        "));

        $handle = fopen(storage_path('mandril_statistic.csv'), 'w');
        fputcsv($handle, [
            'recipient',
            'status',
            'download count',
            'send document date',

            'document id',
            'document model',
            'document created_at',
            'patient name',
            'patient url'
        ]);


        $result = [];
        foreach ($log as $item) {
            $document = ($item->document_model)::findOrFail($item->patient_documents_id);
            $patient = $document->patient;

            $effStopDate = $patient->eff_stop_date;
            $daysDiff = 0;
            if (!is_null($effStopDate)) {
                $effStopDate = \Carbon\Carbon::parse($effStopDate);
                $now = \Carbon\Carbon::now();
                $daysDiff = $now->diffInDays($effStopDate, false);
            }
            if (($daysDiff <= 14) || is_null($effStopDate)) {
                $item->patient_url = 'https://admin.cwr.care/chart/' . $patient->id;
                $item->patient_name = $patient->first_name . ' ' . $patient->last_name;
                $item->eff_stop_date = $patient->eff_stop_date;
                $result[] = $item;
                $data = [
                    $item->recipient,
                    $item->status,
                    $item->download_count,
                    $document->created_at,
                    $item->patient_documents_id,
                    $item->document_model,
                    $item->created_at,
                    $item->patient_name,
                    $item->patient_url,
                    $effStopDate,
                ];

                fputcsv($handle, $data);
            }


        }

        fclose($handle);
        dd($result);
    }
}
