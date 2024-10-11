<?php

namespace App\Console\Commands;

use DOMDocument;
use App\Patient;
use App\PatientStatus;
use App\PatientDocument;
use App\PatientDocumentType;
use App\Models\Patient\PatientDocumentConsentInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Documents\PrepareDocumentDownload;
use Carbon\Carbon;

class ParseConsentInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reparse:consent-info';

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
        $documentTypeId = PatientDocumentType::getNewPatientId();
        $documentName = PatientDocumentType::NEW_PATIENT_DOCUMENT_NAME;

        $patientIds = PatientDocument::query()
            ->whereBetween('created_at', [Carbon::today()->subDays(2), Carbon::today()])
            ->where('document_type_id', $documentTypeId)
            ->pluck('patient_id');

        $patients = Patient::query()
            ->where('status_id', '!=' , PatientStatus::getArchivedId())
            ->whereIn('id', $patientIds)
            ->where('is_test', 0)
            ->get();

        foreach ($patients as $patient) {
            $initialAssessmentDoc = PatientDocument::query()
                ->where('patient_id', $patient->id)
                ->where('document_type_id', $documentTypeId)
                ->where('original_document_name', 'LIKE', "%{$documentName}%")
                ->orderBy('created_at', 'desc')
                ->first();

            if (empty($initialAssessmentDoc)) {
                continue;
            }
            
            $preparedData = \Bus::dispatchNow(new PrepareDocumentDownload($initialAssessmentDoc->id));

            if (empty($preparedData) || $preparedData === -1) {
                PatientDocumentConsentInfo::updateOrCreate([
                    'patient_document_id' => $initialAssessmentDoc->id,
                ], [
                    'allow_mailing' => null,
                    'allow_home_phone_call' => null,
                    'allow_mobile_phone_call' => null,
                    'allow_mobile_send_messages' => null,
                    'allow_work_phone_call' => null,
                ]);
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

                    $pmap = $this->getPosMap($ps);
                    $allowMailingPos = $this->getPosByText($ps, 'to support your treatment');
                    $allowHomePhoneCallPos = $this->getPosByText($ps, 'Phone (home):');
                    $allowMobilePhoneCallPos = $this->getPosByText($ps, 'Phone (mobile):');
                    $allowMobileSendMessagesPos = $this->getPosByText($ps, 'Okay to send treatment related text messages');
                    $allowWorkPhoneCallPos = $this->getPosByText($ps, 'Phone (work):');

                    $pmap = array_reverse($pmap);
                    $allowMailing = $this->getRadioInputValueByPos($pmap, $allowMailingPos);
                    $allowHomePhoneCall = $this->getRadioInputValueByPos($pmap, $allowHomePhoneCallPos);
                    $allowMobilePhoneCall = $this->getRadioInputValueByPos($pmap, $allowMobilePhoneCallPos);
                    $allowMobileSendMessages = $this->getRadioInputValueByPos($pmap, $allowMobileSendMessagesPos);
                    $allowWorkPhoneCall = $this->getRadioInputValueByPos($pmap, $allowWorkPhoneCallPos);

                    PatientDocumentConsentInfo::updateOrCreate([
                        'patient_document_id' => $initialAssessmentDoc->id,
                    ], [
                        'allow_mailing' => $allowMailing,
                        'allow_home_phone_call' => $allowHomePhoneCall,
                        'allow_mobile_phone_call' => $allowMobilePhoneCall,
                        'allow_mobile_send_messages' => $allowMobileSendMessages,
                        'allow_work_phone_call' => $allowWorkPhoneCall,
                    ]);
                } else {
                    PatientDocumentConsentInfo::updateOrCreate([
                        'patient_document_id' => $initialAssessmentDoc->id,
                    ], [
                        'allow_mailing' => null,
                        'allow_home_phone_call' => null,
                        'allow_mobile_phone_call' => null,
                        'allow_mobile_send_messages' => null,
                        'allow_work_phone_call' => null,
                    ]);
                }
            } catch (\Exception $e) {
                PatientDocumentConsentInfo::updateOrCreate([
                    'patient_document_id' => $initialAssessmentDoc->id,
                ], [
                    'allow_mailing' => null,
                    'allow_home_phone_call' => null,
                    'allow_mobile_phone_call' => null,
                    'allow_mobile_send_messages' => null,
                    'allow_work_phone_call' => null,
                ]);
            }
            
            Storage::delete(storage_path('app/tmp.pdf'));
        }
    }

    private function getPosMap($tags)
    {
        $map = [];
        foreach ($tags as $p) {
            $styleString = $p->getAttribute('style');
            $topPos = $this->getPosFromStyle($styleString, 'top');
            $leftPost = $this->getPosFromStyle($styleString, 'left');
            if (isset($map[$topPos])) {
                $map[$topPos][$leftPost] = $p->nodeValue;
            } else {
                $map[$topPos] = [$leftPost => $p->nodeValue];
            }
        }

        return $map;
    }

    private function getPosByText($tags, $text, $direction = 'top')
    {
        foreach ($tags as $p) {
            if ($this->compareNodeValueWithText($p->nodeValue, $text)) {
                $styleString = $p->getAttribute('style');
                return $this->getPosFromStyle($styleString, $direction);
            }
        }

        return null;
    }

    private function compareNodeValueWithText($nodeValue, $text)
    {
        $textArr = explode(' ', $text);
        foreach ($textArr as $word) {
            if (strpos($nodeValue, $word) === false) {
                return false;
            }
        }

        return true;
    }

    private function getPosFromStyle($style, $direction = 'top')
    {
        $styleArray = explode(";", $style);

        foreach ($styleArray as $styleElement) {
            $value = explode(":", $styleElement);
            if (trim($value[0]) == $direction) {
                return trim($value[1]);
            }
        }

        return null;
    }

    private function getRadioInputValueByPos($pmap, $searchPosTop)
    {
        $searchPosTopFormatted = (int) str_replace('px', "", $searchPosTop);
        foreach ($pmap as $posTop => $items) {
            $posTopFormatted = (int) str_replace('px', "", $posTop);
            
            if (abs($posTopFormatted - $searchPosTopFormatted) < 10) {
                foreach ($items as $posLeft => $val) {
                    $posLeftFormatted = (int) str_replace('px', "", $posLeft);
                    if ($posLeftFormatted === 724) { // 724px - position of "Yes" radio button 
                        return $val === '◉';
                    }
                }
            }
        }
    }
}
