<?php

namespace Tests\Helpers\OfficeAlly;

use App\Helpers\Sites\OfficeAlly\OfficeAllyHelper;
use App\Office;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Psy\Exception\BreakException;
use Symfony\Component\DomCrawler\Crawler;

class AppointmentsOfficeAllyHelper
{
    public static function findNotEmptyAppointmentsPage(OfficeAllyHelper $officeAllyHelper): ?string
    {
        $period = self::getPeriod();
        $offices = Office::query()->whereNotNull('external_id')->get();

        foreach ($offices as $office) {
            foreach ($period as $date) {
                $appointments = $officeAllyHelper->getAppointments($date, $office->external_id);

                if (!self::checkAppointmentsHtmlData($appointments)) {
                    continue;
                }

                return $appointments;
            }
        }

        return null;
    }

    public static function getPatientDataFromAppointmentsHtml($appointments)
    {
        $crawler = new Crawler($appointments);

        $patientData = null;

        $columnsMapping = self::getColumnsMappingWithIndexes();

        try {
            $crawler->filter('#divDaily .tblAppts > tr')->each(function ($node, $i) use (&$patientData, &$columnsMapping) {
                $appointmentId = self::getIntVal('appointment_id', $node, $columnsMapping);
                if (empty($appointmentId)) {
                    return;
                }

                $patientData = [
                    'patient_id'             => self::getIntVal('patient_id', $node, $columnsMapping),
                    'patient_account_number' => self::getStringVal('patient_account_number', $node, $columnsMapping),
                    'auth_number'            => self::getStringVal('auth_number', $node, $columnsMapping),
                    'first_name'             => self::getStringVal('first_name', $node, $columnsMapping),
                    'last_name'              => self::getStringVal('last_name', $node, $columnsMapping),
                    'middle_initial'         => self::getStringVal('middle_initial', $node, $columnsMapping),
                    'insured_name'           => self::getStringVal('insured_name', $node, $columnsMapping),
                    'secondary_insured_name' => self::getStringVal('secondary_insured_name', $node, $columnsMapping),
                    'address'                => self::getStringVal('address', $node, $columnsMapping),
                    'cell_phone'             => self::getStringVal('cell_phone', $node, $columnsMapping),
                    'home_phone'             => self::getStringVal('home_phone', $node, $columnsMapping),
                    'work_phone'             => self::getStringVal('work_phone', $node, $columnsMapping),
                    'visits_auth'            => self::getIntVal('visits_auth', $node, $columnsMapping),
                    'visits_auth_left'       => 1,
                    'primary_insurance'      => self::getStringVal('primary_insurance', $node, $columnsMapping),
                    'secondary_insurance'    => self::getStringVal('secondary_insurance', $node, $columnsMapping),
                    'sex'                    => self::getStringVal('sex', $node, $columnsMapping),
                    'elig_copay'             => self::getStringVal('elig_copay', $node, $columnsMapping),
                    'elig_status'            => self::getStringVal('elig_status', $node, $columnsMapping),
                    'reffering_provider'     => self::getStringVal('reffering_provider', $node, $columnsMapping),
                    'visit_copay'            => self::getFloatVal('visit_copay', $node, $columnsMapping),
                ];

                throw new BreakException();
            });
        } catch (BreakException $e) {
            return $patientData;
        }
        return $patientData;
    }

    public static function getAppointmentDataFromAppointmentsHtml($appointments)
    {
        $parsedAt = Carbon::now()->timestamp;
        $crawler = new Crawler($appointments);

        $appointmentData = null;

        $columnsMapping = self::getColumnsMappingWithIndexes();

        try {
            $crawler->filter('#divDaily .tblAppts > tr')->each(function ($node, $i) use ($parsedAt, &$appointmentData, &$columnsMapping) {
                $appointmentId = self::getIntVal('appointment_id', $node, $columnsMapping);
                if (empty($appointmentId)) {
                    return;
                }

                $appointmentData = [
                    'time'             => isset($columnsMapping['add_button']) && isset($columnsMapping['add_button']['index']) ? strtotime($node->children()->eq($columnsMapping['add_button']['index'])->children()->attr('title')) : null,
                    'idAppointments'   => $appointmentId,
                    'resource'         => self::getStringVal('resource', $node, $columnsMapping),
                    'visit_copay'      => self::getFloatVal('visit_copay', $node, $columnsMapping),
                    'visit_length'     => self::getIntVal('visit_length', $node, $columnsMapping),
                    'notes'            => self::getStringVal('notes', $node, $columnsMapping),
                    'reason_for_visit' => self::getStringVal('reason_for_visit', $node, $columnsMapping),
                    'sheldued_by'      => self::getStringVal('sheldued_by', $node, $columnsMapping),
                    'date_created'     => self::getStringVal('date_created', $node, $columnsMapping),
                    'check_in'         => self::getStringVal('check_in', $node, $columnsMapping),
                    'not_found_count'  => 0,
                    'parsed_at'        => $parsedAt,
                ];

                throw new BreakException();
            });
        } catch (BreakException $e) {
            return $appointmentData;
        }
        return $appointmentData;
    }

