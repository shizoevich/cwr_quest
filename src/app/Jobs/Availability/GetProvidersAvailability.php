<?php

/**
 * Created by PhpStorm.
 * User: eremenko_aa
 * Date: 22.04.2018
 * Time: 14:33
 */

namespace App\Jobs\Availability;


use App\Availability;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GetProvidersAvailability extends GetProviderWorkHours
{
    /**
     * @var Collection
     */
    private $data;

    /**
     * GetProvidersAvailability constructor.
     *
     * @param $startDate
     * @param $endDate
     * @param array $data
     * @param bool $withAppointments
     */
    public function __construct($startDate, $endDate, $data, $withAppointments = true)
    {
        $this->data = collect($data);
        parent::__construct($startDate, $endDate, $withAppointments);
    }

    public function handle()
    {
        $events = $this->getWorkHours();
        $dataset = [];
        $existsEventIds = [];

        foreach ($events as $event) {
            $event->date = $this->getNearEventDate($event);
            if (is_null($event->date)) {
                continue;
            }
            if (!in_array($event->id, $existsEventIds)) {
                $existsEventIds[] = $event->id;
                $dataset[] = $event;
            }
        }

        return collect($dataset);
    }

    protected function getWorkHours($type = 'all')
    {
        $request = $this->data;
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        $data = Availability::with(['provider', 'officeRoom', 'provider.insurances', 'availabilityType', 'availabilitySubtype'])
            ->select([
                'availabilities.*',
            ])
            ->withTherapistSurvey()
            ->whereProvider($request->has('provider_id')
                ? intval($request->get('provider_id')) : 0)
            ->whereOffice($request->has('office_id')
                ? intval($request->get('office_id')) : 0)
            ->whereProviderAgeGroups($request->has('age_group_id_all')
                ? $request->get('age_group_id_all') : 0)
            ->whereProviderTypesOfClients($request->has('types_of_clients_id_all')
                ? $request->get('types_of_clients_id_all') : 0)
            ->whereInsurance($request->has('insurance_id')
                ? intval($request->get('insurance_id')) : 0)
            ->whereVisitType($this->data->has('visit_types')
                ? $this->data->get('visit_types') : 0)
            ->whereAvailabilityTypes($request->has('availability_types')
                ? $request->get('availability_types') : 0)
            ->whereAvailabilitySubtypes($request->has('availability_subtypes')
                ? $request->get('availability_subtypes') : 0)
            ->whereProviderKaiserTypes($request->has('kaiser_types')
                ? $request->get('kaiser_types') : 0)
            ->whereProviderEthnicities($request->has('ethnicities_id_all')
                ? $request->get('ethnicities_id_all') : 0)
            ->whereProviderLanguages($request->has('languages_id_all')
                ? $request->get('languages_id_all') : 0)
            ->whereProviderPatientCategories($this->data->has('patient_categories_id_all')
                ? $this->data->get('patient_categories_id_all') : 0)
            ->whereProviderRaces($request->has('races_id_all')
                ? $request->get('races_id_all') : 0)
            ->whereProviderSpecialties($request->has('specialties_id_all')
                ? $request->get('specialties_id_all') : 0)
            ->whereProviderTreatmentTypes($request->has('treatment_types_id_all')
                ? $request->get('treatment_types_id_all') : 0)
            ->when($this->startDate instanceof Carbon, function ($query) use (&$startDate) {
                $query->where(function ($query) use (&$startDate) {
                    $query->orWhereDate('availabilities.start_date', '>=', $startDate->toDateString());
                });
            })->when($endDate instanceof Carbon, function ($query) use (&$endDate) {
                $query->whereDate('availabilities.start_date', '<', $endDate->toDateString());
            })
            ->whereNotNull('availabilities.start_date')
            ->groupBy('availabilities.id')
            ->orderBy('availabilities.provider_id')
            ->get();

        return $data;
    }
}
