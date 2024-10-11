<?php

namespace App\Console\Commands;

use DOMDocument;
use App\Patient;
use App\PatientStatus;
use App\PatientDocument;
use App\PatientDocumentType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Documents\PrepareDocumentDownload;
use Carbon\Carbon;

class UpdateCancelFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:cancel-fee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('update:cancel-fee');

        $paymentForServiceDocumentTypeId = PatientDocumentType::getPaymentForServiceId();

        $patientIds = PatientDocument::query()
            ->where('document_type_id', $paymentForServiceDocumentTypeId)
            ->pluck('patient_id');

        $patients = Patient::query()
            ->whereIn('status_id', [PatientStatus::getNewId(), PatientStatus::getActiveId()])
            ->whereIn('id', $patientIds)
            ->where('is_test', 0)
            ->get();

        foreach ($patients as $patient) {
            $initialAssessmentDoc = PatientDocument::query()
                ->where('patient_id', $patient->id)
                ->where('document_type_id', $paymentForServiceDocumentTypeId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (empty($initialAssessmentDoc)) {
                continue;
            }

            file_put_contents(storage_path('logs/cancel-fee.log'), PHP_EOL."PARSING START! PATIENT_ID: {$patient->id}; PATIENT_ID: {$patient->id}; DOC_ID: {$initialAssessmentDoc->id}", FILE_APPEND);
            
            $preparedData = \Bus::dispatchNow(new PrepareDocumentDownload($initialAssessmentDoc->id));

            if (empty($preparedData) || $preparedData === -1) {
                file_put_contents(storage_path('logs/cancel-fee.log'), PHP_EOL."INVALID DOCUMENT: {$preparedData}; PATIENT_ID: {$patient->id}; DOC_ID: {$initialAssessmentDoc->id}", FILE_APPEND);
                continue;
            }

            Storage::put('tmp.pdf', $preparedData['file']);

            try {
                // for local usage on Windows
                // $pdf = new \TonchikTm\PdfToHtml\Pdf(storage_path('app\tmp.pdf'), [
                //     'pdftohtml_path' => 'D:\programs\poppler-0.89.0\bin\pdftohtml',
                //     'pdfinfo_path' => 'D:\programs\poppler-0.89.0\bin\pdfinfo',
                // ]);

                $pdf = new \TonchikTm\PdfToHtml\Pdf(storage_path('app/tmp.pdf'), [
                    'pdftohtml_path' => '/usr/bin/pdftohtml',
                    'pdfinfo_path' => '/usr/bin/pdfinfo',
                ]);

                if (isset($pdf)) {
                    $firstPage = $pdf->getHtml()->getAllPages()[1];

                    $doc = new DOMDocument();
                    $doc->loadHTML($firstPage);
                    $ps = $doc->getElementsByTagName("p");
                    $pos = null;
                    $pmap = [];
                    foreach ($ps as $p) {
                        // $p->text;
                        $styleString = $p->getAttribute('style');
                        if (
                            strpos($p->nodeValue, "Charge") !== false &&
                            strpos($p->nodeValue, "cancellation") !== false &&
                            strpos($p->nodeValue, "without") !== false &&
                            strpos($p->nodeValue, "24") !== false &&
                            strpos($p->nodeValue, "hours") !== false
                        ) {
                            $pos = $this->getTopFromStyle($styleString);
                        }
                        $pmap[$this->getTopFromStyle($styleString)] = $p->nodeValue;
                    }
                    $res = null;
                    $pmap = array_reverse($pmap);
                    foreach ($pmap as $key => $val) {
                        if (abs(((int) str_replace('px', "", $key)) - ((int) str_replace('px', "", $pos))) < 10) {
                            $res = $val;
                            break;
                        }
                    }

                    file_put_contents(storage_path('logs/cancel-fee.log'), PHP_EOL."PREV VALUE: {$patient->charge_for_cancellation_appointment}; CURR VALUE: {$res}", FILE_APPEND);
                    if (strval($patient->charge_for_cancellation_appointment) !== $res) {
                        file_put_contents(storage_path('logs/cancel-fee.log'), PHP_EOL."VALUES IS NOT EQUAL! PREV VALUE: {$patient->charge_for_cancellation_appointment}; CURR VALUE: {$res}; PATIENT_ID: {$patient->id}; DOC_ID: {$initialAssessmentDoc->id};", FILE_APPEND);
                    }

                    // $patient->update(
                    //     [
                    //         'charge_for_cancellation_appointment' => $res,
                    //         'is_parsed_cancellation_fee' => 1,
                    //     ]
                    // );
                } else {
                    file_put_contents(storage_path('logs/cancel-fee.log'), PHP_EOL."PARSING ERROR! PATIENT_ID: {$patient->id}; DOC_ID: {$initialAssessmentDoc->id};", FILE_APPEND);

                    // $patient->update(
                    //     [
                    //         'charge_for_cancellation_appointment' => null,
                    //         'is_parsed_cancellation_fee' => 1,
                    //     ]
                    // );
                }
            } catch (\Exception $e) {
                file_put_contents(storage_path('logs/cancel-fee.log'), PHP_EOL."PARSING ERROR! PATIENT_ID: {$patient->id}; DOC_ID: {$initialAssessmentDoc->id}; {$e}", FILE_APPEND);
                \App\Helpers\SentryLogger::captureException($e);
            }
            
            Storage::delete(storage_path('app/tmp.pdf'));

            file_put_contents(storage_path('logs/cancel-fee.log'), PHP_EOL."PARSING END! --------------------------------------------------", FILE_APPEND);
        }
    }

    public function getTopFromStyle($style)
    {
        $styleArray = explode(";", $style);

        foreach ($styleArray as $styleElement) {
            $value = explode(":", $styleElement);
            if (trim($value[0]) == 'top') {
                return trim($value[1]);
            }
        }

        return null;
    }
}
