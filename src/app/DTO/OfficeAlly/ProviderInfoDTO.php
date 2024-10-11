<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Class ProviderInfoDTO
 * @package App\DataTransferObjects
 */
class ProviderInfoDTO extends DataTransferObject
{
    /**
     * The first name.
     *
     * @var string|null
     */
    public $first_name;

    /**
     * The last name.
     *
     * @var string|null
     */
    public $last_name;

    /**
     * The middle initial.
     *
     * @var string|null
     */
    public $middle_initial;

    /**
     * The license number.
     *
     * @var string|null
     */
    public $license_no;

    /**
     * The individual NPI.
     *
     * @var string|null
     */
    public $individual_npi;

    /**
     * The taxonomy code.
     *
     * @var string|null
     */
    public $taxonomy_code;

    /**
     * Create a new InfoDTO instance and validate the input data.
     *
     * @param array $data The input data for the InfoDTO.
     *
     * @throws \InvalidArgumentException if the input data is invalid.
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string', 'max:45'],
            'license_no' => ['nullable', 'string', 'max:255'],
            'individual_npi' => ['nullable', 'string', 'max:255'],
            'taxonomy_code' => ['nullable', 'string', 'max:45'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