    public static function getStructurePatientData(): array
    {
        return [
            'patient_id' => 'int',
            'patient_account_number' => 'string',
            'auth_number' => 'string',
            'first_name' => 'string',
            'last_name' => 'string',
            'middle_initial' => 'string',
            'insured_name' => 'string',
            'secondary_insured_name' => 'string',
            'address' => 'string',
            'cell_phone' => 'string',
            'home_phone' => 'string',
            'work_phone' => 'string',
            'visits_auth' => 'int',
            'visits_auth_left' => 'int',
            'primary_insurance' => 'string',
            'secondary_insurance' => 'string',
            'sex' => 'string',
            // 'elig_copay' => 'string',
            'elig_status' => 'string',
            'reffering_provider' => 'string',
            'visit_copay' => 'float',
        ];
    }

    public static function getStructureAppointmentData(): array
    {
        return [
            'time' => 'int',
            'idAppointments' => 'int',
            'resource' => 'string',
            'visit_copay' => 'float',
            'visit_length' => 'int',
            'notes' => 'string',
            'reason_for_visit' => 'string',
            'sheldued_by' => 'string',
            'date_created' => 'string',
            'check_in' => 'string',
            'not_found_count' => 'int',
            'parsed_at' => 'int',
        ];
    }

    public static function getAppointmentDataForHtml(): array
    {
        $officeData = self::getOfficeDataForHtml();

        return [
            "time" => 1686146400,
            "idAppointments" => 123456789,
            "visit_copay" => 15.0,
            "visit_length" => 60,
            'visits_auth' => 0,
            "notes" => "asd",
            "reason_for_visit" => "Telehealth",
            "sheldued_by" => "groupbwt",
            "date_created" => "Jun 07, 2023 10:03 AM",
            "address" => "123 New St, LA, CA, Test city, CA 90001",
            "patient_age" => 44,
            "cell_phone" => "111-111-1111",
            'home_phone' => '222-222-2222',
            "work_phone" => '333-333-3333',
            "birthday" => '12/23/1978',
            "sex" => 'M',
            "status" => 'Visit Created',
            'elig_status' => 'Do Not Check',
            'elig_copay' => '',
            'first_name' => 'ABC',
            'last_name' => 'XYZ',
            'middle_initial' => 'QWE',
            'insured_name' => 'XYZ, ABC',
            'secondary_insured_name' => '',
            'secondary_insurance' => '',
            'office' => $officeData['office'],
            'patient_id' => 98765432,
            'primary_insurance' => 'CIGNA Behavioral Health',
            'provider_name' => 'Xyz Test',
            'reffering_provider' => '',
            'auth_number' => '',
            'check_in' => '',
            'patient_account_number' => '',
            'resource' => 'Room 1 - Test Room'
        ];
    }

    public static function getOfficeDataForHtml(): array
    {
        return [
            "office" => 'Encino Office',
            "external_id" => 215197
        ];
    }

    public static function getRoomNameFromResource(string $resource): string
    {
        return preg_replace('/\d ?- ?/', '', $resource);
    }

