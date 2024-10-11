<?php

namespace App\Http\Controllers;

use App\Components\PatientForm\NewPatient\CreateInformationForm;
use App\Components\Square\Customer;
use App\Components\Square\CustomerCard;
use App\Exceptions\Square\SquareException;
use App\Jobs\PatientForms\CreatePatientInformationDocument;
use App\Patient;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;

class PatientFormFirstController
{
    use RemoveArrayItems, DispatchesJobs;

    /**
     * Save Form
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function saveForm(Request $request)
    {
        $patient = Patient::findOrFail($request->input('patient_id'));

        //create Information Form
        $informationFormDataMaker = new CreateInformationForm();
        $informationFormData = $informationFormDataMaker->create($patient, $request->all());
        if ($patient->informationForm === null) {
            $patient->informationForm()->create($informationFormData);
        } else {
            $patient->informationForm()->update($informationFormData);
        }

        //create square account
        if (!$request->has('dont_create_square_customer')) {
            try {
                $squareCustomerService = new Customer();
                
                $squareAccount = $squareCustomerService->createIfNotExist($patient, [
                    'first_name' => $patient->first_name,
                    'last_name' => $patient->last_name,
                    'email' => $request->input('email'),
                    'mobile_phone' => $request->input('mobile_phone'),
                    'address_line_1' => $request->input('home_address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'zip' => $request->input('zip'),
                ]);
                if($request->get('store_credit_card') && $request->get('credit_card_nonce')) {
                    $squareCustomerCardService = new CustomerCard();
                    $squareCustomerCardService->create($squareAccount, $request->get('credit_card_nonce'), $request->input('zip'));
                }
            } catch (SquareException $e) {
                \App\Helpers\SentryLogger::captureException($e);
                return response()->json([
                    'success' => false,
                    'errors' => $e->getErrors(),
                ], 400);
            }
        }

        $this->dispatchNow(new CreatePatientInformationDocument($patient, $request->all()));

        return response()->json([
            'success' => true
        ], 201);
    }

}
