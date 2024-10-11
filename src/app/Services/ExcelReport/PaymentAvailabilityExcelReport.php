<?php

namespace App\Services\ExcelReport;

use App\Appointment;
use App\Patient;
use App\PatientStatus;
use App\Status;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Style\Border as StyleBorder;

class PaymentAvailabilityExcelReport
{
    public function generateReport()
    {
        $folderNameForReport = config('google_drive_folder.payment_availability_excel_report');
        $data = $this->collectDataForExcelReport();
        $this->generateExcelReport($data, $folderNameForReport);
    }

    private function collectDataForExcelReport()
    {
        $visitIdStatus = Status::getVisitCreatedId();
        $completeIdStatus  = Status:: getCompletedId();

        $cancelledByPatientIdStatus = Status::getCancelledByPatientId();
        $cancelledByProviderIdStatus = Status::getCancelledByProviderId();
        $lastMinuteCancelByPatientIdStatus = Status::getLastMinuteCancelByPatientId();
        $cancelledByOfficeIdStatus = Status::getCancelledByOfficeId();

         return Patient::select(
            'patients.id',
            'patients.first_name',
            'patients.last_name',
            'patients.primary_insurance',
            'patients.visit_copay',
            'patients.insurance_plan_id',
            'patient_statuses.status AS patient_status',
            \DB::raw('(
                SELECT COUNT(*)
                FROM appointments
                WHERE appointments.patients_id = patients.id
                AND appointments.appointment_statuses_id IN (' . $visitIdStatus . ',' . $completeIdStatus . ')
                AND appointments.deleted_at IS NULL
            ) AS completed_and_visited_created_appointments_count'),
            \DB::raw('(
                SELECT COUNT(*)
                FROM appointments
                WHERE appointments.patients_id = patients.id
                AND appointments.appointment_statuses_id IN ('. $cancelledByPatientIdStatus. ',' .$cancelledByProviderIdStatus. ',' .$lastMinuteCancelByPatientIdStatus. ',' .$cancelledByOfficeIdStatus.')
                AND appointments.deleted_at IS NULL
            ) AS canceled_appointments_count'), 
            \DB::raw('(
                SELECT COUNT(*)
                FROM patient_square_account_cards
                WHERE patient_square_account_cards.patient_square_account_id = patient_square_accounts.id
                AND (patient_square_account_cards.exp_year >= YEAR(CURRENT_DATE())
                AND patient_square_account_cards.exp_month >= MONTH(CURRENT_DATE()))
            ) as credit_cards_count'),
            \DB::raw('(
                SELECT COUNT(*)
                FROM square_transactions
                JOIN patient_square_accounts ON square_transactions.customer_id = patient_square_accounts.id
                WHERE patient_square_accounts.patient_id = patients.id
            ) AS patient_preprocessed_transactions_count'),
            \DB::raw('(
                SELECT SUM(square_transactions.amount_money)
                FROM square_transactions
                JOIN patient_square_accounts ON square_transactions.customer_id = patient_square_accounts.id
                WHERE patient_square_accounts.patient_id = patients.id
            ) as total_amount_money')
        )
            ->join('patient_statuses', 'patients.status_id', '=', 'patient_statuses.id')
            ->join('patient_square_accounts', 'patients.id', '=', 'patient_square_accounts.patient_id') 
            ->where('patients.is_test', 0)
            ->where('patients.is_payment_forbidden', 0)
            ->whereIn('patients.status_id', [PatientStatus::getActiveId(), PatientStatus::getInactiveId()])
            ->where(function ($query) {
                $query
                    ->whereHas('appointments', function ($query) {
                        $query->leftJoin('treatment_modalities', 'treatment_modalities.id', '=', 'appointments.treatment_modality_id')
                            ->where('treatment_modalities.is_telehealth', false);
                    })
                    ->orWhereHas('insurancePlan', function ($query) {
                        $query->where('need_collect_copay_for_telehealth', 1);
                    });
            })
            ->distinct()
            ->get()
            ->map(function ($patient) {
                return [
                    'Full Name' => $patient->first_name . ' ' . $patient->last_name,
                    'Insurance' => $patient->primary_insurance,
                    'Patient Status' => $patient->patient_status,
                    'Credit Cards' => $patient->credit_cards_count ? 'YES' : 'NO',
                    'Co-pay' => $patient->visit_copay ? $patient->visit_copay : '0',
                    'Transaction Count' => $patient->patient_preprocessed_transactions_count ? $patient->patient_preprocessed_transactions_count : '0',
                    'Total Transaction Amount Money' => $patient->total_amount_money ? ($patient->total_amount_money / 100) : '0',
                    'Completed And Visited Created Appointments' => $patient->completed_and_visited_created_appointments_count ? $patient->completed_and_visited_created_appointments_count : '0',
                    'Canceled Appointments' => $patient->canceled_appointments_count  ? $patient->canceled_appointments_count: '0',
                ];
            });
    }

    private function generateExcelReport($patientsData, $folderNameForReport)
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        $titles = [
            'Full Name',
            'Insurance',
            'Patient Status',
            'Has Credit Card',
            'Patient Co-pay',
            'Number of Transactions',
            'Total Transaction Amount Money',
            'Completed And Visited Created Appointments',
            'Canceled Appointments',
        ];

        $boldFontStyle = [
            'font' => [
                'bold' => true,
            ],
        ];
        $worksheet->getStyle('A1:I1')->applyFromArray($boldFontStyle);

        $worksheet->fromArray([$titles], NULL, 'A1');
        $worksheet->fromArray($patientsData->toArray(), NULL, 'A2');

        foreach (range('A', 'I') as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => StyleBorder::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $worksheet->getStyle('A1:I' . (count($patientsData) + 1))->applyFromArray($borderStyle);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Patient_Payment_Availability.xlsx';
        $path = storage_path('app/' . $filename);
        $writer->save($path);
        Storage::disk('google')->put($folderNameForReport  . '/' . $filename, file_get_contents($path));
    }
}
