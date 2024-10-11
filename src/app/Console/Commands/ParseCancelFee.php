<?php

namespace App\Console\Commands;

use App\Patient;
use DOMDocument;
use App\PatientDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Documents\PrepareDocumentDownload;
use App\PatientDocumentType;
use Carbon\Carbon;

class ParseCancelFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reparse:cancel-fee';

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
        $this->info('reparse:cancel-fee');

        $paymentForServiceDocumentTypeId = PatientDocumentType::getPaymentForServiceId();

        $patientIds = PatientDocument::query()
          ->whereBetween('created_at', [Carbon::today()->subDays(2), Carbon::today()])
          ->where('document_type_id', $paymentForServiceDocumentTypeId)
          ->pluck('patient_id');

        $patients = Patient::query()
            ->where('status_id','!=','7')
            ->whereIn('id', $patientIds)
            ->get();

        foreach ($patients as $patient) {
            $paymentForServiceDoc = PatientDocument::query()
                ->where('patient_id', $patient->id)
                ->where('document_type_id', $paymentForServiceDocumentTypeId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (isset($paymentForServiceDoc) && $paymentForServiceDoc !== null) {
                $preparedData = \Bus::dispatchNow(new PrepareDocumentDownload($paymentForServiceDoc->id));

                Storage::put('tmp.pdf', $preparedData['file']);

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
                    $pmap = array_reverse($pmap);
                    $res = null;
                    foreach ($pmap as $key => $val) {
                        if (abs(((int) str_replace('px', "", $key)) - ((int) str_replace('px', "", $pos))) < 10) {
                            $res = $val;
                            break;
                        }
                    }

                    $patient->update(
                        [
                            'charge_for_cancellation_appointment' => $res,
                            'is_parsed_cancellation_fee' => 1,
                        ]
                    );
                } else {
                    $patient->update(
                        [
                            'charge_for_cancellation_appointment' => null,
                            'is_parsed_cancellation_fee' => 1,
                        ]
                    );
                }

                Storage::delete(storage_path('app/tmp.pdf'));
            }
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
