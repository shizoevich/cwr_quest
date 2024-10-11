<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Data Transfer Object (DTO) for Diagnose data with validation.
 */
class InsurancesDTO extends DataTransferObject
{
    /**
     * The external ID of the patient insurance.
     *
     * @var string|null
     */
    public $external_id;

    /**
     * The name of the insurance.
     *
     * @var string
     */
    public $insurance;

    /**
     * The address line 1 of the insurance.
     *
     * @var string|null
     */
    public $address_line_1;

    /**
     * The city of the insurance.
     *
     * @var string|null
     */
    public $city;

    /**
     * The state of the insurance.
     *
     * @var string|null
     */
    public $state;

    /**
     * The zip code of the insurance.
     *
     * @var string|null
     */
    public $zip;

       /**
     * Constructor for the DTO.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'external_id' => ['nullable', 'string', 'max:32'],
            'insurance' => ['required', 'string', 'max:191'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:64'],
            'state' => ['nullable', 'string', 'max:16'],
            'zip' => ['nullable', 'string', 'max:16'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
