<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Illuminate\Support\Carbon;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Data Transfer Object (DTO) for PatientAlert data used in the updateOrCreate method.
 */
class AppliedPaymentDTO extends DataTransferObject
{
    /**
     * The external ID.
     *
     * @var string
     */
    public $external_id;

    /**
     * The applied transaction type ID.
     *
     * @var int
     */
    public $applied_transaction_type_id;

    /**
     * The patient visit ID.
     *
     * @var int
     */
    public $patient_visit_id;

    /**
     * The applied amount.
     *
     * @var float
     */
    public $applied_amount;

    /**
     * The applied date.
     *
     * @var \Carbon\Carbon
     */
    public $applied_date;

    /**
     * The transaction date (nullable).
     *
     *  @var \Carbon\Carbon|null
     */
    public $transaction_date;

    /**
     * Create a new PaymentDataDTO instance and validate the input data.
     *
     * @param array $data The input data for the payment DTO.
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'external_id' => ['required', 'string', 'max:255'],
            'applied_transaction_type_id' => ['required', 'integer'],
            'patient_visit_id' => ['required', 'integer'],
            'applied_amount' => ['required', 'numeric'],
            'applied_date' => ['required', 'date'],
            'transaction_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
