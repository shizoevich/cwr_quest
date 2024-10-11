<?php

namespace App\Helpers\Sites\OfficeAlly\Traits;

use App\DTO\OfficeAlly\Appointment;
use App\Exceptions\Officeally\Appointment\AppointmentNotDeletedException;
use App\Exceptions\Officeally\Appointment\AppointmentNotFoundException;
use App\Exceptions\Officeally\Appointment\AppointmentNotUpdatedException;
use App\Exceptions\Officeally\Appointment\PaymentNotAddedException;
use App\Helpers\Sites\OfficeAlly\Enums\AppointmentStatuses;
use App\Models\Officeally\OfficeallyLog;
use Carbon\Carbon;
use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Trait Appointments
 * @package App\Helpers\Sites\OfficeAlly\Traits
 */
trait Appointments
{
    /**
     * @param Carbon $date
     * @param        $officeId
     *
     * @return mixed
     */
    public function getAppointments(Carbon $date, $officeId)
    {
        $response = $this->officeAlly->get("Appointments/ViewAppointments.aspx?Tab=A&View=d&Day={$date->format('j')}&Month={$date->format('n')}&Year={$date->format('Y')}&ProviderID=&OfficeID={$officeId}&StatusID=&TimeInterval=5&DailyMode=5",
            [], true);
        
        return $response->getBody()->getContents();
    }
    
    /**
     * @param int $patientId
     *
     * @return mixed
     */
    public function getPatientAppointments(int $patientId)
    {
        $response = $this->officeAlly->get("ManagePatients/Patient_Appointments.aspx?PID={$patientId}&From=EditPatient&FromApp=PM",
            [], true);
        
        return $response->getBody()->getContents();
    }
    
    /**
     * @param $appointmentId
     *
     * @return array|null
     */
    public function getAppointmentById($appointmentId)
    {
        $response = $this->officeAlly->post("CommonUserControls/Appointments/Api.aspx?oper=GetAppointmentData", [
            'headers' => [
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
            ],
            'json'    => [
                'appointmentID'              => $appointmentId,
                '__RequestVerificationToken' => $this->getRequestVerificationToken(),
            ]
        ], true);
        $appointment = json_decode($response->getBody()->getContents(), true);
        $appointment = data_get($appointment, 'dt.Appointment.dt');
        
        return $appointment;
    }
    
    /**
     * @param Appointment $appointmentDto
     *
     * @throws AppointmentNotFoundException
     * @throws AppointmentNotUpdatedException
     */
    public function editAppointment(Appointment $appointmentDto)
    {
        if (!$this->isProduction()) {
            return;
        }
        $appointment = $this->getAppointmentById($appointmentDto->id);
        if (!$appointment) {
            $message = "Appointment {$appointmentDto->id} is not parsed and cannot be modified.";
            $this->officeAlly->notifyIfFailed($message);
            throw new AppointmentNotFoundException($appointmentDto->id);
        }
        
        $payload = $this->buildPayloadForUpdateAppointment($appointment, $appointmentDto);
        
        $this->officeAlly->post("CommonUserControls/Appointments/Api.aspx?oper=SaveAppointment", [
            'headers' => [
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
            ],
            'json' => array_merge($payload, ['__RequestVerificationToken' => $this->getRequestVerificationToken()]),
        ], true);
        
        // check if appointment not updated
        $appointment = $this->getAppointmentById($appointmentDto->id);
        $updatedPayload = $this->buildPayloadForUpdateAppointment($appointment, null);
        $payloadForLog = [
            'new' => $payload,
            'actual' => $updatedPayload,
        ];
        unset(
            $payload['eligibilityInsuranceID'],
            $payload['eligibilityPayerID'],
            $payload['userDefined1'],
            $payload['userDefined2'],
            $payload['userDefined3'],
            $payload['userDefined4'],
            $payload['userDefined5'],
            $payload['userDefined6'],
            $payload['notes'],
            $updatedPayload['eligibilityInsuranceID'],
            $updatedPayload['eligibilityPayerID'],
            $updatedPayload['userDefined1'],
            $updatedPayload['userDefined2'],
            $updatedPayload['userDefined3'],
            $updatedPayload['userDefined4'],
            $updatedPayload['userDefined5'],
            $updatedPayload['userDefined6'],
            $updatedPayload['notes'],
        );

        if ($payload !== $updatedPayload) {
            $exception = new AppointmentNotUpdatedException($payload, $updatedPayload, "Appointment {$appointmentDto->id} is not updated.");
            $this->log(OfficeallyLog::ACTION_UPDATE_APPOINTMENT, false, $payloadForLog, $exception->getOriginalMessage());
            throw $exception;
        }

        $this->log(OfficeallyLog::ACTION_UPDATE_APPOINTMENT, true, $payloadForLog);
    }
    
