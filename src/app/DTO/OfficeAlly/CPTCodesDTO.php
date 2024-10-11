<?php

namespace App\DTO\OfficeAlly;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Spatie\DataTransferObject\DataTransferObject;
use Illuminate\Support\Facades\Validator;

/**
 * Data Transfer Object (DTO) for CPT Codes  data with validation.
 */
class CPTCodesDTO extends DataTransferObject
{
    /**
     * The code of patient insurances procedures.
     *
     * @var string|null
     */
    public $code;
    /**
     * The name of patient insurances procedures. 
     *
     * @var string|null
     */
    public $name;
    /**
     * The patient pos insurances procedures. 
     *
     * @var string|null
     */
    public $pos;
    /**
     * The patient modifier_a insurances procedures. 
     *
     * @var string|null
     */
    public $modifier_a;
    /**
     * The patient modifier_b insurances procedures. 
     *
     * @var string|null
     */
    public $modifier_b;
    /**
     * The patient modifier_c insurances procedures. 
     *
     * @var string|null
     */
    public $modifier_c;
    /**
     * The patient modifier_d insurances procedures. 
     *
     * @var string|null
     */
    public $modifier_d;
    /**
     * The termination date of the Diagnose (nullable).
     *
     * @var float|null
     */
    public $charge;

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
            'code' => ['nullable', 'string', 'max:255'],
            'pos' => ['nullable', 'string', 'max:255'],
            'modifier_a' => ['nullable', 'string', 'max:255'],
            'modifier_b' => ['nullable', 'string', 'max:255'],
            'modifier_c' => ['nullable', 'string', 'max:255'],
            'modifier_d' => ['nullable', 'string', 'max:255'],
            'charge' => ['nullable', 'float'],
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
