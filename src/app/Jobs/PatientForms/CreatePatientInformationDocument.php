<?php

namespace App\Jobs\PatientForms;

use App\Components\PatientForm\NewPatient\NewPatientForm;
use App\Components\PatientForm\PaymentForService\PaymentForServiceForm;
use App\Patient;
use App\PatientDocumentType;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class CreatePatientInformationDocument
 * @package App\Jobs\PatientForms
 */
class CreatePatientInformationDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Patient
     */
    private $patient;
    /**
     * @var array
     */
    private $data;

    /**
     * Create a new job instance.
     *
     * @param Patient $patient
     * @param array $data
     */
    public function __construct(Patient $patient, array $data)
    {
        $this->patient = $patient;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @param $request
     * @return void
     * @throws \Mpdf\MpdfException
     */
    public function handle()
    {
        $request = new Request();
        $request->replace($this->data);

        $this->updateChargeForCancellationIfNeeded($request);

        $this->createDocument($request, NewPatientForm::class, PatientDocumentType::getNewPatientId(), PatientDocumentType::NEW_PATIENT_DOCUMENT_NAME);
        $this->createDocument($request, PaymentForServiceForm::class, PatientDocumentType::getPaymentForServiceId(), PatientDocumentType::PAYMENT_FOR_SERVICE_DOCUMENT_NAME);
    }

    /**
     * Update cancellation charge for the patient.
     *
     * @param Request $request
     */
    private function updateChargeForCancellationIfNeeded(Request $request)
    {
        if ($request->has('charge_for_cancellation') && !is_null($request->charge_for_cancellation)) {
            $charge = intval($request->charge_for_cancellation);
            if ($charge >= 0) {
                $this->patient->charge_for_cancellation_appointment = $charge;
                $this->patient->save();
            }
        }
    }

    /**
     * Create a document for the patient.
     *
     * @param Request $request
     * @param string $formClass
     * @param int $documentTypeId
     * @param string $documentTypeName
     */
    private function createDocument(Request $request, $formClass, $documentTypeId, $documentTypeName)
    {
        $fileName = md5(uniqid(time())) . '.pdf';
        $form = new $formClass($request->all());
        $form->fillDocument($fileName);

        $this->patient->documents()->create([
            'original_document_name' => $request->name . " - " . $documentTypeName . ".pdf",
            'aws_document_name' => $fileName,
            'document_type_id' => $documentTypeId,
        ]);
    }
}
