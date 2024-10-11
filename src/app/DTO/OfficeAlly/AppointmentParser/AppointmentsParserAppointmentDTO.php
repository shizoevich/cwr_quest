<?php

namespace App\DTO\OfficeAlly\AppointmentParser;

use App\Helpers\ExceptionNotificator;
use App\Notifications\AnErrorOccurred;
use Illuminate\Support\Facades\Validator;
use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class AppointmentDTO
 *
 * Data Transfer Object for Appointment Data.
 *
 * @package App\DTO\OfficeAlly\AppointmentParser
 */
class AppointmentsParserAppointmentDTO extends DataTransferObject
{
    /**
     * @var int|null The appointment time.
     */
    public $time;

    /**
     * @var int The appointment ID.
     */
    public $idAppointments;

    /**
     * @var string|null The resource name.
     */
    public $resource;

    /**
     * @var float|null The visit copay.
     */
    public $visit_copay;

    /**
     * @var int|null The visit length.
     */
    public $visit_length;

    /**
     * @var string|null The appointment notes.
     */
    public $notes;

    /**
     * @var string|null The reason for the visit.
     */
    public $reason_for_visit;

    /**
     * @var string|null The scheduled by information.
     */
    public $sheldued_by;

    /**
     * @var string|null The date when the appointment was created.
     */
    public $date_created;

    /**
     * @var string|null The check-in information.
     */
    public $check_in;

    /**
     * @var int The count of appointments not found.
     */
    public $not_found_count = 0;

    /**
     * @var int|null The timestamp when the data was parsed.
     */
    public $parsed_at;

    /**
     * Create a new AppointmentDTO instance and validate the input data.
     *
     * @param array $data The input data for the appointment.
     *
     * @throws \InvalidArgumentException if the input data is invalid.
     */
    public function __construct(array $data)
    {
        $validator = Validator::make($data, [
            'time' => 'nullable|integer',
            'idAppointments' => 'required|integer',
            'resource' => 'nullable|string|max:45',
            'visit_copay' => 'nullable|numeric|min:0',
            'visit_length' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:255',
            'reason_for_visit' => 'nullable|string|max:255',
            'sheldued_by' => 'nullable|string|max:45',
            'date_created' => 'nullable|string|max:45',
            'check_in' => 'nullable|string|max:45',
        ]);

        if ($validator->fails()) {
            $errorMessage = 'Invalid DTO data: ' . implode(', ', $validator->errors()->all());
            with(new ExceptionNotificator())
                ->officeAllyNotifyAndSendToSentry(new AnErrorOccurred($errorMessage), ['office_ally' => 'emergency']);
        }

        parent::__construct($data);
    }
}
