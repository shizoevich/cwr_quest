<?php

namespace App\Repositories\Patient;

use App\Patient;
use App\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Requests\Patient\Api\UpdateRequest;
use App\Http\Requests\Api\ReauthorizationRequestDashboard\UpdateAuthNumberRequest;
use App\Http\Requests\Patient\Api\UpdateSecondaryEmail as UpdateSecondaryEmailRequest;
use App\Http\Requests\Patient\Api\UpdatePatientVisitFrequency as UpdatePatientVisitFrequencyRequest;
use App\PatientAlert;
use Illuminate\Database\Eloquent\Collection;

interface PatientRepositoryInterface
{
    /**
     * @param int $limit
     * @param string|null $searchQuery
     * @param User $user
     * @return LengthAwarePaginator
     */
    public function all(int $limit, $searchQuery, User $user): LengthAwarePaginator;

    /**
     * @param array $data
     * @return Patient
     */
    public function create(array $data): Patient;

    /**
     * @param array $data
     * @param Patient $patient
     * @return Patient
     */
    public function update(array $data, Patient $patient): Patient;
    
    /**
     * @param array $data
     * @param Patient $patient
     * @return Patient
     */
    public function updateAttachedProviders(array $data, Patient $patient): Patient;

    /**
     * @param Patient $patient
     * @return array
     */
    public function show(Patient $patient): array;

    public function updateAuthNumber(UpdateAuthNumberRequest $request);

    public function updatePatientLanguagePrefer(UpdateRequest $request);

    public function addPatientSecondaryEmail(UpdateSecondaryEmailRequest $request);

    public function updatePatientVisitFrequency(UpdatePatientVisitFrequencyRequest $request);

    public function addPatientAlertData(array $alertData): PatientAlert;

    public function getPatientsWithoutUpcomingAppointments(array $filters = []): Collection;

    public function patientNotesWithDocumentsCount($patientId):array;

    public function checkIsSynchronized(Patient $patient): bool;
}
