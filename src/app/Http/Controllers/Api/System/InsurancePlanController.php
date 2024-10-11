<?php

namespace App\Http\Controllers\Api\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Insurance\Plan\UpdateRequest;
use App\PatientInsurancePlan;

class InsurancePlanController extends Controller
{
    public function update(UpdateRequest $request, PatientInsurancePlan $plan)
    {
        $plan->update([
            'need_collect_copay_for_telehealth' => $request->input('need_collect_copay_for_telehealth'),
            'is_verification_required' => $request->input('is_verification_required'),
            'requires_reauthorization_document' => $request->input('requires_reauthorization_document') ?? $plan->requires_reauthorization_document,
            'reauthorization_notification_visits_count' => $request->input('reauthorization_notification_visits_count') ?? $plan->reauthorization_notification_visits_count,
            'reauthorization_notification_days_count' => $request->input('reauthorization_notification_days_count') ?? $plan->reauthorization_notification_days_count,
        ]);

        $plan->childPlans()->update([
            'need_collect_copay_for_telehealth' => $request->input('need_collect_copay_for_telehealth'),
            'is_verification_required' => $request->input('is_verification_required'),
            'requires_reauthorization_document' => $request->input('requires_reauthorization_document') ?? $plan->requires_reauthorization_document,
            'reauthorization_notification_visits_count' => $request->input('reauthorization_notification_visits_count') ?? $plan->reauthorization_notification_visits_count,
            'reauthorization_notification_days_count' => $request->input('reauthorization_notification_days_count') ?? $plan->reauthorization_notification_days_count,
        ]);

        $plan->load(['childPlans']);

        return response()->json([
            'plan' => $plan,
        ]);
    }
}