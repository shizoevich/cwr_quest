<?php

namespace App\Console\Commands\StatisticsReport;

use App\Availability;
use App\Appointment;
use App\Status;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GenerateAvailabilitySchedulingReport extends Command
{
    const DATE_OF_KAISER_APPOINTMENTS_INTERNAL_ID_WAS_ADDED = '2021-05-01';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics-report:availability-scheduling';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $data = $this->getData();

        $this->generateExcelReport($data);
    }

    private function getData(): array
    {
        $data = [];
        $availabilitiesData = [];

        Availability::query()
            ->select([
                'availabilities.id',
                'availabilities.provider_id',
                'providers.provider_name',
                'availabilities.start_date',
                'availabilities.start_time',
                'availabilities.length',
                'availability_types.type AS availability_type',
                'availability_subtypes.type AS availability_subtype',
                'availabilities.created_at',
            ])
            ->join('providers', 'availabilities.provider_id', '=', 'providers.id')
            ->leftJoin('availability_types', 'availability_types.id', '=', 'availabilities.availability_type_id')
            ->leftJoin('availability_subtypes', 'availability_subtypes.id', '=', 'availabilities.availability_subtype_id')
            ->whereDate('availabilities.start_date', '>=', self::DATE_OF_KAISER_APPOINTMENTS_INTERNAL_ID_WAS_ADDED)
            ->where('providers.is_test', 0)
            ->orderBy('availabilities.start_date', 'desc')
            ->orderBy('providers.provider_name', 'asc')
            ->orderBy('availabilities.start_time', 'asc')
            ->each(function ($availability) use (&$availabilitiesData) {
                $duration = 0;
                $startDateTime = Carbon::parse($availability->start_date)->setTimeFromTimeString($availability->start_time);

                while ($availability->length > $duration) {
                    $startTimestamp = $startDateTime->copy()->addMinutes($duration)->timestamp;

                    $availabilitiesData[] = [
                        'id' => $availability->id,
                        'provider_id' => $availability->provider_id,
                        'provider_name' => $availability->provider_name,
                        'date' => Carbon::parse($availability->start_date)->toDateString(),
                        'start_timestamp' => $startTimestamp,
                        'end_timestamp' => $startTimestamp + 3600, // add one hour in seconds (60*60)
                        'availability_type' => $availability->availability_type,
                        'availability_subtype' => $availability->availability_subtype,
                        'created_at' => $availability->created_at,
                    ];

                    $duration += 60;

                    // dump('Processed availability: ' . $availability->id . ', duration: ' . $duration);
                }
            });

        dump('--------------------------------------');
        dump('------ Got all availabilities --------');
        dump('--------------------------------------');

        foreach ($availabilitiesData as $availabilityData) {
            $appointmentData = Appointment::query()
                ->select([
                    'appointments.id',
                    'appointments.patients_id AS patient_id',
                    'kaiser_appointments.id AS kaiser_appointment_id',
                    'appointments.created_at',
                    'appointment_statuses.status',
                    'appointments.custom_notes',
                    DB::raw('CONCAT(patients.first_name, " ", patients.last_name) AS patient_name'),
                ])
                ->join('patients', 'appointments.patients_id', '=', 'patients.id')
                ->leftJoin('kaiser_appointments', 'kaiser_appointments.internal_id', '=', 'appointments.id')
                ->leftJoin('appointment_statuses', 'appointment_statuses.id', '=', 'appointments.appointment_statuses_id')
                ->where('appointments.providers_id', $availabilityData['provider_id'])
                ->where('appointments.time', '>=', $availabilityData['start_timestamp'])
                ->where('appointments.time', '<', $availabilityData['end_timestamp'])
                ->where('patients.is_test', 0)
                ->first();

            $startTime = Carbon::createFromTimestamp($availabilityData['start_timestamp'])->format('H:i');
            $endTime = Carbon::createFromTimestamp($availabilityData['end_timestamp'])->format('H:i');

            // dump('Add data for: ' . $availabilityData['id']);

            $appointmentCreatedBy = null;
            if ($appointmentData['kaiser_appointment_id']) {
                $appointmentCreatedBy = 'Lucet';
            } else if ($appointmentData['id']) {
                $appointmentCreatedBy = 'CWR';
            }

            $isApptWithNewPatient = '-';
            if ($appointmentData['id'] && $appointmentData['patient_id']) {
                $isApptWithNewPatient = $this->checkIsApptWithNewPatient($appointmentData['id'], $appointmentData['patient_id']) ? 'Yes' : 'No';
            }

            $data[] = [
                'Provider Name' => $availabilityData['provider_name'],
                'Availability Type' => $availabilityData['availability_type'] ?: '-',
                'Availability Subtype' => $availabilityData['availability_subtype'] ?: '-',
                'Availability Created At' => $availabilityData['created_at'],
                'Availability Date Time' => $availabilityData['date'] . ' ' . $startTime . ' - ' . $endTime,
                'Appointment Created At' => $appointmentData['created_at'] ?: '-',
                'Appointment Created By' => $appointmentCreatedBy ?: '-',
                'Appointment Status' => $appointmentData['status'] ?: '-',
                'Patient ID' => $appointmentData['patient_id'] ?: '-',
                'Patient Name' => $appointmentData['patient_name'] ?: '-',
                'Is Appt. With New Patient' => $isApptWithNewPatient,
                'Appointment Notes' => $appointmentData['custom_notes'] ?: '-',
            ];
        }

        dump('--------------------------------------');
        dump('------ Finished collecting data ------');
        dump('--------------------------------------');

        return $data;
    }

    private function checkIsApptWithNewPatient($appointmentId, $patientId)
    {
        $firstAppointment = Appointment::query()
            ->select('appointments.id')
            ->orderBy('appointments.time')
            ->where('appointments.patients_id', $patientId)
            ->whereIn('appointment_statuses_id', [Status::getActiveId(), Status::getCompletedId(), Status::getVisitCreatedId()])
            ->first();

        return $firstAppointment['id'] === $appointmentId;
    }

    private function generateExcelReport($visitsData): void
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Provider Name',
            'Availability Type',
            'Availability Subtype',
            'Availability Created At',
            'Availability Date Time',
            'Appointment Created At',
            'Appointment Created By',
            'Appointment Status',
            'Patient ID',
            'Patient Name',
            'Is Appt. With New Patient',
            'Appointment Notes'
        ];

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($visitsData, NULL, 'A2');

        $writer = new Xlsx($spreadsheet);
        $filename = "availability_scheduling.xlsx";
        $path = storage_path('app/temp/' . $filename);
        $writer->save($path);
    }
}
