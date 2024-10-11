<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Class OfficeRoomDTO
 * @package App\DTO\OfficeAlly
 */
class OfficeRoomDTO extends DataTransferObject
{
    /**
     * The external ID of the office room.
     *
     * @var string
     */
    public $external_id;

    /**
     * The ID of the associated office.
     *
     * @var int
     */
    public $office_id;

    /**
     * The name of the office room.
     *
     * @var string
     */
    public $name;

    /**
     * Constructor for the DTO.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'external_id' => ['required', 'string', 'max:16'],
            'office_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:45'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