    /**
     * @param Appointment $appointmentDto
     *
     * @return mixed|null
     */
    public function createAppointment(Appointment $appointmentDto)
    {
        if (!$this->isProduction()) {
            return rand(1, 2000000);
        }
        
        $patientPage = $this->getPatientAppointments($appointmentDto->patientId);
        $crawler = new Crawler($patientPage);
        $hasAppointment = false;
        $crawler->filter('table#grdAppointment1 tr.row, table#grdAppointment1 tr.row-alt')->each(function ($node) use (
            $appointmentDto,
            &$hasAppointment
        ) {
            $date = trim($node->children()->eq(0)->text());
            $result = [];
            preg_match('/(?<date>\d{1,2}\/\d{1,2}\/\d{4})/', $date, $result);
            $status = trim(remove_nbsp($node->children()->eq(6)->text()));
            if ($appointmentDto->date->copy()->startOfDay()->eq(Carbon::parse($result['date'])->startOfDay()) && $status === 'Active') {
                $hasAppointment = true;
            }
        });
        $payload = [
            'appointmentDate'        => $appointmentDto->date->format('n/j/Y'), //e.g. 7/23/2020
            'appointmentID'          => 0,
            'checkEligibility'       => false,
            'colorCode'              => '#ffffff',
            'day'                    => $appointmentDto->date->format('j'), //e.g. 7
            'daysBetweenVisits'      => $appointmentDto->recurrence->daysBetweenVisits ?? 7,
            'eligibilityInsuranceID' => 0,    //?
            'eligibilityPayerID'     => '0',    //?
            'month'                  => $appointmentDto->date->format('n'),   //e.g. 8
            'notes'                  => $appointmentDto->notes ?? '',
            'officeID'               => $appointmentDto->officeId,
            'oldStaffId'             => 0,
            'oldVisitType'           => -1,
            'paAppointment'          => [    //?
                                             'isPaAppointment' => false,
                                             'isVirtualVisit'  => false,
                                             'paAppointmentID' => 0,
                                             'panote'          => '',
                                             'providerID'      => 0,
                                             'status'          => '',
                                             'statusid'        => 0,
            ],
            'patientID'              => $appointmentDto->patientId,
            'reasonForVisit'         => $appointmentDto->reasonForVisit,
            'reminderCallID'         => 0,  //?
            'reminderCallIDOriginal' => 0,  //?
            'reminderChanges'        => null,  //?
            'repeat'                 => $appointmentDto->recurrence->repeat ?? 0,
            'resourceID'             => $appointmentDto->resource->id,
            'resourceType'           => $appointmentDto->resource->type,
            
            'staffID'   => $appointmentDto->providerId,
            
            /**
             * 0 - Provider
             */
            'staffType' => 0,
            
            /**
             * @see AppointmentStatuses
             */
            'statusID'  => $appointmentDto->statusId,
            
            'time'                  => $appointmentDto->date->format('H:i'),  //e.g. 20:30
            'timeInterval'          => 30,   //static
            'userDefined1'          => "",
            'userDefined2'          => "",
            'userDefined3'          => "",
            'userDefined4'          => "",
            'userDefined5'          => "",
            'userDefined6'          => "",
            'visitLength'           => $appointmentDto->visitLength,
            'wasEligibilityChecked' => false,   //static
            'year'                  => $appointmentDto->date->format('Y'),    //e.g. 2020
        ];

        if ($hasAppointment) {
            $this->log(OfficeallyLog::ACTION_CREATE_APPOINTMENT, false, $payload, 'Appointment already exists in OA for patient "' . $appointmentDto->patientId . '" on date "' . $appointmentDto->date->toDateTimeString() .'"');
            return null;
        }
        
        $response = $this->officeAlly->post("CommonUserControls/Appointments/Api.aspx?oper=SaveAppointment", [
            'headers' => [
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
            ],
            'json'    => array_merge($payload, ['__RequestVerificationToken' => $this->getRequestVerificationToken()]),
        ], true);
        $response = json_decode($response->getBody()->getContents(), true);
        if (data_get($response, 'Status') && data_get($response, 'dt.appointmentID')) {
            $this->log(OfficeallyLog::ACTION_CREATE_APPOINTMENT, true,
                ['payload' => $payload, 'oa_appointment_id' => $response['dt']['appointmentID']]);
            
            return $response['dt']['appointmentID'];
        }
        $this->log(OfficeallyLog::ACTION_CREATE_APPOINTMENT, false, $payload);
        
        return null;
    }
    
