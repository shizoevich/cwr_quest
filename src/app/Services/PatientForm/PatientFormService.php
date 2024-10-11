<?php


namespace App\Services\PatientForm;

use App\Appointment;
use App\Components\PatientForm\ConfidentialInformation\ConfidentialInformationForm;
use App\Components\PatientForm\NewPatient\NewPatientForm;
use App\Components\PatientForm\PaymentForService\PaymentForServiceForm;
use App\Components\PatientForm\PaymentForService\PaymentForServiceWithCreditCardForm;
use App\Components\PatientForm\Telehealth\TelehealthForm;
use App\Jobs\MakeImageThumbnail;
use App\Models\Patient\PatientDocumentConsentInfo;
use App\Models\Patient\PatientForm;
use App\Patient;
use App\PatientDocumentType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mpdf\MpdfException;

class PatientFormService
{
    /**
     * @param array $request
     * @param Patient $patient
     * @param bool $saveToForms
     * @param null $itemId
     * @throws MpdfException
     */
    public function saveConfidentialInformation(array $request, Patient $patient, $saveToForms = false, $itemId = null)
    {
        $newFileName = md5(uniqid(time())) . '.pdf';
        $documentMaker = new ConfidentialInformationForm($request);
        $documentMaker->fillDocument($newFileName);
        $documentTypeId = PatientDocumentType::getAuthToReleaseId();
        $this->saveDocumentToDb($request, $patient, $saveToForms, $itemId, $newFileName, $documentTypeId, 'Authorization to Release Confidential Information', PatientForm::TYPE_AUTHORIZATION_TO_RELEASE);
    }
    
    /**
     * @param array   $request
     * @param Patient $patient
     * @param false   $saveToForms
     * @param null    $itemId
     *
     * @throws MpdfException
     */
    public function saveNewPatient(array $request, Patient $patient, $saveToForms = false, $itemId = null)
    {
        $newFileName = md5(uniqid(time())) . '.pdf';
        $documentMaker = new NewPatientForm($request);
        $documentMaker->fillDocument($newFileName);
        $documentTypeId = PatientDocumentType::getNewPatientId();
        $this->saveDocumentToDb($request, $patient, $saveToForms, $itemId, $newFileName, $documentTypeId, PatientDocumentType::NEW_PATIENT_DOCUMENT_NAME, PatientForm::TYPE_NEW_PATIENT);
    }

    /**
     * @param array   $request
     * @param Patient $patient
     * @param false   $saveToForms
     * @param null    $itemId
     *
     * @throws MpdfException
     */
    public function savePaymentForService(array $request, Patient $patient, $saveToForms = false, $itemId = null, $withCreditCard = false)
    {
        $newFileName = md5(uniqid(time())) . '.pdf';
        $formClass = $withCreditCard ? PaymentForServiceWithCreditCardForm::class : PaymentForServiceForm::class;
        $documentMaker = new $formClass($request);
        $documentMaker->fillDocument($newFileName);
        $documentTypeId = PatientDocumentType::getPaymentForServiceId();
        $this->saveDocumentToDb($request, $patient, $saveToForms, $itemId, $newFileName, $documentTypeId, PatientDocumentType::PAYMENT_FOR_SERVICE_DOCUMENT_NAME, PatientForm::TYPE_PAYMENT_FOR_SERVICE);
    }
    
    /**
     * @param array   $request
     * @param Patient $patient
     * @param false   $saveToForms
     * @param null    $itemId
     *
     * @throws MpdfException
     */
    public function saveTelehealth(array $request, Patient $patient, $saveToForms = false, $itemId = null)
    {
        $newFileName = md5(uniqid(time())) . '.pdf';
        $documentMaker = new TelehealthForm($request);
        $documentMaker->fillDocument($newFileName);
        $documentTypeId = PatientDocumentType::getTelehealthId();
        $this->saveDocumentToDb($request, $patient, $saveToForms, $itemId, $newFileName, $documentTypeId, Appointment::REASON_TELEHEALTH, PatientForm::TYPE_TELEHEALTH);
    }
    
