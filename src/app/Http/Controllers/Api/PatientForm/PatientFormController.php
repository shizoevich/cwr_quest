<?php


namespace App\Http\Controllers\Api\PatientForm;


use App\Components\Square\Customer;
use App\Components\Square\CustomerCard;
use App\Exceptions\Square\SquareException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PatientForm\BaseSavePatientFormRequest;
use App\Http\Requests\PatientForm\ChangePatientFormStatus;
use App\Http\Requests\PatientForm\SaveAllFormsRequest;
use App\Http\Requests\PatientForm\SaveSupportingDocumentsFormRequest;
use App\Http\Requests\PatientForm\StoreCreditCard as StoreCreditCardRequest;
use App\Models\Patient\DocumentRequest\PatientDocumentRequest;
use App\Models\Patient\DocumentRequest\PatientDocumentRequestItem;
use App\Models\Patient\DocumentRequest\PatientFormType;
use App\Models\Patient\PatientForm;
use App\Patient;
use App\Provider;
use App\Services\PatientForm\PatientFormService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Mpdf\MpdfException;
use App\Helpers\UphealHelper;

class PatientFormController extends Controller
{
    /** @var PatientFormService */
    private $patientFormService;

    public function __construct()
    {
        $this->patientFormService = new PatientFormService();
    }

    public function storeNewPatientForm(BaseSavePatientFormRequest $request, Patient $patient)
    {
    }

    /**
     * @param BaseSavePatientFormRequest $request
     * @param Patient $patient
     * @return JsonResponse
     * @throws MpdfException
     */
    public function storeConfidentalInformationForm(BaseSavePatientFormRequest $request, Patient $patient)
    {
        $this->patientFormService->saveConfidentialInformation($request->all(), $patient);
        return response()->json([
            'success' => true
        ], 201);
    }

    public function storeTelehealthForm(BaseSavePatientFormRequest $request, Patient $patient)
    {
    }

    public function storeSupportingDocumentsForm(SaveSupportingDocumentsFormRequest $request, Patient $patient)
    {
        $saved = $this->patientFormService->saveSupportingDocuments($request->all(), $patient);
        return response()->json([
            'success' => $saved,
            'message' => $saved ? 'The documents has been successfully uploaded.' : 'Error. Please try again.'
        ], 201);
    }

    public function storePaymentForServiceForm(BaseSavePatientFormRequest $request, Patient $patient)
    {
    }

