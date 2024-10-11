<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 23.10.2017
 * Time: 16:02
 */

namespace App\Traits\Filters;


use App\Provider;

trait ProviderScope
{
    public function scopeWhereProviderAgeGroups($query, $ageGroupIdAll)
    {
        if ($ageGroupIdAll === 0 || array_search(0, $ageGroupIdAll) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_age_group', 'therapist_survey.id', '=','therapist_has_age_group.therapist_id')
                ->join('therapist_survey_age_groups', 'therapist_has_age_group.age_group_id','=','therapist_survey_age_groups.id')
                ->whereIn('therapist_survey_age_groups.id', $ageGroupIdAll);
        }
    }

    public function scopeWhereProviderTypesOfClients($query, $typesOfClientsIdAll)
    {
        if ($typesOfClientsIdAll === 0 || array_search(0, $typesOfClientsIdAll) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_client_type', 'therapist_survey.id', '=','therapist_has_client_type.therapist_id')
                ->join('therapist_survey_type_of_clients', 'therapist_has_client_type.client_type_id','=','therapist_survey_type_of_clients.id')
                ->whereIn('therapist_survey_type_of_clients.id', $typesOfClientsIdAll);
        }
    }


    public function scopeWhereProviderFocus($query, $focusIdAll)
    {
        if ($focusIdAll === 0 || array_search(0, $focusIdAll) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_focus', 'therapist_survey.id', '=','therapist_has_focus.therapist_id')
                ->join('therapist_survey_practice_focus', 'therapist_has_focus.focus_id','=','therapist_survey_practice_focus.id')
                ->whereIn('therapist_survey_practice_focus.id', $focusIdAll);
        }
    }

    public function scopeWhereVisitType($query, $visitTypes)
    {
        if ($visitTypes === 0 || array_search(0, $visitTypes) !== false) {
            return $query;
        } else {
            return $query->when(array_search(1, $visitTypes) !== false && array_search(2, $visitTypes) === false, function($query) {
                $query->where('availabilities.in_person', true);
            })->when(array_search(2, $visitTypes) !== false && array_search(1, $visitTypes) === false, function($query) {
                $query->where('availabilities.virtual', true);
            });
        }
    }

    public function scopeWhereProviderEthnicities($query, $ethnicities)
    {
        if ($ethnicities === 0 || array_search(0, $ethnicities) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_ethnicities', 'therapist_survey.id', '=','therapist_has_ethnicities.therapist_id')
                ->join('therapist_survey_ethnicities', 'therapist_has_ethnicities.ethnicity_id','=','therapist_survey_ethnicities.id')
                ->whereIn('therapist_survey_ethnicities.id', $ethnicities);
        }
    }

    public function scopeWhereProviderLanguages($query, $languages)
    {
        if ($languages === 0 || array_search(0, $languages) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_languages', 'therapist_survey.id', '=','therapist_has_languages.therapist_id')
                ->join('therapist_survey_languages', 'therapist_has_languages.language_id','=','therapist_survey_languages.id')
                ->whereIn('therapist_survey_languages.id', $languages);
        }
    }

    public function scopeWhereProviderPatientCategories($query, $patientCategories)
    {
        if ($patientCategories === 0 || array_search(0, $patientCategories) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_patient_categories', 'therapist_survey.id', '=','therapist_has_patient_categories.therapist_id')
                ->join('therapist_survey_patient_categories', 'therapist_has_patient_categories.patient_category_id','=','therapist_survey_patient_categories.id')
                ->whereIn('therapist_survey_patient_categories.id', $patientCategories);
        }
    }

    public function scopeWhereProviderRaces($query, $races)
    {
        if ($races === 0 || array_search(0, $races) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_races', 'therapist_survey.id', '=','therapist_has_races.therapist_id')
                ->join('therapist_survey_races', 'therapist_has_races.race_id','=','therapist_survey_races.id')
                ->whereIn('therapist_survey_races.id', $races);
        }
    }

    public function scopeWhereProviderSpecialties($query, $specialties)
    {
        if ($specialties === 0 || array_search(0, $specialties) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_specialties', 'therapist_survey.id', '=','therapist_has_specialties.therapist_id')
                ->join('therapist_survey_specialties', 'therapist_has_specialties.specialty_id','=','therapist_survey_specialties.id')
                ->whereIn('therapist_survey_specialties.id', $specialties);
        }
    }

    public function scopeWhereProviderTreatmentTypes($query, $treatmentTypes)
    {
        if ($treatmentTypes === 0 || array_search(0, $treatmentTypes) !== false) {
            return $query;
        } else {
            return $query->join('therapist_has_treatment_types', 'therapist_survey.id', '=','therapist_has_treatment_types.therapist_id')
                ->join('therapist_survey_treatment_types', 'therapist_has_treatment_types.treatment_type_id','=','therapist_survey_treatment_types.id')
                ->whereIn('therapist_survey_treatment_types.id', $treatmentTypes);
        }
    }
}