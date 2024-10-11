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

class GetDoctorsAvailability implements ShouldQueue
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
            } else {
                return [
                    'filtersValues' => $filtersValues
                ];
            }
        }

        $workHours = \Bus::dispatchNow(new GetProvidersAvailability($this->startDate, $this->endDate, $this->data->all()));
        foreach ($workHours as $workHour) {
            $hour = $workHour->date->hour;
            $hours = ((int)($workHour->length / 60)) + ($workHour->length % 60
                > 0 ? 1 : 0);

            for ($i = 0; $i < $hours; $i++) {
                $s_t = (($hour + $i) < 10 ? '0' : '') . (($hour + $i) > 24 ? '24' : ($hour + $i)) . ':00:00';
                $infoWorkHours[$workHour->date->toDateString()][$s_t][$workHour->office_id][]
                    = $workHour->toArray();
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
                Carbon::parse(array_keys($infoWorkHours)[0])->timestamp
            )
            ->where(
                'time',
                '<=',
                Carbon::parse(array_keys($infoWorkHours)[count($infoWorkHours)
                    - 1])->endOfDay()->timestamp
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
            $date = Carbon::createFromTimestamp($appointment->time);

            //            $startTime = str_pad($date->hour, 2, '0', STR_PAD_LEFT) . ':00:00';
            $tmp = [];
            $hours = ((int)($appointment->visit_length / 60))
                + ($appointment->visit_length % 60 > 0 ? 1 : 0);

            for ($i = 0; $i < $hours; $i++) {
                $s_t = (($date->hour + $i) < 10 ? '0' : '') . ($date->hour + $i) . ':00:00';
                $tmp[$s_t] = [];

                foreach ($infoWorkHours[$date->toDateString()][$s_t][$appointment->offices_id]
                    as $k => $providerTimes) {
                    if ($appointment->providers_id == $providerTimes['provider_id']) {
                        array_splice($infoWorkHours[$date->toDateString()][$s_t][$appointment->offices_id], $k, 1);

                        if (!array_key_exists(
                            $appointment->offices_id,
                            $infoAppointments
                        )) {
                            $infoAppointments[$appointment->offices_id] = [];
                        }
                        if (!array_key_exists(
                            $appointment->providers_id,
                            $infoAppointments[$appointment->offices_id]
                        )) {
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id]
                                = [];
                        }
                        if (!array_key_exists(
                            $date->toDateString(),
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id]
                        )) {
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date->toDateString()]
                                = [];
                        }

                        if (!array_key_exists(
                            $s_t,
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date->toDateString()]
                        )) {
                            $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date->toDateString()][$s_t]
                                = [];
                        }

                        $infoAppointments[$appointment->offices_id][$appointment->providers_id][$date->toDateString()][$s_t][]
                            = $appointment->toArray();
                    }
                }
            }
        }

        $events = [];
        foreach ($infoWorkHours as $date => $hours) {
            foreach ($hours as $hour => $offices) {

                $totalAvailabilityCount = 0;
                $info = [];
                $popover = '<div class="d-flex flex-column" style="gap: 5px">';

                foreach ($offices as $office_id => $providers) {
                    if (count($providers) > 0) {
                        $totalAvailabilityCount += count($providers);
                        $info = array_merge($info, $providers);

                        foreach ($providers as $provider) {
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
                            if (isset($provider['availability_subtype']) && isset($provider['availability_subtype']['hex_color'])) {
                                $hexColor = $provider['availability_subtype']['hex_color'];
                            }

                            $popover .=  "<span class='badge' style='background-color: $hexColor; font-size: 10px'>" . $providerName . $icons . '</span>';
                        }
                    }
                }

                $popover .= '</div>';

                if ($totalAvailabilityCount) {
                    $description = "Availability: $totalAvailabilityCount";

                    $events[] = [
                        'id' => time(),
                        'description' => $description,
                        'title' => "",
                        'allDay' => false,
                        'start' => Carbon::parse($date . ' ' . $hour)->toDateTimeString(),
                        'end' => Carbon::parse($date . ' ' . $hour)->addHour(1)->toDateTimeString(),
                        'forceEventDuration' => true,
                        'editable' => false,
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
        } else {
            return compact('events', 'appointments', 'filtersValues');
        }
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
            for ($j = 0; $j <= 24; $j++) {
                $timeString = str_pad($j, 2, '0', STR_PAD_LEFT) . ':00:00';
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
