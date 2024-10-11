<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateUserSignature;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Provider;

class UsersController extends Controller {

    public function registrationComplete() {
        if(Auth::check() && Auth::user()->isProviderAttached()
            && !empty(Auth::user()->meta->signature) && Auth::user()->hasRole('provider')) {
            return redirect()->route('vue-chart');
        }
        if(Auth::guest()) {
            return redirect()->route('home');
        } else {
//            Auth::logout();
            return view('auth.register-complete');
        }
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showReassignProvider() {
        $user = Auth::user();
        if(!$user->meta->has_access_rights_to_reassign_page) {
            abort(403);
        }
        $providers = Provider::orderBy('provider_name')->get();

        return view('dashboard.doctors.assign-admin', compact('providers', 'user'));
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reassignProvider(Request $request) {
        $this->validate($request, [
            'provider_id' => 'required|numeric|exists:providers,id',
        ]);

        $user = Auth::user();
        if(!$user->meta->has_access_rights_to_reassign_page) {
            abort(403);
        }

        $providerMeta = User::where('provider_id', $request->provider_id)->first();
        $user->provider_id = $request->provider_id;
        $user->save();

        if($providerMeta) {
            $providerMeta = $providerMeta->meta;
            if(is_null($providerMeta->signature)) {
                \Bus::dispatchNow(new GenerateUserSignature($user->id));
            } else {
                $user->meta->signature = $providerMeta->signature;
                $user->meta->save();
            }
        } else {
            \Bus::dispatchNow(new GenerateUserSignature($user->id));
        }

        return redirect()->back()->with(['message' => 'You has successfully reassigned.']);
    }

    public function isPasswordOutdated() {
        $maxDateDiff = config('app.password_days_lifetime');
        $user = Auth::user();
        $passwordUpdatedAt = $user->password_updated_at;
        $dateDiff = $maxDateDiff + 1;
        $daysLeft = 0;
        if(!is_null($passwordUpdatedAt)) {
            $now = Carbon::now();
            $dateDiff = $passwordUpdatedAt->diffInDays($now, false);
            if($dateDiff >= 0 && $dateDiff <= $maxDateDiff) {
                $daysLeft = $maxDateDiff - $dateDiff;
            } else if($dateDiff > $maxDateDiff) {
                $daysLeft = 0;
            } else {
                $daysLeft = $maxDateDiff;
            }
        }

        $lastChangePasswordDate = 'n/a';
        $nextChangePasswordDate = 'n/a';
        if(!is_null($user->password_updated_at)) {
            $lastChangePasswordDate = $user->password_updated_at->format('m/d/Y');
            $nextChangePasswordDate = clone $user->password_updated_at;
            $nextChangePasswordDate = $nextChangePasswordDate->addDays($maxDateDiff)->format('m/d/Y');
        }

        return response([
            'outdated' => ($dateDiff >= $maxDateDiff),
            'days_left' => $daysLeft,
            'last_change_password_date' => $lastChangePasswordDate,
            'next_change_password_date' => $nextChangePasswordDate,
        ]);
    }

    /**
     * @return bool
     */
    public function isAdmin() 
    {
        return Auth::user()->isAdmin();
    }

    /**
     * @return bool
     */
    public function isOnlyAdmin()
    {
        return Auth::user()->isOnlyAdmin();
    }
    
    /**
     * @return bool
     */
    public function isAudit() 
    {
        return response()->json([
            'audit' => Auth::user()->isInsuranceAudit(),
        ]);
    }

    /**
     * @return int
     */
    public function isSecretary() 
    {
        return Auth::user()->isSecretary();
    }

    public function isPatientRelationManager()
    {
        return Auth::user()->isPatientRelationManager();
    }

    public function isSupervisorOrAdmin()
    {
        $user = Auth::user();
        return intval($user->isSupervisor() || $user->isAdmin() || $user->isSecretary());
    }

    public function getUserRoles()
    {
        $user = Auth::user();
        return response()->json([
            'isAdmin' => (int) $user->isAdmin(),
            'isOnlyAdmin' => (int) $user->isOnlyAdmin(),
            'isAudit' => (int) $user->isInsuranceAudit(),
            'isSecretary' => (int) $user->isSecretary(),
            'isPatientRelationManager' => (int) $user->isPatientRelationManager(),
            'isSupervisorOrAdmin' => (int) ($user->isSupervisor() || $user->isAdmin() || $user->isSecretary()),
        ]);
    }

    public function getUserMeta()
    {
        $user = \Auth::user();
        $meta = $user->meta;
        $meta->role = $user->roles()->first();
        return response()->json($meta);
    }

    /**
     * Method is used to reset auto logout time.
     *
     * @return \Illuminate\Http\Response
     */
    public function emptyRequest() {
        return response()->json();
    }
}
