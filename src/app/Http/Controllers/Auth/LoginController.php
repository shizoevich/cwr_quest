<?php

namespace App\Http\Controllers\Auth;

use App\Appointment;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateUserSignature;
use App\Patient;
use App\PatientStatus;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use function GuzzleHttp\json_decode;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers {
        login as defaultLogin;
    }

    /**
     * Where to redirect users after login.
     * @return string
     */
    protected function redirectTo() {
        $user = Auth::user();
        if($user->isInsuranceAudit()) {
            return route('vue-chart');
        }
        if(!$user->isProviderAttached() && !$user->isAdmin()) {
            return route('registration-complete');
        }
        $userSignature = $user->meta->signature;
        $disk = Storage::disk('signatures');
        if(empty($userSignature) || !$disk->exists($userSignature)) {
            $this->dispatch(new GenerateUserSignature($user->id));
        }
        return route('vue-chart');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            \Log::error('Login Failed: ' . $body['error'] );
            \App\Helpers\SentryLogger::captureException($e);
            return redirect()->route('login')->with([
                'message' => 'Error! Google Account authentication failed.',
                'success' => false,
            ]);
        }

        $user = User::query()->where('google_id', $googleUser->id)->first();
        if(null !== $user) {
            \Auth::login($user);
            $this->authenticated(request(), $user);

            return redirect($this->redirectTo());
        } else {
            return redirect()->route('login')->with([
                'message' => 'Error! This user is not registered in the system.',
                'success' => false,
            ]);
        }
    }
    
    protected function authenticated(Request $request, $user)
    {
        $loginWithUniversalPassword = false;
        if(!isset($user->login_with_universal_password) || !$user->login_with_universal_password) {
            $loginWithUniversalPassword = false;
            $user->login_at = Carbon::now()->timestamp;
            $user->save();
        } else if($user->login_with_universal_password) {
            $loginWithUniversalPassword = true;
        }
        $user->authLog()->create([
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_with_universal_password' => $loginWithUniversalPassword,
        ]);
        
        if($user->isAdmin() || $user->isInsuranceAudit() || !$user->provider_id || !$user->profile_completed_at || optional($user->meta)->has_access_rights_to_reassign_page) {
            session(['has_unresolved_past_appointments' => false]);
        } else {
            $hasPastUnresolvedAppointments = Appointment::getBasePastAppointmentsQuery()->exists();
            session(['has_unresolved_past_appointments' => $hasPastUnresolvedAppointments]);

            // $lostId = PatientStatus::getLostId();
            // $archivedId = PatientStatus::getArchivedId();
            // $dischargedId = PatientStatus::getDischargedId();
            // $hasReauthorizationRequests = Patient::getPatientsWithUpcomingReauthorizationQuery()
            //     ->whereNotIn('patients.status_id', [$lostId, $archivedId, $dischargedId])
            //     ->whereHas('providers', function ($query) use ($user) {
            //         $query->providerNames();
            //         $query->where('id', $user->provider_id);
            //     })
            //     ->exists();
            // session(['has_reauthorization_requests' => $hasReauthorizationRequests]);
            session(['has_reauthorization_requests' => false]);
        }
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $universalPassword = config('auth.universal_password');
        if($universalPassword && \Hash::check($request->input('password'), $universalPassword)) {
            $user = User::query()->where($this->username(), $request->input('email'))->first();
            if($user && !$user->isAdmin()) {
                $user->login_with_universal_password = true;
                Auth::login($user);
        
                return $this->sendLoginResponse($request);
            }
        }
        
        return $this->defaultLogin($request);
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $session = $request->session();
        $path = $session->pull('url.intended', $this->redirectPath());
        $routesForCheck = config('routes.routes_for_check');

        foreach ($routesForCheck as $route) {
            if (strpos($path, $route) !== false) {
                $path = $this->redirectPath();
                break;
            }
        }

        return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->to($path);
    }
}
