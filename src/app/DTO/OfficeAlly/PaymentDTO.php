<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
/**
 * Data Transfer Object (DTO) for PatientAlert data used in the updateOrCreate method.
 */
class PaymentDTO extends DataTransferObject
{
    /**
     * The external ID.
     *
     * @var string
     */
    public $external_id;

    /**
     * The patient ID.
     *
     * @var int
     */
    public $patient_id;

    /**
     * The transaction type ID.
     *
     * @var int
     */
    public $transaction_type_id;

    /**
     * The transaction purpose ID.
     *
     * @var int
     */
    public $transaction_purpose_id;

    /**
     * The payment amount (in cents).
     *
     * @var float
     */
    public $payment_amount;

    /**
     * The applied amount (in cents).
     *
     * @var float
     */
    public $applied_amount;

    /**
     * OfficeallyTransaction Date
     * @var \Carbon\Carbon
     */
    public $transaction_date;

    /**
     * Create a new PaymentDataDTO instance and validate the input data.
     *
     * @param array $data The input data for the payment DTO.
     */
    public function __construct(array $data)
    {
        // Define validation rules for each property
        $validator = Validator::make($data, [
            'external_id' => ['required', 'max:255'],
            'patient_id' => ['required', 'integer'],
            'transaction_type_id' => ['required', 'integer'],
            'transaction_purpose_id' => ['required', 'integer'],
            'payment_amount' => ['required', 'numeric'],
            'applied_amount' => ['required', 'numeric'],
            'transaction_date' => ['nullable'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
