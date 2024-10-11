<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Therapist\TherapistSurveyLanguage;
use App\Models\Therapist\TherapistSurveySpecialty;
use App\Models\Therapist\TherapistSurveyTreatmentType;
use App\PatientInsurance;
use App\Role;
use App\TherapistSurveyAgeGroup;
use App\TherapistSurveyTypeOfClient;
use App\User;
use Illuminate\Http\Request;

class TherapistsController extends Controller
{
    /**
     * Show therapists page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('dashboard.therapists.index');
    }

    public function indexApi(Request $request) {
        $data = $request->all();

        $providerRoleId = Role::getRoleId('provider');
        
        $query = User::query()
            ->with([
                'roles',
                'therapistSurvey.insurances',
                'therapistSurvey.specialties',
                'therapistSurvey.ageGroups',
                'therapistSurvey.treatmentTypes',
                'therapistSurvey.typesOfClients',
                'therapistSurvey.languagesTridiuum',
            ])
            ->select('users.*', 'providers.provider_name as provider_name')
            ->join('providers', 'users.provider_id', '=', 'providers.id')
            ->whereHas('roles', function($query) use ($providerRoleId) {
                $query->where('role_id', $providerRoleId);
            })
            ->whereDoesntHave('roles', function($query) use ($providerRoleId) {
                $query->where('role_id', '<>', $providerRoleId);
            });

        $query = $this->applyFilters($query, $data);

        $query->orderBy('created_at', 'desc');
        $users = $query->get();
    
        $usersObject = $users->map(function ($user, $key) {
            return $this->transformUser($user, $key);
        });
    
        return response()->json($usersObject);
    }
    
    private function applyFilters($query, $data)
    {
        if (!empty($data['provider_id'])) {
            $query->where('users.provider_id', '=', $data['provider_id']);
        }

        $filters = [
            'insurances' => 'therapistSurvey.insurances',
            'specialties' => 'therapistSurvey.specialties',
            'clientFocus' => 'therapistSurvey.ageGroups',
            'typeOfTherapy' => 'therapistSurvey.treatmentTypes',
            'modality' => 'therapistSurvey.typesOfClients',
            'languages' => 'therapistSurvey.languagesTridiuum'
        ];

        foreach ($filters as $key => $relation) {
            if (!empty($data[$key])) {
                $query->whereHas($relation, function ($query) use ($data, $key) {
                    $query->whereIn('id', $data[$key]);
                }, '=', count($data[$key]));
            }
        }

        return $query;
    }

    private function transformUser($user, $key)
    {
        $userObject = $user->toArray();
        $userObject['is_active_status'] = !$user->deleted_at;

        if ($user->therapistSurvey) {
            $userObject['therapist_survey'] = [
                'insurances' => $user->therapistSurvey->insurances->pluck('insurance')->implode('; ') ?: '-',
                'specialties' => $user->therapistSurvey->specialties->pluck('label')->implode('; ') ?: '-',
                'age_groups' => $user->therapistSurvey->ageGroups->pluck('label')->implode('; ') ?: '-',
                'treatment_types' => $user->therapistSurvey->treatmentTypes->pluck('label')->implode('; ') ?: '-',
                'types_of_clients' => $user->therapistSurvey->typesOfClients->pluck('label')->implode('; ') ?: '-',
                'languages_tridiuum' => $user->therapistSurvey->languagesTridiuum->pluck('label')->implode('; ') ?: '-',
            ];
        }

        return $userObject;
    }

    public function getFiltersOptions()
    {
        $insurances = PatientInsurance::query()
            ->select(['id', 'insurance'])
            ->orderBy('insurance')
            ->get();
        $specialties = TherapistSurveySpecialty::all();
        $ageGroups = TherapistSurveyAgeGroup::all();
        $treatmentTypes = TherapistSurveyTreatmentType::all();
        $typesOfClients = TherapistSurveyTypeOfClient::all();
        $languages = TherapistSurveyLanguage::all();

        return response([
            'insurances' => $insurances,
            'specialties' => $specialties,
            'ageGroups' => $ageGroups,
            'treatmentTypes' => $treatmentTypes,
            'typesOfClients' => $typesOfClients,
            'languages' => $languages,
        ]);
    }
}
