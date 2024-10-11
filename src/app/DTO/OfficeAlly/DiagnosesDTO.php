<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Data Transfer Object (DTO) for Diagnose data with validation.
 */
class DiagnosesDTO extends DataTransferObject
{
    /**
     * The code for the Diagnose.
     *
     * @var string
     */
    public $code;

    /**
     * The description of the Diagnose.
     *
     * @var string
     */
    public $description;

    /**
     * Indicates if the Diagnose is related to HCC.
     *
     * @var int
     */
    public $hcc;

    /**
     * Indicates if the Diagnose is billable.
     *
     * @var int
     */
    public $is_billable;

    /**
     * The termination date of the Diagnose (nullable).
     *
     * @var string|null
     */
    public $terminated_at;

    /**
     * Create a new DiagnoseDTO instance and validate the input data.
     *
     * @param array $data The input data for the Diagnose.
     *
     * @throws \InvalidArgumentException if the input data is invalid.
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'code' => ['nullable', 'string'],
            'description' => ['required', 'string'],
            'hcc' => ['required', 'integer'],
            'is_billable' => ['required', 'integer'],
            'terminated_at' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