    /**
     * @param SaveAllFormsRequest $request
     * @param Patient $patient
     * @param PatientDocumentRequest $documentRequest
     * @throws MpdfException
     */
    public function storeAll(SaveAllFormsRequest $request, Patient $patient, PatientDocumentRequest $documentRequest)
    {
        $data = $request->all();
        $signatureData = $data['signature_data'];
        $formTypes = PatientFormType::all()->pluck('id', 'name')->toArray();
        $forms = $data['forms'];
        $item = null;
        if (isset($forms['new_patient'])) {
            $item = $documentRequest->items()->where('form_type_id', $formTypes['new_patient'])->first();
            $this->patientFormService->saveNewPatient(
                array_merge($forms['new_patient'], $signatureData, (isset($forms['payment_for_service']) ? $forms['payment_for_service'] : [])),
                $patient,
                true,
                optional($item)->id
            );
            $documentRequest->items()
                ->whereIn(
                    'form_type_id',
                    [
                        $formTypes['new_patient'],
                        $formTypes['agreement_for_service_and_hipaa_privacy_notice_and_patient_rights'],
                    ]
                )->each(function (PatientDocumentRequestItem $item) {
                    $item->update(['filled_at' => Carbon::now()]);
                });

            $this->createUphealPatient($patient);
        }
        if (isset($forms['payment_for_service'])) {
            $item = $documentRequest->items()->where('form_type_id', $formTypes['payment_for_service'])->first();
            
            $cardNumber = '';
            if (isset($forms['payment_for_service']['card_data']) && isset($forms['payment_for_service']['card_data']['last_four'])) {
                $expirationDate = Carbon::createFromFormat('Y-m', "{$forms['payment_for_service']['card_data']['exp_year']}-{$forms['payment_for_service']['card_data']['exp_month']}")->format('m/y');
                $cardNumber = "**** **** **** {$forms['payment_for_service']['card_data']['last_four']} (Expires {$expirationDate})";
            }
            
            $deductible = ((float) $forms['payment_for_service']['payment_for_session_not_converted'] > 0) ? 'TBD**' : $forms['payment_for_service']['payment_for_session_not_converted'];
            $otherCharges = $forms['payment_for_service']['other_charges'] === 'null' ? '' : $forms['payment_for_service']['other_charges'];

            $this->patientFormService->savePaymentForService(
                array_merge($forms['payment_for_service'], $signatureData, ['card_number' => $cardNumber, 'payment_for_session_not_converted' => $deductible, 'other_charges' => $otherCharges]),
                $patient,
                true,
                optional($item)->id,
                isset($forms['credit_card_on_file'])
            );

            $documentRequest->items()
                ->whereIn(
                    'form_type_id',
                    [
                        $formTypes['payment_for_service'],
                        $formTypes['attendance_policy'],
                    ]
                )->each(function (PatientDocumentRequestItem $item) {
                    $item->update(['filled_at' => Carbon::now()]);
                });
        }
        if (isset($forms['confidential_information'])) {
            $item = $documentRequest->items()->where('form_type_id', $formTypes['confidential_information'])->first();
            if (
                isset($forms['confidential_information']['exchange_with']) 
                && is_array($forms['confidential_information']['exchange_with']) 
                && count($forms['confidential_information']['exchange_with'])
            ) {
                foreach ($forms['confidential_information']['exchange_with'] as $exchangeWith) {
                    $this->patientFormService->saveConfidentialInformation(
                        array_merge($forms['confidential_information'], $signatureData, ['hereby_information_with' => $exchangeWith]), 
                        $patient,
                        true,
                        optional($item)->id
                    );
                }
            } else {
                $this->patientFormService->saveConfidentialInformation(
                    array_merge($forms['confidential_information'], $signatureData),
                    $patient,
                    true,
                    optional($item)->id
                );
            }
            $item->update(['filled_at' => Carbon::now()]);
        }
        if (isset($forms['telehealth'])) {
            $item = $documentRequest->items()->where('form_type_id', $formTypes['telehealth'])->first();
            $this->patientFormService->saveTelehealth(array_merge($forms['telehealth'], $signatureData), $patient, true, optional($item)->id);
            $item->update(['filled_at' => Carbon::now()]);
        }
        if (isset($forms['supporting_documents'])) {
            $item = $documentRequest->items()->where('form_type_id', $formTypes['supporting_documents'])->first();
            $this->patientFormService->saveSupportingDocuments($forms['supporting_documents'], $patient, true, optional($item)->id);
            $item->update(['filled_at' => Carbon::now()]);
        }
        if (isset($forms['credit_card_on_file'])) {
            $item = $documentRequest->items()->where('form_type_id', $formTypes['credit_card_on_file'])->first();
            $item->update(['filled_at' => Carbon::now()]);
        }
    }

    private function createUphealPatient(Patient $patient)
    {
        if (isset($patient->upheal_user_id)) {
            return; 
        }

        $providerId = $patient->getLastAppointmentProviderId();
        if (empty($providerId)) {
            return;
        }

        $provider = Provider::find($providerId);
        if (empty($provider) || !$provider->works_with_upheal || empty($provider->upheal_user_id)) {
            return;
        }

        try {
            UphealHelper::createPatient($provider, $patient);
        } catch (\Exception $exception) {
            \App\Helpers\SentryLogger::captureException($exception);
        }
    }

    /**
     * @param ChangePatientFormStatus $request
     * @param PatientForm $patientForm
     * @return JsonResponse
     */
    public function approve(ChangePatientFormStatus $request, PatientForm $patientForm)
    {
        $updated = $this->patientFormService->changeStatus($patientForm, PatientForm::STATUS_APPROVED);
        return response()->json(['success' => $updated], 200);
    }

    /**
     * @param ChangePatientFormStatus $request
     * @param PatientForm $patientForm
     * @return JsonResponse
     */
    public function decline(ChangePatientFormStatus $request, PatientForm $patientForm)
    {
        $updated = $this->patientFormService->changeStatus($patientForm, PatientForm::STATUS_DECLINED);
        return response()->json(['success' => $updated], 200);
    }
    
    /**
     * Copied from old class
     * @param StoreCreditCardRequest $request
     * @param Patient                $patient
     * @param PatientDocumentRequest $documentRequest
     *
     * @return JsonResponse
     */
    public function storeCreditCard(StoreCreditCardRequest $request, Patient $patient, PatientDocumentRequest $documentRequest)
    {
        try {
            $squareCustomer = new Customer();
            $squareAccount = $squareCustomer->createIfNotExist($patient, [
                'email' => $request->input('email'),
            ]);
            $squareCustomerCardService = new CustomerCard();
            $squareCustomerCardService->create($squareAccount, $request->get('nonce'), $request->input('zip'), [
                'address_line_1' => $request->input('home_address'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
            ]);
        } catch (SquareException $e) {
            \App\Helpers\SentryLogger::captureException($e);
            return response()->json([
                'success' => false,
                'errors' => $e->getErrors(),
            ], 400);
        }
        
        return response()->json(['success' => true], 200);
    }
}