    public static function getMockAppointmentsHtml(array $appointmentData, array $officeData): string
    {
        $date = Carbon::createFromTimestamp($appointmentData['time']);
        $hours = $date->format('H');
        $minutes = $date->format('i');
        $formattedTime = $date->format('m/d/Y h:i A');

        $officesHtml = '<select id="ctl00_phFolderContent_Appointments_lstOffice" class="text">
            <option value="' . $officeData['external_id'] . '">' . $officeData['office'] . '</option>
        </select>';

        $appointmentsHtml = '<div id="divDaily">
            <table class="tblAppts">
                <thead>
                    <tr>
                        <th class="th60" align="center" colspan="2">Time</th>
                        <th style="width: 90px;">Patient Name</th>
                        <th nowrap="" style="width: -3;">Address</th>
                        <th nowrap="" style="width: 30;">Appointment ID</th>
                        <th nowrap="" style="width: -3;">Age</th>
                        <th style="width: -3;">Auth. Number</th>
                        <th nowrap="" style="width: -3;">Cell Phone</th>
                        <th nowrap="" style="width: -3;">Check In?</th>
                        <th nowrap="" style="width: -3;">Date Created</th>
                        <th nowrap="" style="width: 30;">DOB</th>
                        <th style="width: 30;"><span class="hdrLabel" style="width:82px">Automated Eligibility Status</span></th>
                        <th nowrap="" style="width: -3;">First Name</th>
                        <th nowrap="" style="width: -3;">Home Phone</th>
                        <th nowrap="" style="width: -3;">Insured Name</th>
                        <th nowrap="" style="width: -3;">Last Name</th>
                        <th nowrap="" style="width: -3;">Middle Initial</th>
                        <th nowrap="" style="width: 30;">No. of Visits Auth.</th>
                        <th style="width: -3;"><span class="hdrLabel" style="min-width:120px">Notes</span></th>
                        <th nowrap="" style="width: -3;">Office</th>
                        <th nowrap="" style="width: -3;">Patient Account No</th>
                        <th nowrap="" style="width: 30;">Patient ID</th>
                        <th nowrap="" style="width: -3;">Primary Insurance</th>
                        <th nowrap="" style="width: -3;">Provider Name</th>
                        <th nowrap="" style="width: -3;">Reason For Visit</th>
                        <th nowrap="" style="width: -3;">Referring Provider</th>
                        <th nowrap="" style="width: -3;">Reminder Status</th>
                        <th nowrap="" style="width: -3;">Resource</th>
                        <th nowrap="" style="width: -3;">Scheduled By</th>
                        <th nowrap="" style="width: -3;">Sec. Insured Name</th>
                        <th nowrap="" style="width: -3;">Secondary Insurance</th>
                        <th nowrap="" style="width: -3;">Sex</th>
                        <th nowrap="" style="width: -3;">Status</th>
                        <th nowrap="" style="width: -3;">Visit Copay</th>
                        <th style="width: -3;">Visit Length</th>
                        <th nowrap="" style="width: -3;">Work Phone</th>
                        <th class="th30" align="center">Add</th>
                        <th class="th30" align="center">Edit</th>
                        <th class="th25" align="center">Del</th>
                        <th class="th40" align="center">Check<br>In</th>
                        <th class="th40" align="center">Check<br>Out</th>
                        <th class="th40" align="center">Create<br>Visit</th>
                    </tr>
                </thead>
                <tr>
                    <td>' . $hours . '</td>
                    <td>:' . $minutes . '</td>
                    <td></td>
                    <td class="TableRowStyle1">' . $appointmentData['address'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['idAppointments'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['patient_age'] . ' year old&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['auth_number'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['cell_phone'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['check_in'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['date_created'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['birthday'] . '&nbsp;</td>
                    <!-- <td class="TableRowStyle1">' . $appointmentData['elig_copay'] . '</td> -->
                    <td class="TableRowStyle1">' . $appointmentData['elig_status'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['first_name'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['home_phone'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['insured_name'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['last_name'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['middle_initial'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['visits_auth'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['notes'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['office'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['patient_account_number'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['patient_id'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['primary_insurance'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['provider_name'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['reason_for_visit'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['reffering_provider'] . '&nbsp;</td>
                    <td class="TableRowStyle1">&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['resource'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['sheldued_by'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['secondary_insured_name'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['secondary_insurance'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['sex'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['status'] . '</td>
                    <td class="TableRowStyle1">' . $appointmentData['visit_copay'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['visit_length'] . '&nbsp;</td>
                    <td class="TableRowStyle1">' . $appointmentData['work_phone'] . '&nbsp;</td>
                    <td class="TableRowStyle1"><img src="" title="' . $formattedTime . '"></td>
                    <td class="TableRowStyle1"><img src="" title="' . $formattedTime . '"></td>
                </tr>
            </table>
        </div>';

        return "<div>$officesHtml $appointmentsHtml</div>";
    }


    private static function getPeriod(): CarbonPeriod
    {
        $date = Carbon::now()->subDays(config('parser.parsing_depth'));

        $maxDate = Carbon::now()->addDays(config('parser.parsing_depth_after_today'));

        return CarbonPeriod::create($date, $maxDate);
    }

    private static function checkAppointmentsHtmlData($appointments): bool
    {
        $crawler = new Crawler($appointments);

        $columnsMapping = self::getColumnsMappingWithIndexes();
        
        try {
            $crawler->filter('#divDaily .tblAppts > tr')->each(function ($node) use (&$columnsMapping) {
                $appointmentId = self::getIntVal('appointment_id', $node, $columnsMapping);
                if (empty($appointmentId)) {
                    return;
                }

                throw new BreakException();
            });
        } catch (BreakException $e) {
            return true;
        }

        return false;
    }

    private static function getColumnsMappingWithIndexes()
    {
        return [
            'time' => ['index' => 0, 'name' => 'Time', 'required' => false],
            'patient_name' => ['index' => 2, 'name' => 'Patient Name', 'required' => false],
            'address' => ['index' => 3, 'name' => 'Address', 'required' => true],
            'appointment_id' => ['index' => 4, 'name' => 'Appointment ID', 'required' => true],
            'age' => ['index' => 5, 'name' => 'Age', 'required' => false],
            'auth_number' => ['index' => 6, 'name' => 'Auth. Number', 'required' => true],
            'cell_phone' => ['index' => 7, 'name' => 'Cell Phone', 'required' => true],
            'check_in' => ['index' => 8, 'name' => 'Check In?', 'required' => true],
            'date_created' => ['index' => 9, 'name' => 'Date Created', 'required' => true],
            'birth_date' => ['index' => 10, 'name' => 'DOB', 'required' => false],
            'elig_copay' => ['index' => null, 'name' => 'Eligibility Copay', 'required' => false],
            'elig_status' => ['index' => 11, 'name' => 'Automated Eligibility Status', 'required' => true],
            'first_name' => ['index' => 12, 'name' => 'First Name', 'required' => true],
            'home_phone' => ['index' => 13, 'name' => 'Home Phone', 'required' => true],
            'insured_name' => ['index' => 14, 'name' => 'Insured Name', 'required' => true],
            'last_name' => ['index' => 15, 'name' => 'Last Name', 'required' => true],
            'middle_initial' => ['index' => 16, 'name' => 'Middle Initial', 'required' => true],
            'visits_auth' => ['index' => 17, 'name' => 'No. of Visits Auth.', 'required' => true],
            'notes' => ['index' => 18, 'name' => 'Notes', 'required' => true],
            'office' => ['index' => 19, 'name' => 'Office', 'required' => true],
            'patient_account_number' => ['index' => 20, 'name' => 'Patient Account No', 'required' => true],
            'patient_id' => ['index' => 21, 'name' => 'Patient ID', 'required' => true],
            'primary_insurance' => ['index' => 22, 'name' => 'Primary Insurance', 'required' => true],
            'provider_name' => ['index' => 23, 'name' => 'Provider Name', 'required' => true],
            'reason_for_visit' => ['index' => 24, 'name' => 'Reason For Visit', 'required' => true],
            'reffering_provider' => ['index' => 25, 'name' => 'Referring Provider', 'required' => true],
            'reminder_status' => ['index' => 26, 'name' => 'Reminder Status', 'required' => false],
            'resource' => ['index' => 27, 'name' => 'Resource', 'required' => false],
            'sheldued_by' => ['index' => 28, 'name' => 'Scheduled By', 'required' => true],
            'secondary_insured_name' => ['index' => 29, 'name' => 'Sec. Insured Name', 'required' => true],
            'secondary_insurance' => ['index' => 30, 'name' => 'Secondary Insurance', 'required' => true],
            'sex' => ['index' => 31, 'name' => 'Sex', 'required' => true],
            'status' => ['index' => 32, 'name' => 'Status', 'required' => true],
            'visit_copay' => ['index' => 33, 'name' => 'Visit Copay', 'required' => true],
            'visit_length' => ['index' => 34, 'name' => 'Visit Length', 'required' => true],
            'work_phone' => ['index' => 35, 'name' => 'Work Phone', 'required' => true],
            'add_button' => ['index' => 36, 'name' => 'Add', 'required' => true],
        ];
    }

    private static function getStringVal(string $key, $rowNode, array &$columnsMapping)
    {
        $text = self::getColumnText($key, $rowNode, $columnsMapping);
        return empty($text) ? null : remove_nbsp($text);
    }

    private static function getIntVal(string $key, $rowNode, array &$columnsMapping)
    {
        $text = self::getColumnText($key, $rowNode, $columnsMapping);
        return empty($text) ? null : intval($text);
    }

    private static function getFloatVal(string $key, $rowNode, array &$columnsMapping)
    {
        $text = self::getColumnText($key, $rowNode, $columnsMapping);
        return empty($text) ? null : floatval($text);
    }

    private static function getColumnText(string $key, $rowNode, array &$columnsMapping)
    {
        if (isset($columnsMapping[$key]) && isset($columnsMapping[$key]['index'])) {
            return $rowNode->children()->eq($columnsMapping[$key]['index'])->text();
        }

        return null;
    }
}