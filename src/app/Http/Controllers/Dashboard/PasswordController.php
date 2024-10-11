<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\ChangePassword;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function form()
    {
        return view('password.form', [
            'user' => \Auth::user(),
        ]);
    }

    /**
     * @param ChangePassword $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function store(ChangePassword $request)
    {
        if(Hash::check($request->get('password'), \Auth::user()->password)) {
            return redirect()->route('change-password.form')->withErrors(['password' => 'Old passwords cannot be used']);
        } else {
            \Auth::user()->forceFill([
                'password' => bcrypt($request->get('password')),
                'password_updated_at' => Carbon::now(),
            ])->save();

            return redirect()->route('vue-chart')->with('password-successfully-changed', 'Password changed successfully.');
        }
    }
}