    /**
     * @param float  $amount
     * @param int    $appointmentId
     * @param int    $patientId
     * @param int    $providerId
     * @param int    $officeId
     * @param string $paymentMethod
     * @param string $description
     * @param string $checkNo
     *
     * @throws PaymentNotAddedException
     */
    public function addPaymentToAppointment(
        float $amount,
        int $appointmentId,
        int $patientId,
        int $providerId,
        int $officeId,
        string $paymentMethod,
        string $description = 'Copay',
        string $checkNo = ''
    ) {
        if (!$this->isProduction()) {
            return;
        }
        $payload = [
            'id'                         => $appointmentId,
            'pid'                        => $patientId,
            'm'                          => $paymentMethod, //payment method: 1 - Cash; 2 - Check; 3 - Credit Card
            'cn'                         => $checkNo,  //check no
            'd'                          => $description,  //description
            'Amount'                     => $amount,
            'officeid'                   => $officeId,
            'providerid'                 => $providerId,
            '__RequestVerificationToken' => $this->getRequestVerificationToken(),
        ];
        $payloadForLog = array_merge($payload, ['meta' => ['appointment_id' => $appointmentId]]);
        $existingPayments = $this->getAppointmentPayments($appointmentId);
        foreach ($existingPayments as $payment) {
            if ($payment['cell'][6] == $amount) {
                //payment already exists
                $this->log(OfficeallyLog::ACTION_ADD_PAYMENT_TO_APPOINTMENT, true, $payloadForLog,
                    'Payment already exists');
                
                return $payment;
            }
        }
        
        $this->officeAlly->post("CommonUserControls/Appointments/CheckInApi.aspx?oper=AddPayment", [
            'json' => $payload,
        ], true);
        
        //check payment added
        $existingPayments = $this->getAppointmentPayments($appointmentId);
        $officeAllyPayment = null;
        foreach ($existingPayments as $payment) {
            if ($payment['cell'][6] == $amount) {
                $officeAllyPayment = $payment;
                break;
            }
        }
        if (!$officeAllyPayment) {
            $exception = new PaymentNotAddedException($appointmentId);
            $this->log(OfficeallyLog::ACTION_ADD_PAYMENT_TO_APPOINTMENT, false, $payloadForLog,
                $exception->getOriginalMessage());
            throw $exception;
        }
        $this->log(OfficeallyLog::ACTION_ADD_PAYMENT_TO_APPOINTMENT, true, $payloadForLog);

        return $officeAllyPayment;
    }
    
