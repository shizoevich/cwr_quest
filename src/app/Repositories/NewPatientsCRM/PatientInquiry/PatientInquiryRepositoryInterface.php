<?php

namespace App\Repositories\NewPatientsCRM\PatientInquiry;

use App\Appointment;
use App\Models\Patient\Inquiry\PatientInquiry;
use App\PatientComment;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PatientInquiryRepositoryInterface
{
    public function create(array $data, ?int $stageId = null, ?bool $isInquirableReturning = null, ?bool $forbidInquirableUpdate = null): PatientInquiry;

    public function update(array $data);

    public function getInquiries(array $params, bool $archived = false): array;

    public function changeStage(PatientInquiry $inquiry, array $data, ?string $reason = null): PatientInquiry;

    public function createPatientFromPatientLead(PatientInquiry $inquiry): PatientInquiry;

    public function archive(PatientInquiry $inquiry, array $data): PatientInquiry;

    public function close(PatientInquiry $inquiry): PatientInquiry;

    public function getComments(PatientInquiry $inquiry): LengthAwarePaginator;

    public function createComment(PatientInquiry $inquiry, array $data);

    public function createInitialSurveyComment(PatientInquiry $inquiry, array $data): PatientComment;

    public function createSecondSurveyComment(PatientInquiry $inquiry, array $data): PatientComment;

    public function getCompletedInitialAppointment(PatientInquiry $inquiry): Appointment;

    public function createOnboardingCompleteComment(PatientInquiry $inquiry, array $data): PatientComment;
}