<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Class PatientDTO
 * @package App\DataTransferObjects
 */
class PatientDTO extends DataTransferObject
{
    /**
     * The patient's ID.
     *
     * @var int
     */
    public $patient_id;
    
    /**
     * The last name of the patient.
     *
     * @var string
     */
    public $last_name;
    
    /**
     * The first name of the patient.
     *
     * @var string
     */
    public $first_name;
    
    /**
     * The middle initial of the patient, if available.
     *
     * @var string|null
     */
    public $middle_initial;
    
    /**
     * The date of birth of the patient in YYYY-MM-DD format, if available.
     *
     * @var string|null
     */
    public $date_of_birth;

    /**
     * Create a new PatientDTO instance and validate the input data.
     *
     * @param array $data The input data for the patient DTO.
     */
    public function __construct(array $data)
    {
        // Define validation rules for each property
        $validator = Validator::make($data, [
            'patient_id' => ['required', 'integer'],
            'last_name' => ['required', 'max:255'],
            'first_name' => ['required', 'max:255'],
            'middle_initial' => ['nullable', 'max:45'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