    /**
     * @param array   $request
     * @param Patient $patient
     * @param         $saveToForms
     * @param         $itemId
     * @param         $newFileName
     * @param         $documentTypeId
     */
    protected function saveDocumentToDb(array $request, Patient $patient, $saveToForms, $itemId, $newFileName, $documentTypeId, $documentName, $formTypeId)
    {
        $data = [
            'original_document_name' => $request['name'] . " - {$documentName}.pdf",
            'aws_document_name' => $newFileName,
            'document_type_id' => $documentTypeId,
            'document_request_item_id' => $itemId
        ];
        $patientDocument = $patient->documents()->create($data);
    
        $data['document_data'] = $request;
    
        if ($saveToForms) {
            $patientForm = new PatientForm();
            $patientForm->status = PatientForm::STATUS_NEW;
            $patientForm->type = $formTypeId;
            $patientForm->data = $data;
            $patientForm->visible_in_patient_forms_page = 0;
            $patient->patientForms()->save($patientForm);
        }

        if ($documentTypeId === PatientDocumentType::getNewPatientId()) {
            PatientDocumentConsentInfo::create([
                'patient_document_id' => $patientDocument->id,
                'allow_mailing' => $this->getBoolValueForConsentInfoItem($request['allow_mailing']),
                'allow_home_phone_call' => $this->getBoolValueForConsentInfoItem($request['allow_home_phone_call']),
                'allow_mobile_phone_call' => $this->getBoolValueForConsentInfoItem($request['allow_mobile_phone_call']),
                'allow_mobile_send_messages' => $this->getBoolValueForConsentInfoItem($request['allow_mobile_send_messages']),
                'allow_work_phone_call' => $this->getBoolValueForConsentInfoItem($request['allow_work_phone_call']),
            ]);
        }

        if ($documentTypeId === PatientDocumentType::getPaymentForServiceId()) {
            $patient->update([
                'charge_for_cancellation_appointment' => $request['charge_for_cancellation'] ?? 0,
                'is_parsed_cancellation_fee' => 1,
            ]);
        }
    }

    private function getBoolValueForConsentInfoItem($value): bool
    {
        return $value === 'Yes';
    }

    /**
     * @param array $request
     * @param Patient $patient
     * @param bool $saveToForms
     * @param null $itemId
     * @return bool
     */
    public function saveSupportingDocuments(array $request, Patient $patient, $saveToForms = false, $itemId = null)
    {
        $data = [];
        $isAllUploaded = true;
        $otherDocumentTypeID = PatientDocumentType::getSupportingDocumentId();
        foreach ($request['documents'] as $document) {
            $type = $document['type'];
            $sanitizedType = sanitize_name($type);
            $typeId = $otherDocumentTypeID;
            if(in_array($type, ['Insurance', 'Driver\'s License'])) {
                $subtypeId = optional(PatientDocumentType::query()->where('type', $type)->first())->id;
                if($subtypeId) {
                    $typeId = $subtypeId;
                    $type = null;
                }
            }
            $isTypeUploaded = true;
            foreach ($document['files'] as $file) {
                $ext = $file->getClientOriginalExtension();
                $fileName = md5(uniqid(time())) . ".$ext";
                $isUploadedSuccess = Storage::disk('patients_docs')->put($fileName,
                    file_get_contents($file));
                if ($isUploadedSuccess) {
                    dispatch(new MakeImageThumbnail($fileName));
                    $picture = [
                        'aws_document_name' => $fileName,
                        'original_document_name' => $sanitizedType . ".$ext",
                        'visible' => true,
                        'document_type_id' => $typeId,
                        'document_request_item_id' => $itemId,
                        'other_document_type' => $type
                    ];
                    $data[] = $picture;
                    $patient->documents()->create($picture);
                } else {
                    $isAllUploaded = false;
                    $isTypeUploaded = false;
                }
            }
            if ($isTypeUploaded && $saveToForms) {
                $patientForm = new PatientForm();
                $patientForm->status = PatientForm::STATUS_NEW;
                $patientForm->type = PatientForm::TYPE_PICTURE;
                $patientForm->data = $data;
                $patientForm->visible_in_patient_forms_page = 0;
                $patient->patientForms()->save($patientForm);
            }
        }
        return $isAllUploaded;
    }

    /**
     * @param PatientForm $form
     * @param int $status
     * @return bool
     */
    public function changeStatus(PatientForm $form, $status)
    {
        $form->status = $status;
        $form->reviewed_at = Carbon::now();
        $form->reviewed_by = auth()->id();
        $form->provider_id = auth()->user()->provider_id;
        return $form->save();
    }
}