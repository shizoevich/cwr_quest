<?php
/**
 * Created by PhpStorm.
 * User: braginec_dv
 * Date: 30.09.2017
 * Time: 17:19
 */

namespace App\Http\Controllers\Utils;

use App\Models\Patient\PatientElectronicDocument;
use App\Models\PatientHasProvider;
use App\Scopes\PatientDocuments\DocumentsForAllScope;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\PatientDocument;
use Illuminate\Support\Facades\DB;

trait AccessUtils
{
    /**
     * @param $documentName
     *
     * @return bool
     */
    public function isUserHasAccessRightsForDocument($documentName) {
        if(Auth::check() && !Auth::user()->isAdmin()) {
            $authProvider = Auth::user()->provider;
            $document = PatientDocument::withoutGlobalScope(DocumentsForAllScope::class)
                ->where('aws_document_name', $documentName)
                ->first();

            if($document->only_for_admin && !Auth::user()->isInsuranceAudit()) {
                abort(403);
            }

            if ($authProvider->checkSupervisorAccessToPatient($document->patient_id)) {
                return true;
            }

            $providersHasAccess = $document->patient->providers;
            foreach($providersHasAccess as $provider) {
                if($provider->id == $authProvider->id) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * @param $document
     *
     * @return bool
     * @internal param $id
     *
     */
    public function isUserHasAccessRightsForElectronicDocument($document) {
        if(Auth::check()) {
            if(Auth::user()->isAdmin()) {
                return true;
            }
            /** @var @var User $user */
            $user = auth()->user();
            $authProviderID = $user->provider_id;

            if(!($document instanceof PatientElectronicDocument)) {
                $document = PatientElectronicDocument::find($document);
            }

            $providerHasAccess = $document->patient->providers()->where('id', $authProviderID)->first();
            return !is_null($providerHasAccess);
        }

        return false;
    }

    /**
     * @param $patientId
     * @param User|null $user
     * @param bool $readOnlyHasAccess
     *
     * @return bool
     */
    public function isUserHasAccessRightsForPatient($patientId, User $user = null, $readOnlyHasAccess = false) {
        if(is_null($user)) {
            $user = Auth::user();
        }
        if(!$user->isAdmin()) {
            $authProviderID = $user->provider->id;
            if(is_null($authProviderID)) {
                return false;
            }
            $providersHasAccess = PatientHasProvider::where('patients_id', $patientId)
                                    ->where('providers_id', $authProviderID)
                                    ->when(!$readOnlyHasAccess, function($query) {
                                        $query->where('chart_read_only', false);
                                    })->count();

            return ($providersHasAccess > 0);
        } else {
            return true;
        }
    }

    /**
     * @param $date
     *
     * @return array
     */
    public function isElectronicDocumentEditingAllowed($date) {

        $now = Carbon::now();

        $documentCreatedDate = Carbon::parse($date);
        $hoursdiff = $documentCreatedDate->diffInHours($now, false);
        $allowed = false;
        $hours = 0;
        if($hoursdiff <= config('app.allowed_note_editing_depth')) {
            $allowed = true;
            $hours = config('app.allowed_note_editing_depth') - $hoursdiff;
        }
        if($hours <= 0) {
            $hours = 0;
            $allowed = false;
        }

        return [
            'allowed' => $allowed,
            'hours' => $hours,
        ];
    }
}