    /**
     * @param int $appointmentId
     */
    public function deleteAppointment(int $appointmentId)
    {
        if (!$this->isProduction()) {
            return;
        }
        $page = $this->officeAlly->get('Appointments/ViewAppointments.aspx')->getBody()->getContents();
        $crawler = new Crawler($page);
        $payload = [];
        $crawler->filter('form#aspnetForm input, form#aspnetForm textarea')->each(function($node) use (&$payload) {
            $inputType = $node->attr('type');
            $inputName = $node->attr('name');
            if($inputType !== 'button' && $inputType !== 'submit' && !empty($inputName)) {
                $payload[$inputName] = $this->prepareValue($node->attr('value'));
            }
        });
        $crawler->filter('form#aspnetForm select')->each(function($node) use (&$payload) {
            $inputName = $node->attr('name');
            if(empty($inputName)) {
                return;
            }
            try {
                $val = $node->filter('option[selected="selected"]')->first()->attr('value');
            } catch(InvalidArgumentException $e) {
                $val = null;
            }
        
            $payload[$inputName] = $this->prepareValue($val);
        });
        $payload['PageAction'] = 'delete';
        $payload['ID'] = $appointmentId;
        
        $page = $this->officeAlly->post("Appointments/ViewAppointments.aspx",
            [
                'form_params' => $payload
            ], true)->getBody()->getContents();
        $crawler = new Crawler($page);
        $statusText = $crawler->filter('#ctl00_phFolderContent_Appointments_lblStatus')->first()->text();
        if($statusText === 'Deleted appointment successfully.') {
            $this->log(OfficeallyLog::ACTION_DELETE_APPOINTMENT, true, ['appointment_id' => $appointmentId]);
        } else {
            $this->officeAlly->notifyIfFailed("Appointment {$appointmentId} is not deleted.");
            $this->log(OfficeallyLog::ACTION_DELETE_APPOINTMENT, false, ['appointment_id' => $appointmentId]);
            throw new AppointmentNotDeletedException($appointmentId);
        }
    }
    
    /**
     * @param int      $patientId  OfficeAlly patient id
     * @param int|null $providerId OfficeAlly provider id
     */
    public function deleteUpcomingAppointments(int $patientId, int $providerId = null)
    {
        if (!$this->isProduction()) {
            return;
        }
        $patientPage = $this->getPatientAppointments($patientId);
        $crawler = new Crawler($patientPage);
        $viewStateGenerator = $crawler->filter('[name="__VIEWSTATEGENERATOR"]')->first()->attr('value');
        $upcomingAppointments = $this->getUpcomingAppointmentIds($crawler);
        $logPayload = [
            'patient_id'                    => $patientId,
            'deleted_upcoming_appointments' => $upcomingAppointments,
        ];
        if (empty($upcomingAppointments)) {
            $this->log(OfficeallyLog::ACTION_DELETE_UPCOMING_APPOINTMENTS, true, $logPayload);
            
            return;
        }
        $appointmentsCount = count($upcomingAppointments);
        $deletedAppointmentsCount = 0;
        foreach ($upcomingAppointments as $appointmentId) {
            if ($providerId) {
                $appointment = $this->getAppointmentById($appointmentId);
                if (data_get($appointment, 'StaffID') != $providerId) {
                    continue;
                }
            }
            $this->officeAlly->post("ManagePatients/Patient_Appointments.aspx?PID={$patientId}&From=EditPatient&FromApp=PM",
                [
                    'headers'     => [
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    ],
                    'form_params' => [
                        '__EVENTTARGET'        => '',
                        '__EVENTARGUMENT'      => '',
                        '__VIEWSTATEGENERATOR' => $viewStateGenerator,
                        'PageAction'           => 'DeleteAppointment',
                        'ID'                   => $appointmentId,
                    ]
                ], true);
            $deletedAppointmentsCount++;
        }
        $upcomingAppointmentsCount = count($upcomingAppointments);
        
        //check if all upcoming appointments deleted
        $patientPage = $this->getPatientAppointments($patientId);
        $crawler = new Crawler($patientPage);
        $upcomingAppointments = $this->getUpcomingAppointmentIds($crawler);
        if (($appointmentsCount - $deletedAppointmentsCount) > count($upcomingAppointments)) {
            $message = sprintf('Deleted %d/%d upcoming appointments for patient %d',
                $upcomingAppointmentsCount - count($upcomingAppointments), $upcomingAppointmentsCount, $patientId);
            $this->officeAlly->notifyIfFailed($message);
            $logPayload['not_deleted_upcoming_appointments'] = $upcomingAppointments;
            $this->log(OfficeallyLog::ACTION_DELETE_UPCOMING_APPOINTMENTS, false, $logPayload, $message);
        } else {
            $this->log(OfficeallyLog::ACTION_DELETE_UPCOMING_APPOINTMENTS, true, $logPayload);
        }
    }
    
