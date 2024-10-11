<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Class PatientVisitDTO
 * @package App\DataTransferObjects
 */
class PatientVisitDTO extends DataTransferObject
{
    /**
     * The visit ID.
     *
     * @var int|null
     */
    public $visit_id;

    /**
     * The provider ID.
     *
     * @var int|null
     */
    public $provider_id;

    /**
     * Is telehealth.
     *
     * @var int|null
     */
    public $is_telehealth;

    /**
     * The copay amount.
     *
     * @var float|null
     */
    public $copay;

    /**
     * The patient ID.
     *
     * @var int|null
     */
    public $patient_id;

    /**
     * Indicates whether the payment is in cash (1 for true, 0 for false).
     *
     * @var int|null
     */
    public $is_cash;

    /**
     * Create a new PaymentDataDTO instance and validate the input data.
     *
     * @param array $data The input data for the payment DTO.
     */
    public function __construct(array $data)
    {
        // Define validation rules for each property
        $validator = Validator::make($data, [
            'visit_id' => ['nullable','integer'],
            'provider_id' => ['nullable','integer'],
            'is_telehealth' => ['nullable','boolean'],
            'copay' => ['nullable','numeric',],
            'patient_id' => ['nullable','integer'],
            'is_cash' => ['nullable','boolean']
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
