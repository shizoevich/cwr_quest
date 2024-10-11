<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Data Transfer Object (DTO) for PatientAlert data used in the updateOrCreate method.
 */
class PatientAlertDTO extends DataTransferObject
{
    /**
     * The OfficeAlly alert ID.
     *
     * @var int
     */
    public $officeally_alert_id;

    /**
     * The ID of the associated patient.
     *
     * @var int
     */
    public $patient_id;

    /**
     * The date the alert was created.
     *
     * @var string
     */
    public $date_created;

    /**
     * The alert message.
     *
     * @var string
     */
    public $message;

    /**
     * The resolved_by field.
     *
     * @var string|null
     */
    public $resolved_by;

    /**
     * The status of the alert.
     *
     * @var string|null
     */
    public $status;

    /**
     * The date the alert was resolved (nullable).
     *
     * @var string|null
     */
    public $date_resolved;

     /**
     * Create a new PatientAlertDTO instance and validate the input data.
     *
     * @param array $data The input data for the patient alert.
     *
     * @throws \InvalidArgumentException if the input data is invalid.
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'officeally_alert_id' => ['required', 'integer'],
            'patient_id' => ['required', 'integer'],
            'date_created' => ['nullable', 'date'],
            'message' => ['nullable', 'string', 'max:255'],
            'resolved_by' => ['nullable',  'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'date_resolved' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
