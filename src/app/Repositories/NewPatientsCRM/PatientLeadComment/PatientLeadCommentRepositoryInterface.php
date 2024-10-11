<?php

namespace App\Repositories\NewPatientsCRM\PatientLeadComment;

use App\Models\Patient\Lead\PatientLeadComment;
use Illuminate\Support\Collection;

interface PatientLeadCommentRepositoryInterface
{
    public function update(PatientLeadComment $comment, array $data): PatientLeadComment;

    public function delete(PatientLeadComment $comment): void;
}