<?php


namespace App\Components\PatientForm\NewPatient;

use App\Patient;
use Carbon\Carbon;

class CreateInformationForm
{

    protected $informationFormFields = [
        'patient_id',
        'name',
        'date_of_birth',
        'home_address',
        'city',
        'state',
        'zip',
        'email',
        'allow_mailing',
        'email_sent',
        'home_phone',
        'mobile_phone',
        'work_phone',
        'emergency_contact',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'co_pay',
        'self_pay',
        'charge_for_cancellation',
        'other_charges',
        'other_charges_price',
        'introduction',
        'health_insurance',
        'request_payment_of_authorized',
        'hear_about_us_other_specify',
        'referred_by_other_insurance_specify',
        'payment_for_session_not_converted',
        'guardian_name',
        'relationship',
        'allow_home_phone_call',
        'allow_mobile_phone_call',
        'allow_mobile_send_messages',
        'allow_work_phone_call',
        'access_credit_card',
        'notify_agree',
        'receive_electronic_v_of_pp',
        'receive_paper_v_of_pp',
        'allow_request_payment_of_authorized',
        'yelp',
        'google',
        'yellow_pages',
        'event_i_attended',
        'hear_about_us_other',
        'friend_or_relative',
        'another_professional',
        'kaiser',
        'referred_by_other_insurance',
        'store_credit_card',
    ];

    public function create(Patient $patient, array $dataFromRequest)
    {
        return $this->prepareInformationFormData($dataFromRequest);
    }
    
    /**
     * Transfered from old old class
     * @param $request
     *
     * @return array
     */
    protected function prepareInformationFormData($request)
    {
        $radioButtons = [
            "allow_mailing",
            "allow_home_phone_call",
            "allow_mobile_phone_call",
            "allow_mobile_send_messages",
            "allow_work_phone_call",
        ];
        
        $fields = $this->informationFormFields;
        
        $result = array_filter($request, function($value, $key) use ($fields){
            return in_array($key, $fields);
        }, ARRAY_FILTER_USE_BOTH);
        
        
        if(isset($result['date_of_birth'])){
            $result['date_of_birth'] = Carbon::parse($result['date_of_birth']);
        }
        
        foreach ($result as $key => $value){
            if(in_array($key, $radioButtons)){
                $result[$key] = $value === 'Yes' ? true : false;
            }
        }
        
        return $result;
    }
}