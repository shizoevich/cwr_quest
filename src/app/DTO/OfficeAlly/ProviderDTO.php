<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Class ProviderDTO
 * @package App\DataTransferObjects
 */
class ProviderDTO extends DataTransferObject
{
      /**
     * @var int|null
     */
    public $officeally_id;

    /**
     * @var string|null
     */
    public $provider_name;

    /**
     * @var string|null
     */
    public $phone;

     /**
     * Create a new ProviderDTO instance and validate the input data.
     *
     * @param array $data The input data for the patient alert.
     *
     * @throws \InvalidArgumentException if the input data is invalid.
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'officeally_id' => ['nullable', 'integer'],
            'provider_name' => ['nullable', 'string', 'max:45'],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }

}
