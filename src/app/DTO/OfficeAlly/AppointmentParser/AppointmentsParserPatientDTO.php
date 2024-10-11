<?php

namespace App\DTO\OfficeAlly\AppointmentParser;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Illuminate\Support\Facades\Validator;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class Appointment
 * @package App\DTO\OfficeAlly
 */
class AppointmentsParserPatientDTO extends DataTransferObject
{
    /**
     * The patient ID.
     *
     * @var int
     */
    public $patient_id;

    /**
     * The patient account number.
     *
     * @var string|null
     */
    public $patient_account_number;

    /**
     * The first name of the patient.
     *
     * @var string
     */
    public $first_name;

    /**
     * The last name of the patient.
     *
     * @var string
     */
    public $last_name;

    /**
     * The middle initial of the patient.
     *
     * @var string|null
     */
    public $middle_initial;

    /**
     * The name of the insured person.
     *
     * @var string
     */
    public $insured_name;

    /**
     * The name of the secondary insured person.
     *
     * @var string
     */
    public $secondary_insured_name;

    /**
     * The patient's address.
     *
     * @var string
     */
    public $address;

    /**
     * The patient's cell phone number.
     *
     * @var string|null
     */
    public $cell_phone;

    /**
     * The patient's home phone number.
     *
     * @var string|null
     */
    public $home_phone;

    /**
     * The patient's work phone number.
     *
     * @var string|null
     */
    public $work_phone;

    /**
     * The number of visits authorized.
     *
     * @var int|null
     */
    public $visits_auth;

    /**
     * The number of visits authorized left.
     *
     * @var int|null
     */
    public $visits_auth_left;

    /**
     * The name of the primary insurance.
     *
     * @var string|null
     */
    public $primary_insurance;

    /**
     * The name of the secondary insurance.
     *
     * @var string|null
     */
    public $secondary_insurance;

    /**
     * The patient's sex.
     *
     * @var string|null
     */
    public $sex;

    /**
     * The eligibility copay status.
     *
     * @var string|null
     */
    public $elig_copay;

    /**
     * The eligibility status.
     *
     * @var string
     */
    public $elig_status;

    /**
     * The referring provider's name.
     *
     * @var string|null
     */
    public $reffering_provider;

    /**
     * The copay for each visit.
     *
     * @var float
     */
    public $visit_copay;

    /**
     * Create a new PatientDTO instance and validate the input data.
     *
     * @param array $data The input data for the patient.
     *
     * @throws \InvalidArgumentException if the input data is invalid.
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'patient_id' => ['nullable', 'integer'],
            'patient_account_number' => ['nullable', 'string','max:45'],
            'first_name' => ['nullable', 'string','max:45'],
            'last_name' => ['nullable', 'string','max:45'],
            'middle_initial' => ['nullable', 'string','max:45'],
            'insured_name' => ['nullable', 'string','max:45'],
            'secondary_insured_name' => ['nullable', 'string','max:45'],
            'address' => ['nullable', 'string','max:255'],
            'cell_phone' => ['nullable', 'string','max:45'],
            'home_phone' => ['nullable', 'string','max:45'],
            'work_phone' => ['nullable', 'string','max:45'],
            'visits_auth' => ['nullable', 'integer'],
            'visits_auth_left' => ['nullable', 'integer'],
            'primary_insurance' => ['nullable', 'string','max:255'],
            'secondary_insurance' => ['nullable', 'string','max:255'],
            'sex' => ['nullable', 'string','max:6'],
            'elig_copay' => ['nullable', 'string','max:45'],
            'elig_status' => ['nullable', 'string','max:45'],
            'reffering_provider' => ['nullable', 'string','max:45'],
            'visit_copay' => ['nullable', 'numeric'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
