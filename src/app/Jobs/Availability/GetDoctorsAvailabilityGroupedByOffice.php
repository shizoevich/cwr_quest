<?php

namespace App\Jobs\Availability;

use App\Appointment;
use App\AvailabilitySubtype;
use App\AvailabilityType;
use App\Models\Therapist\TherapistSurveyEthnicity;
use App\Models\Therapist\TherapistSurveyLanguage;
use App\Models\Therapist\TherapistSurveyPatientCategory;
use App\Models\Therapist\TherapistSurveyRace;
use App\Models\Therapist\TherapistSurveySpecialty;
use App\Models\Therapist\TherapistSurveyTreatmentType;
use App\Office;
use App\PatientInsurance;
use App\Provider;
use App\Status;
use App\TherapistSurveyAgeGroup;
use App\TherapistSurveyTypeOfClient;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class GetDoctorsAvailabilityGroupedByOffice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Carbon */
    protected $startDate;
    /** @var Carbon */
    protected $endDate;
    /** @var Collection */
    protected $data;
    /** @var bool */
    protected $isAjax;

    /** @var array */
    protected $officesInfo;

    /**
     * Create a new job instance.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param Collection|array $data
     * @param bool $isAjax
     */
    public function __construct(Carbon $startDate, Carbon $endDate, $data, $isAjax = false)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->data = collect($data);
        $this->isAjax = $isAjax;
        $this->officesInfo = Office::all()->keyBy('id')->toArray();
    }

    /**
     * Execute the job.
     *
     * @return array
     */
    public function handle()
    {
        $infoWorkHours = $this->getEmptyDataset();
        $filtersValues = $this->getFilterValues();

        if (!count($infoWorkHours)) {
            if ($this->isAjax) {
                return [];
            }

            return [
                'filtersValues' => $filtersValues
            ];
        }

        $workHours = \Bus::dispatchNow(new GetProvidersAvailability($this->startDate, $this->endDate, $this->data->all()));

        foreach ($workHours as $workHour) {
            $start = $workHour->date->copy();
            $end = $start->copy()->addMinutes($workHour->length);
            $workHour->end = $end;
            while ($start->lt($end)) {
                $infoWorkHours[$start->toDateString()][$start->toTimeString()][$workHour->office_id][] = $workHour->toArray();
                $start->addMinutes(30);
            }
        }

        $cancelStatusesIds = Status::getOtherCancelStatusesId();

        //@todo: [Project Optimization] - check retrieving appointments. Maybe filters is not needed in this case.
        $appointments = Appointment::with(['provider', 'officeRoom', 'provider.insurances'])
            ->addSelect([
                '*',
                \DB::raw('DATE(FROM_UNIXTIME(time)) as date_string'),
            ])
            ->withTherapistSurvey()
            ->where(
                'time',
                '>=',
                Carbon::parse(array_key_first($infoWorkHours))->timestamp
            )
            ->where(
                'time',
                '<=',
                Carbon::parse(array_key_last($infoWorkHours))->endOfDay()->timestamp
            )
            ->whereNotIn('appointment_statuses_id', $cancelStatusesIds)
            ->whereProvider($this->data->has('provider_id')
                ? intval($this->data->get('provider_id')) : 0)
            ->whereOffice($this->data->has('office_id')
                ? intval($this->data->get('office_id')) : 0)
            ->whereProviderAgeGroups($this->data->has('age_group_id_all')
                ? $this->data->get('age_group_id_all') : 0)
            ->whereProviderTypesOfClients($this->data->has('types_of_clients_id_all')
                ? $this->data->get('types_of_clients_id_all') : 0)
            ->whereProviderEthnicities($this->data->has('ethnicities_id_all')
                ? $this->data->get('ethnicities_id_all') : 0)
            ->whereProviderLanguages($this->data->has('languages_id_all')
                ? $this->data->get('languages_id_all') : 0)
            ->whereProviderPatientCategories($this->data->has('patient_categories_id_all')
                ? $this->data->get('patient_categories_id_all') : 0)
            ->whereProviderRaces($this->data->has('races_id_all')
                ? $this->data->get('races_id_all') : 0)
            ->whereProviderSpecialties($this->data->has('specialties_id_all')
                ? $this->data->get('specialties_id_all') : 0)
            ->whereProviderTreatmentTypes($this->data->has('treatment_types_id_all')
                ? $this->data->get('treatment_types_id_all') : 0)
            ->groupBy('appointments.id')
            ->orderBy('time')
            ->get();

        $infoAppointments = [];

        foreach ($appointments as $appointment) {
            $start = Carbon::createFromTimestamp($appointment->time);
            $end = $start->copy()->addMinutes($appointment->visit_length);
            while ($start->lt($end)) {
                $date = $start->toDateString();
                $time = $start->toTimeString();
                foreach ($infoWorkHours[$date][$time][$appointment->offices_id] as $k => $providerTimes) {
                    if ($appointment->providers_id == $providerTimes['provider_id']) {
                        array_splice($infoWorkHours[$date][$time][$appointment->offices_id], $k, 1);

                        if (!array_key_exists($appointment->offices_id, $infoAppointments)) {
                            $infoAppointments[$appointment->offices_id] = [];
                        }
                        if (!array_key_exists($appointment->providers_id, $infoAppointments[$appointment->offices_id])) {
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id] = [];
                        }
                        if (!array_key_exists($date, $infoAppointments[$appointment->offices_id][$appointment->providers_id])) {
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date] = [];
                        }
                        if (!array_key_exists($time, $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date])) {
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date][$time] = [];
                        }

                        $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date][$time][] = $appointment->toArray();
                    }
                }
                $start->addMinutes(15);
            }
        }

        $infoWorkHoursWithHourWindow = [];
        $infoWorkHoursTmp = $infoWorkHours;
        foreach ($infoWorkHours as $date => $hours) {
            foreach ($hours as $hour => $offices) {
                foreach ($offices as $officeId => $workHours) {
                    if (!empty($infoWorkHoursTmp[$date][$hour][$officeId])) {
                        $next = Carbon::parse("$date $hour")->addMinutes(30);
                        if (!empty($infoWorkHoursTmp[$next->toDateString()][$next->toTimeString()][$officeId])) {
                            array_set(
                                $infoWorkHoursWithHourWindow,
                                "$date.$hour.$officeId",
                                collect(array_merge($workHours, $infoWorkHoursTmp[$next->toDateString()][$next->toTimeString()][$officeId]))
                                    ->unique('id')
                                    ->toArray()
                            );
                            array_set($infoWorkHoursTmp, implode('.', [$next->toDateString(), $next->toTimeString(), $officeId]), []);
                        }
                    }
                }
            }
        }

        $events = [];
        foreach ($infoWorkHoursWithHourWindow as $date => $hours) {
            foreach ($hours as $hour => $offices) {
                $description = "";
                $info = [];
                $count = [];
                $popover = '';
                $start = Carbon::parse("$date $hour");
                $end = Carbon::parse("$date $hour");
                foreach ($offices as $office_id => $providers) {
                    if (count($providers) > 0) {
                        $description .= $this->officesInfo[$office_id]['office']
                            . ' :' . count($providers) . '<br/>';
                        $info[$office_id] = $providers;
                        if (!empty($popover)) {
                            $popover .= '<br/>';
                        }
                        $popover .= "<div class='d-flex flex-column' style='gap: 5px'><b>{$this->officesInfo[$office_id]['office']}:</b>";
                        foreach ($providers as $provider) {
                            $end = collect([
                                $end->copy(),
                                $start->copy()->addHour()->lte($provider['end']) ? $start->copy()->addHour() : $provider['end']->copy(),
                            ])->max();
                            $providerName = data_get($provider, 'provider.provider_name') ?? '';
                            $providerName = explode(',', $providerName)[0];
                            $icons = '';
                            if (data_get($provider, 'virtual')) {
                                $icons .= '&nbsp; <i class="fa fa-video-camera"></i>';
                            }
                            if (data_get($provider, 'in_person')) {
                                $icons .= '&nbsp; <i class="fa fa-user"></i>';
                            }
                            $hexColor = $provider['availability_type']['hex_color'];

                            $popover .=  "<span class='badge' style='background-color: $hexColor; font-size: 10px'>" . $providerName . $icons . '</span>';
                        }
                        $popover .= '</div>';
                    }
                    $count[$office_id] = count($providers);
                }

                if ($description != "") {
                    $events[] = [
                        'id' => time(),
                        'description' => $description,
                        'title' => "",
                        'allDay' => false,
                        'start' => $start->toDateTimeString(),
                        'end' => $end->toDateTimeString(),
                        'forceEventDuration' => true,
                        'editable' => false,
                        'count' => $count,
                        'info' => $info,
                        'popover' => $popover
                    ];
                }
            }
        }

        $appointments = $infoAppointments;

        if ($this->isAjax) {
            $response = $events;
            if ($this->data->has('needs') && $this->data->get('needs') === 'appointments') {
                $response = $appointments;
            }

            return $response;
        }
        
        return compact('events', 'appointments', 'filtersValues');
    }

    /**
     * @return array
     */
    private function getEmptyDataset(): array
    {
        $dataset = [];
        $date = $this->startDate->copy();
        while ($date->lt($this->endDate)) {
            $dayString = $date->toDateString();
            $dataset[$dayString] = [];
            for ($h = 0; $h < 24; $h++) {
                $timeString = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00:00';
                $dataset[$dayString][$timeString] = [];
                foreach ($this->officesInfo as $office) {
                    $dataset[$dayString][$timeString][$office['id']] = [];
                }

                $timeString = str_pad($h, 2, '0', STR_PAD_LEFT) . ':15:00';
                $dataset[$dayString][$timeString] = [];
                foreach ($this->officesInfo as $office) {
                    $dataset[$dayString][$timeString][$office['id']] = [];
                }

                $timeString = str_pad($h, 2, '0', STR_PAD_LEFT) . ':30:00';
                $dataset[$dayString][$timeString] = [];
                foreach ($this->officesInfo as $office) {
                    $dataset[$dayString][$timeString][$office['id']] = [];
                }

                $timeString = str_pad($h, 2, '0', STR_PAD_LEFT) . ':45:00';
                $dataset[$dayString][$timeString] = [];
                foreach ($this->officesInfo as $office) {
                    $dataset[$dayString][$timeString][$office['id']] = [];
                }
            }
            $date->addDay();
        }

        return $dataset;
    }

    /**
     * @return array
     */
    private function getProviders(): array
    {
        $providers = Provider::select(['id', 'provider_name'])
            ->orderBy('provider_name')
            ->get()
            ->keyBy('id')
            ->toArray();

        return $providers;
    }

    /**
     * @return array
     */
    private function getAgeGroups(): array
    {
        $ageGroups = TherapistSurveyAgeGroup::select(['id', 'label'])
            ->orderBy('id')
            ->get()
            ->toArray();

        return $ageGroups;
    }

    /**
     * @return array
     */
    private function getClientTypes(): array
    {
        $typesOfClients = TherapistSurveyTypeOfClient::select(['id', 'label'])
            ->orderBy('id')
            ->get()
            ->toArray();

        return $typesOfClients;
    }

    /**
     * @return array
     */
    private function getInsurances(): array
    {
        $insurances = PatientInsurance::select(['id', 'insurance as label'])
            ->orderBy('label')
            ->get()
            ->toArray();

        return $insurances;
    }

    /**
     * @return array
     */
    private function getKaiserTypes(): array
    {
        $kaiserTypes = Provider::KAISER_TYPES;

        return $kaiserTypes;
    }

    /**
     * @return array
     */
    private function getAvailabilityTypes(): array
    {
        $availability_types = AvailabilityType::select(['id', 'type'])
            ->orderBy('id')
            ->get()
            ->toArray();

        return $availability_types;
    }

    /**
     * @return array
     */
    private function getAvailabilitySubtypes(): array
    {
        $availability_subtypes = AvailabilitySubtype::select(['id', 'type'])
            ->orderBy('id')
            ->get()
            ->toArray();

        return $availability_subtypes;
    }

    /**
     * @return array
     */
    private function getEthnicities(): array
    {
        $ethnicities = TherapistSurveyEthnicity::select(['id', 'label'])
            ->orderBy('label')
            ->get()
            ->toArray();

        return $ethnicities;
    }

    /**
     * @return array
     */
    private function getLanguages(): array
    {
        $languages = TherapistSurveyLanguage::select(['id', 'label'])
            ->orderBy('label')
            ->get()
            ->toArray();

        return $languages;
    }

    /**
     * @return array
     */
    private function getPatientCategories(): array
    {
        $patient_categories = TherapistSurveyPatientCategory::select(['id', 'label'])
            ->orderBy('label')
            ->get()
            ->toArray();

        return $patient_categories;
    }

    /**
     * @return array
     */
    private function getRaces(): array
    {
        $races = TherapistSurveyRace::select(['id', 'label'])
            ->orderBy('label')
            ->get()
            ->toArray();

        return $races;
    }

    /**
     * @return array
     */
    private function getSpecialties(): array
    {
        $specialties = TherapistSurveySpecialty::select(['id', 'label'])
            ->get()
            ->toArray();

        return $specialties;
    }

    /**
     * @return array
     */
    private function getTreatmentTypes(): array
    {
        $treatmentTypes = TherapistSurveyTreatmentType::select(['id', 'label'])
            ->orderBy('label')
            ->get()
            ->toArray();

        return $treatmentTypes;
    }

    /**
     * @return array
     */
    private function getFilterValues(): array
    {
        return [
            'providers' => $this->getProviders(),
            'age_groups' => $this->getAgeGroups(),
            'types_of_clients' => $this->getClientTypes(),
            'insurances' => $this->getInsurances(),
            'kaiser_types' => $this->getKaiserTypes(),
            'visit_types' => [
                ['id' => 1, 'label' => 'In Person'],
                ['id' => 2, 'label' => 'Virtual'],
            ],
            'availability_types' => $this->getAvailabilityTypes(),
            'availability_subtypes' => $this->getAvailabilitySubtypes(),
            'ethnicities' => $this->getEthnicities(),
            'languages' => $this->getLanguages(),
            'patient_categories' => $this->getPatientCategories(),
            'races' => $this->getRaces(),
            'specialties' => $this->getSpecialties(),
            'treatment_types' => $this->getTreatmentTypes(),
        ];
    }
}
