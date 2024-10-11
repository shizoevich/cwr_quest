<?php

namespace App\Console\Commands\OfficeAlly;

use App\Console\Commands\OfficeAllyHelperTrait;
use App\Models\Diagnose;
use App\Patient;
use App\PatientVisit;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\TableSeparator;

class CompareDiagnoses extends Command
{
    use OfficeAllyHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'office-ally:compare-diagnoses {patient}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare patient diagnoses in CWR with diagnoses in OfficeAlly';

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
        $this->initOfficeAllyHelpers();

        $patient = Patient::whereKey($this->argument('patient'))->firstOrFail();

        $cwrDiagnoses = $this->getDiagnoses($patient);
        $officeAllyDiagnoses = $this->getOfficeAllyDiagnoses($patient);

        $diffs = $this->compareDiagnoses($cwrDiagnoses, $officeAllyDiagnoses);

        if ($diffs->isEmpty()) {
            $this->info('No differences were found in diagnoses');
            return;
        }

        $diagnoses = Diagnose::whereIn('code', $diffs->flatMap(function ($visit) {
            return array_merge($visit['cwr_diagnoses'], $visit['office_ally_diagnoses']);
        }))->get()->keyBy('code');

        $this->response($diffs, $diagnoses);
    }

    private function getDiagnoses(Patient $patient): Collection
    {
        $diagnoses = PatientVisit::query()
            ->select([
                'patient_visits.visit_id',
                'patient_visits.date',
                'patient_notes.id as patient_note_id',
                'diagnoses.code',
            ])
            ->leftJoin('patient_notes', 'patient_notes.appointment_id', 'patient_visits.appointment_id')
            ->leftJoin('patient_note_diagnoses', 'patient_notes.id', 'patient_note_diagnoses.patient_note_id')
            ->leftJoin('diagnoses', 'patient_note_diagnoses.diagnose_id', 'diagnoses.id')
            ->where('patient_visits.patient_id', $patient->id)
            ->whereNotNull('patient_visits.visit_id')
            ->orderBy('patient_visits.visit_id')
            ->toBase()
            ->get();

        return $diagnoses->groupBy('visit_id')->map(function (Collection $visits, $visitId) {
            $diagnoses = $visits->pluck('code')->filter(function ($v) {
                return $v !== null;
            })->unique()->toArray();

            $patientNoteId = $visits->first()->patient_note_id;
            if (!$patientNoteId) {
                $diagnoses = ['Progress note is missing'];
            }

            return [
                'visit_id' => $visitId,
                'date' => $visits->first()->date,
                'diagnoses' => $diagnoses,
            ];
        })->values();
    }

    private function getOfficeAllyDiagnoses(Patient $patient): Collection
    {
        $visits = $this->officeAllyHelper()->getVisitListByPatientId($patient->patient_id);

        return Collection::wrap(array_map(function ($visit) {
            $claim = str_replace_last('|', '', $visit['cell'][16]);
            [$month, $day, $year] = explode('/', $visit['cell'][5]);
            return [
                'visit_id' => $visit['id'],
                'date' => "$year-$month-$day",
                'diagnoses' => $this->officeAllyHelper()->getDiagnosesFromClaim($claim),
            ];
        }, $visits));
    }

    private function compareDiagnoses(Collection $cwrDiagnoses, Collection $officeAllyDiagnoses): Collection
    {
        return $cwrDiagnoses->pluck('visit_id')
            ->concat($officeAllyDiagnoses->pluck('visit_id'))
            ->unique()
            ->map(function ($visitId) use ($cwrDiagnoses, $officeAllyDiagnoses) {
                $cwrVisit = $cwrDiagnoses->first(function ($diagnose) use ($visitId) {
                    return $diagnose['visit_id'] === $visitId;
                }, ['diagnoses' => ['Visit is missing'], 'date' => null]);
                $officeAllyVisit = $officeAllyDiagnoses->first(function ($diagnose) use ($visitId) {
                    return $diagnose['visit_id'] === $visitId;
                }, ['diagnoses' => ['Visit is missing'], 'date' => null]);

                return [
                    'visit_id' => $visitId,
                    'date' => $cwrVisit['date'] ?? $officeAllyVisit['date'],
                    'cwr_diagnoses' => array_values(array_sort($cwrVisit['diagnoses'])),
                    'office_ally_diagnoses' => array_values(array_sort($officeAllyVisit['diagnoses'])),
                ];
            })
            ->filter(function ($visit) {
                return $visit['cwr_diagnoses'] != $visit['office_ally_diagnoses'];
            });
    }

    private function response(Collection $visits, Collection $diagnoses)
    {
        $this->warn('Differences in diagnoses found');
        $this->table(['Date', 'CWR diagnoses', 'Office Ally diagnoses'], $visits->map(function ($visit) use ($diagnoses) {
            return [
                'Date' => $visit['date'],
                'CWR diagnoses' => $this->diagnosesToTableCell($visit['cwr_diagnoses'], $diagnoses),
                'Office Ally diagnoses' => $this->diagnosesToTableCell($visit['office_ally_diagnoses'], $diagnoses),
            ];
        })->sortBy(function ($visit) {
            return strtotime($visit['Date']);
        })->flatMap(function ($row) {
            return [$row, new TableSeparator()];
        })->slice(0, -1));
    }

    private function diagnosesToTableCell(array $diagnoseCodes, Collection $diagnoses): string
    {
        return implode("\n", array_map(function ($diagnoseCode) use ($diagnoses) {
            if (array_key_exists($diagnoseCode, $diagnoses->toArray())) {
                return $diagnoseCode . ': ' . $diagnoses[$diagnoseCode]->description;
            }
            return $diagnoseCode;
        }, $diagnoseCodes));
    }
}
