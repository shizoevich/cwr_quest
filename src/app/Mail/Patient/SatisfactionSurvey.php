<?php

namespace App\Mail\Patient;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SatisfactionSurvey extends Mailable
{
    use Queueable, SerializesModels;

    /** @var string  */
    public $subject = 'Help us become better';

    /** @var string  */
    private $therapistName;

    /** @var string  */
    private $patientName;

    /** @var int  */
    private $appointmentDate;

    /** @var int  */
    private $appointmentId;

    public function __construct(string $therapistName, string $patientName, int $appointmentDate, int $appointmentId)
    {
        $this->therapistName = $therapistName;
        $this->patientName = $patientName;
        $this->appointmentDate = Carbon::createFromTimestamp($appointmentDate)->format('m/d/Y');
        $this->appointmentId = $appointmentId;
    }

    public function build(): SatisfactionSurvey
    {
        $surveyLink = sprintf(
            config('satisfaction_survey.survey_link'),
            urlencode($this->therapistName),
            urlencode($this->appointmentDate),
            urlencode($this->appointmentId)
        );

        return $this->markdown(
            'emails.patients.feedback_survey',
            [
                'surveyLink' => $surveyLink,
                'patientName' => $this->patientName,
            ]
        );
    }
}
