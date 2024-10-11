<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientInformationForm
 *
 * @property int $id
 * @property int $patient_id
 * @property string $name
 * @property string $date_of_birth
 * @property string $home_address
 * @property string $city
 * @property string $state
 * @property string $zip
 * @property string $email
 * @property int|null $email_sent
 * @property string|null $home_phone
 * @property string|null $mobile_phone
 * @property string|null $work_phone
 * @property string|null $emergency_contact
 * @property string|null $emergency_contact_phone
 * @property string|null $emergency_contact_relationship
 * @property string|null $co_pay
 * @property string|null $self_pay
 * @property string|null $charge_for_cancellation
 * @property string|null $other_charges
 * @property string|null $other_charges_price
 * @property string|null $introduction
 * @property string|null $health_insurance
 * @property string|null $request_payment_of_authorized
 * @property string|null $hear_about_us_other_specify
 * @property string|null $referred_by_other_insurance_specify
 * @property string|null $payment_for_session_not_converted
 * @property string|null $guardian_name
 * @property string|null $relationship
 * @property int|null $allow_home_phone_call
 * @property int|null $allow_mobile_phone_call
 * @property int|null $allow_mobile_send_messages
 * @property int|null $allow_work_phone_call
 * @property int|null $access_credit_card
 * @property int|null $notify_agree
 * @property int|null $receive_electronic_v_of_pp
 * @property int|null $receive_paper_v_of_pp
 * @property int|null $unencrypted_communications
 * @property int|null $allow_request_payment_of_authorized
 * @property int|null $yelp
 * @property int|null $google
 * @property int|null $yellow_pages
 * @property int|null $event_i_attended
 * @property int|null $hear_about_us_other
 * @property int|null $friend_or_relative
 * @property int|null $another_professional
 * @property int|null $kaiser
 * @property int|null $referred_by_other_insurance
 * @property int|null $store_credit_card
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Patient $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereAccessCreditCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereAllowHomePhoneCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereAllowMobilePhoneCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereAllowMobileSendMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereAllowRequestPaymentOfAuthorized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereAllowWorkPhoneCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereAnotherProfessional($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereChargeForCancellation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereCoPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereEmailSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereEmergencyContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereEmergencyContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereEmergencyContactRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereEventIAttended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereFriendOrRelative($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereGoogle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereGuardianName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereHealthInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereHearAboutUsOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereHearAboutUsOtherSpecify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereHomeAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereIntroduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereKaiser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereMobilePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereNotifyAgree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereOtherCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereOtherChargesPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm wherePaymentForSessionNotConverted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereReceiveElectronicVOfPp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereReceivePaperVOfPp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereReferredByOtherInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereReferredByOtherInsuranceSpecify($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereRequestPaymentOfAuthorized($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereSelfPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereStoreCreditCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereUnencryptedCommunications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereYellowPages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereYelp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientInformationForm whereZip($value)
 * @mixin \Eloquent
 */
class PatientInformationForm extends Model
{
//    protected $attributes = [
//        'patient_id',
//        'name',
//        'date_of_birth',
//        'home_address',
//        'city',
//        'state',
//        'zip',
//        'email',
//        'email_sent',
//        'home_phone',
//        'mobile_phone',
//        'work_phone',
//        'emergency_contact',
//        'emergency_contact_phone',
//        'emergency_contact_relationship',
//        'co_pay',
//        'self_pay',
//        'charge_for_cancellation',
//        'other_charges',
//        'other_charges_price',
//        'introduction',
//        'health_insurance',
//        'request_payment_of_authorized',
//        'hear_about_us_other_specify',
//        'referred_by_other_insurance_specify',
//        'payment_for_session_not_converted',
//        'guardian_name',
//        'relationship',
//        'allow_home_phone_call',
//        'allow_mobile_phone_call',
//        'allow_mobile_send_messages',
//        'allow_work_phone_call',
//        'access_credit_card',
//        'notify_agree',
//        'receive_electronic_v_of_pp',
//        'receive_paper_v_of_pp',
//        'unencrypted_communications',
//        'allow_request_payment_of_authorized',
//        'yelp',
//        'google',
//        'yellow_pages',
//        'event_i_attended',
//        'hear_about_us_other',
//        'friend_or_relative',
//        'another_professional',
//        'kaiser',
//        'referred_by_other_insurance',
//        'store_credit_card',
//    ];

    protected $fillable = [
        'patient_id',
        'name',
        'date_of_birth',
        'home_address',
        'city',
        'state',
        'zip',
        'email',
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
        'unencrypted_communications',
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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

}