    /**
     * @param Crawler $crawler
     *
     * @return array
     */
    private function getUpcomingAppointmentIds(Crawler $crawler)
    {
        $upcomingAppointments = [];
        $crawler->filter('table#grdAppointment1 tr td span')->each(function ($node) use (&$upcomingAppointments) {
            $onClick = $node->attr('onclick');
            if (empty($onClick)) {
                return;
            }
            preg_match('/DeleteAppointment\((?<appointment_id>\d+),/', $onClick, $matches);
            $appointmentId = data_get($matches, 'appointment_id');
            if ($appointmentId) {
                $upcomingAppointments[] = $appointmentId;
            }
        });
        
        return $upcomingAppointments;
    }
    
    /**
     * @param             $appointment
     * @param Appointment|null $newAppointmentDto
     *
     * @return array
     */
    private function buildPayloadForUpdateAppointment($appointment, $newAppointmentDto)
    {
        if(!$newAppointmentDto) {
            $appointmentDate = $appointment['AppointmentDate'];
            $matches = [];
            preg_match('/\/Date\((?<timestamp>\d+)\)\//', $appointmentDate, $matches);
            $appointmentDate = Carbon::createFromTimestamp($matches['timestamp'] / 1000)->setTimeFromTimeString($appointment['AppointmentTime']);
        } else {
            $appointmentDate = $newAppointmentDto->date;
        }

        $notes = $newAppointmentDto->notes ?? $appointment['Notes'];

        return [
            'appointmentDate'        => $appointmentDate->format('n/j/Y'),
            'appointmentID'          => $newAppointmentDto->id ?? $appointment['AppointmentID'],
            'checkEligibility'       => $appointment['CheckEligibility'], //e.g. true / false
            'colorCode'              => $appointment['ColorCode'],
            'day'                    => $appointmentDate->format('j'), //e.g. 7
            'daysBetweenVisits'      => 7,   //static
            'eligibilityInsuranceID' => $appointment['BatchEligibilityInsuranceID'],    //?
            'eligibilityPayerID'     => $appointment['PayerID'],    //?
            'month'                  => $appointmentDate->format('n'),   //e.g. 8
            'notes'                  => substr($notes, 0, 255),   //e.g. this is a test appointment
            'officeID'               => $newAppointmentDto->officeId ?? $appointment['OfficeID'],
            'paAppointment'          => [    //?
                                             'isPaAppointment' => false,
                                             'paAppointmentID' => 0,
                                             'panote'          => '',
                                             'providerID'      => 0,
                                             'status'          => '',
                                             'statusid'        => 0,
            ],
            'patientID'              => $newAppointmentDto->patientId ?? $appointment['PatientID'],
            'reasonForVisit'         => $newAppointmentDto->reasonForVisit ?? $appointment['ReasonForVisit'],
            'reminderCallID'         => 0,  //?
            'reminderCallIDOriginal' => 0,  //?
            'reminderChanges'        => null,  //?
            'repeat'                 => 0,  //static
            
            'resourceID'             => $newAppointmentDto->resource->id ?? $appointment['ResourceID'], //room id
            'resourceType'       => $newAppointmentDto->resource->type ?? $appointment['ResourceType'],
            
            'staffID'                => $newAppointmentDto->providerId ?? $appointment['StaffID'],   //provider id
            
            /**
             * 0 - Provider
             */
            'staffType'          => $appointment['StaffType'],
            
            'statusID'               => $newAppointmentDto->statusId ?? $appointment['StatusID'],
            'time'                   => $appointmentDate->format('H:i'),
            'timeInterval'           => 30,   //static
            'userDefined1'           => $appointment['UserDefined1'], //static
            'userDefined2'           => $appointment['UserDefined2'], //static
            'userDefined3'           => $appointment['UserDefined3'], //static
            'userDefined4'           => $appointment['UserDefined4'], //static
            'userDefined5'           => $appointment['UserDefined5'], //static
            'userDefined6'           => $appointment['UserDefined6'], //static
            'visitLength'            => $newAppointmentDto->visitLength ?? $appointment['VisitLength'],  //e.g. 60
            'wasEligibilityChecked'  => false,   //static
            'year'                   => $appointmentDate->format('Y'),    //e.g. 2020
        ];
    }
}