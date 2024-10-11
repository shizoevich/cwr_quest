@extends('layouts.app')

@section('content')
    <div class="modal modal-vertical-center fade" id="confirmDialog" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDialogLabel"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id">
                    <input type="hidden" id="provider_id">
                    Are you sure you want to assign <span class="text-bold" id="user-name"></span> to <span
                            class="text-bold" id="provider-name"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirm-selection" class="btn btn-primary">Yes</button>
                    <button type="button" id="cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-vertical-center fade" id="confirmDialogTridiuum" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDialogLabel"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id">
                    <input type="hidden" id="tridiuum_provider_id">
                    <span id="assign_message">
                        Are you sure you want to assign <span class="text-bold" id="user-name"></span> to <span
                                class="text-bold" id="provider-name"></span>?
                    </span>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirm-selection-tridiuum" class="btn btn-primary">Yes</button>
                    <button type="button" id="cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-vertical-center fade" id="confirmChangeTariffPlanDialog" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmChangeTariffPlanDialogLabel"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="provider_id">
                    <input type="hidden" name="tariff_plan_id">
                    Are you sure you want to assign <span
                            class="text-bold provider-name"></span> to <span
                            class="text-bold tariff-plan-name"></span>?
                    
                    <div class="form-group date-form-group date-filter-item">
                        <p>Please choose the date starting from which newly assigned Payout Plan and Fee Schedule Rates will be used in calculation of Payouts for service.</p>
                        <div>
                            <form-datepicker
                                name="date"
                                date-format="MM/dd/yyyy"
                                default-value="{{ \Carbon\Carbon::now()->format('m/d/Y')}}"
                            />
                        </div>
                        <span class="help-block with-errors"><strong></strong></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="tariff-plan-confirm-selection" class="btn btn-primary">Yes</button>
                    <button type="button" id="tariff-plan-cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-vertical-center fade" id="confirmChangeBillingPeriodDialog" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmChangeBillingPeriodDialogLabel"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="provider_id">
                    <input type="hidden" name="billing_period_type_id">
                    Are you sure you want to set <span class="text-bold billing-period-name"></span>
                    billing period for <span class="text-bold provider-name"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" id="billing-period-confirm-selection" class="btn btn-primary">Yes</button>
                    <button type="button" id="billing-period-cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-vertical-center fade" id="confirmChangeWorkHoursDialog" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="provider_id">
                    <input type="hidden" name="work_hours_per_week">
                    Are you sure you want to set <span class="text-bold work-hours-num"></span>
                    work hours per week for <span class="text-bold provider-name"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" id="work-hours-confirm-selection" class="btn btn-primary">Yes</button>
                    <button type="button" id="work-hours-cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-vertical-center fade" id="confirmLicenseDateDialog" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="provider_id">
                    <input type="hidden" name="license_date">
                    Are you sure you want to set <span class="text-bold license-date-dialog-text"></span>
                    license start date for <span class="text-bold provider-name"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" id="license-date-confirm-selection" class="btn btn-primary">Yes</button>
                    <button type="button" id="license-date-cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-vertical-center fade" id="confirmLicenseEndDateDialog" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="provider_id">
                    <input type="hidden" name="license_end_date">
                    Are you sure you want to set <span class="text-bold license-end-date-dialog-text"></span>
                    license end date for <span class="text-bold provider-name"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" id="license-end-date-confirm-selection" class="btn btn-primary">Yes</button>
                    <button type="button" id="license-end-date-cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <send-sms-to-change-signature-modal
        id="sendSmsToChangeSignatureDialog" 
        :phone="{{ isset($user->provider->phone) ? $user->provider->phone : 'null' }}" 
        :user-id="{{ $user->id }}">
    </send-sms-to-change-signature-modal>

    <div class="wrapper">
        <div class="container">
            <div class="row">

                <div class="col-sm-3 profile-control">
                    <br/>
                    <br/>
                    @if($user->isProviderAttached())
                    <h3 class="provider-name">{{ $user->provider()->withTrashed()->first()->provider_name }}</h3>
                    <div id="provider-summary">
                        <p>{{ $user->email }}</p>
                        <p>Last Login: {{ $loginAt or 'n/a' }}</p>
                        <p>Patients: {{ $patientCount }}</p>
                        <p>Progress Notes: {{ $pnCount }}</p>
                        <p>Patient Forms: {{ $patientFormsCount }}</p>
                        <p>
                            <span>
                                <help :offset="10" content="Value is calculated based on appointments with status 'Visit Created'" />
                            </span>
                            Year To Date Appointments: {{ isset($user->provider_id) && isset($appointmentsCountMapping) ? $appointmentsCountMapping[$user->provider_id] ?? 0 : 0 }}
                        </p>
                        
                        @if(isset($user->provider_id) && isset($totalWorkedYearsMapping) && isset($totalWorkedYearsMapping[$user->provider_id]))
                        <p>First Visit in CWR: {{ $totalWorkedYearsMapping[$user->provider_id]['date'] }} </p>
                        <p>Total time in CWR: {{ $totalWorkedYearsMapping[$user->provider_id]['totalWorkedYears'] }} </p>
                        @endif
                    </div>
                    @endif
                    @if(\Auth::user()->isAdmin())
                        <div class="form-group">
                            <select data-userid="{{$user->id}}" name="doctor-provider"
                                    class="form-control doctor-provider" @if($user->trashed()){{'disabled'}}@endif>
                                <option value="-1" disabled selected></option>
                                @foreach($providers as $provider)
                                    <option value="{{$provider->id}}" @if ($provider->id == $user->provider_id){{ 'selected' }}@endif>{{$provider->provider_name}}</option>
                                @endforeach
                            </select>
                            <div class="error-message"></div>
                        </div>
                        @if(!\Auth::user()->isSecretary())
                        <div class="form-group">
                            <p>Contractor Payment Plan:</p>
                            <select data-userid="{{$user->id}}" name="doctor-tariff-plan"
                                    class="form-control doctor-tariff-plan" @if($user->trashed()){{'disabled'}}@endif>
                                <option value="-1" disabled selected></option>
                                @foreach($tariffPlans as $tariffPlan)
                                    <option value="{{$tariffPlan->id}}" @if ($user->provider && $user->provider->tariffPlan && $user->provider->tariffPlan->id == $tariffPlan->id){{ 'selected' }}@endif>{{$tariffPlan->name}}</option>
                                @endforeach
                            </select>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <p>Billing Period:</p>
                            <select data-userid="{{$user->id}}" name="doctor-billing-period"
                                    class="form-control doctor-billing-period" @if($user->trashed()){{'disabled'}}@endif>
                                <option value="-1" disabled selected></option>
                                @foreach($billingPeriodTypes as $type)
                                    <option value="{{$type->id}}" @if ($user->provider && $user->provider->billing_period_type_id == $type->id){{ 'selected' }}@endif>{{$type->title}}</option>
                                @endforeach
                            </select>
                            <div class="error-message"></div>
                        </div>
                        <div class="form-group">
                            <p>Work Hours Per Week:</p>
                            <input
                                type="number"
                                inputmode="numeric"
                                name="work-hours-per-week"
                                class="form-control work-hours-per-week"
                                value="{{$user->provider && $user->provider->work_hours_per_week ? $user->provider->work_hours_per_week : 0}}"
                                placeholder=""
                                min="0"
                                data-userid="{{$user->id}}"
                                data-initial-value="{{$user->provider && $user->provider->work_hours_per_week ? $user->provider->work_hours_per_week : 0}}"
                                onkeypress="return (event.charCode != 8 && event.charCode == 0 || (event.charCode >= 48 && event.charCode <= 57))"
                                @if($user->trashed()){{'disabled'}}@endif
                            >
                            <div class="error-message"></div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6" style="padding-right: 10px;">
                                <div class="form-group">
                                    <p>License Start:</p>
                                    <input
                                        type="date"
                                        name="license-date"
                                        class="form-control license-date"
                                        value="{{$user->provider && $user->provider->license_date ? \Carbon\Carbon::parse($user->provider->license_date)->toDateString() : ''}}"
                                        data-userid="{{$user->id}}"
                                        data-initial-value="{{$user->provider && $user->provider->license_date ? \Carbon\Carbon::parse($user->provider->license_date)->toDateString() : ''}}"
                                        @if($user->trashed()){{'disabled'}}@endif
                                    >
                                    <div class="error-message"></div>
                                </div>
                            </div>
                            <div class="col-md-6" style="padding-left: 10px;">
                                <div class="form-group">
                                    <p>License End:</p>
                                    <input
                                        type="date"
                                        name="license-end-date"
                                        class="form-control license-end-date"
                                        value="{{$user->provider && $user->provider->license_end_date ? \Carbon\Carbon::parse($user->provider->license_end_date)->toDateString() : ''}}"
                                        data-userid="{{$user->id}}"
                                        data-initial-value="{{$user->provider && $user->provider->license_end_date ? \Carbon\Carbon::parse($user->provider->license_end_date)->toDateString() : ''}}"
                                        @if($user->trashed()){{'disabled'}}@endif
                                    >
                                    <div class="error-message"></div>
                                </div>
                            </div>
                        </div>

                        <provider-profile-checkbox-group
                            provider-data="{{$user->provider}}"
                            @if($user->trashed()){{'disabled'}}@endif
                        ></provider-profile-checkbox-group>
                    @endif

                    {{--<div class="form-group">--}}
                        {{--<div class="download-picture-block pull-right">--}}
                            {{--<button onclick="{{"window.open('".route('dashboard-download-photo', ['id' => $user->id])."', '_blank')"}}"--}}
                                    {{--type="button" class="btn btn-primary"--}}
                                    {{--title="Download Picture" @if($user->trashed() || empty($user->meta->photo)){{'disabled'}}@endif>--}}
                                {{--<span class="glyphicon glyphicon-download"></span></button>--}}
                        {{--</div>--}}
                        {{--<div class="pull-left">--}}
                            {{--<form class="user-photo-form" enctype="multipart/form-data">--}}
                                {{--{{csrf_field()}}--}}
                                {{--<input name="user_photo" data-userid="{{$user->id}}"--}}
                                       {{--@if(!empty($user->meta['photo'])){{'data-exists'}}@endif class="user-photo"--}}
                                       {{--type="file" title=""--}}
                                       {{--accept="image/*" @if($user->trashed() || !$user->isProviderAttached()){{'disabled'}}@endif>--}}
                                {{--<div class="help-block-xs">--}}
                                    {{--<span class="help-block error-message"></span>--}}
                                {{--</div>--}}
                            {{--</form>--}}
                        {{--</div>--}}
                        {{--<div class="clearfix"></div>--}}
                    {{--</div>--}}

                    <div class="profile-btn-block">
                        <div class="row">
                            <div class="col-md-6" style="padding-right: 10px;">
                                <div class="form-group">
                                    @if (!empty($user->meta()->withTrashed()->first()['signature']))
                                        <a 
                                            role="button"
                                            href="{{ route('show-signature-form', ['id' => $user->id != \Auth::user()->id ? $user->id : null]) }}"
                                            id="add-signature-btn"
                                            class="add-signature profile-btn btn btn-primary btn-success @if($user->trashed() || !$user->isProviderAttached()){{'disabled'}}@endif"
                                        >
                                            Update signature
                                        </a>
                                    @else
                                        <a
                                            role="button"
                                            href="{{ route('show-signature-form', ['id' => $user->id != \Auth::user()->id ? $user->id : null]) }}"
                                            id="add-signature-btn"
                                            class="add-signature profile-btn btn btn-primary btn-danger @if($user->trashed() || !$user->isProviderAttached()){{'disabled'}}@endif"
                                        >
                                            Add signature
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($user->meta()->withTrashed()->first()['signature']))
                                <div class="col-md-6" style="padding-left: 10px;">
                                    <div class="form-group">
                                        <button
                                            id="show-signature-btn"
                                            data-provider-id="{{$user->provider_id}}"
                                            class="show-signature profile-btn btn btn-primary"
                                        >
                                            Show signature
                                        </button>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button
                                        id="sms-signature-btn"
                                        data-provider-id="{{$user->provider_id}}"
                                        class="sms-signature profile-btn btn btn-primary"
                                    >
                                        Send SMS to update signature
                                    </button>
                                </div>
                            </div>
                        </div>

                        @if(isset($user->therapistSurvey) && $user->therapistSurvey->tridiuum_external_url)
                            <div class="form-group">
                                <a role="button" href="{{ $user->therapistSurvey->tridiuum_external_url }}"
                                   id="view-tridiuum-profile"
                                   class="profile-btn btn btn-primary btn"
                                   target="_blank"
                                >
                                    View on Tridiuum
                                </a>
                            </div>
                        @endif

                        @if($user->id == \Auth::user()->id)
                            <a role="button" class="profile-btn btn btn-primary" href="{{ route('change-password.form') }}">
                                Change Password
                            </a>
                        @endif
                    </div>
                </div>

                <div class="col-sm-9">
                    <ul class="nav nav-tabs">
                        @php
                            $activeTab = session('activeTab', 'tab_profile');
                            if (request()->query->has('tab')) {
                                $activeTab = session('activeTab', request()->query->get('tab'));
                            }
                        @endphp
                        <li class="@if($activeTab == 'tab_profile') active @endif"><a href="#tab_profile" data-toggle="tab">Profile</a></li>
                        @if(!is_null($salary) && !\Auth::user()->isSecretary())
                            <li class=""><a href="#tab_salary" data-toggle="tab">Service payouts</a></li>
                        @endif
                        @if(\Auth::user()->isAdmin() && $user->provider)
                            <li class="@if($activeTab == 'tab_tridiuum') active @endif"><a href="#tab_tridiuum" data-toggle="tab">Tridiuum</a></li>
                            <li class="@if($activeTab == 'tab_supervising') active @endif"><a href="#tab_supervising" data-toggle="tab">Supervising</a></li>
                            <li id="tab-comments" class="@if($activeTab == 'tab_comments') active @endif"><a href="#tab_comments" data-toggle="tab">Comments</a></li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane @if($activeTab == 'tab_profile') active @endif" id="tab_profile">
                            @include('profile.tabs.profile', [
                                'age_groups' => $age_groups,
                                'types_of_clients' => $types_of_clients,
                                'patient_categories' => $patient_categories,
                                'ethnicities' => $ethnicities,
                                'languages' => $languages,
                                'races' => $races,
                                'specialties' => $specialties,
                                'treatment_types' => $treatment_types,
                                'insurances' => $insurances,
                                'user' => $user,
                                'tridiuum_url_guide' => $tridiuum_url_guide,
                                'edit' => true
                            ])
                        </div>
                        @if(!is_null($salary) && !\Auth::user()->isSecretary())
                            <div class="tab-pane" id="tab_salary">
                                @include('profile.tabs.salary')
                            </div>
                        @endif
                        @if(\Auth::user()->isAdmin() && $user->provider)
                            <div class="tab-pane @if($activeTab == 'tab_tridiuum') active @endif" id="tab_tridiuum">
                                <p id="tridiuum_message" class=""></p>
                                <div class="form-group form-group--half-width">
                                    <label for="tridiuum_providers" class="control-label">Tridiuum Accounts:</label>
                                    <select data-userid="{{$user->provider_id}}" name="doctor-tridiuum-provider" id="tridiuum_providers"
                                            class="form-control doctor-tridiuum-provider" @if($user->trashed()){{'disabled'}}@endif>
                                        <option value="" @if(is_null($tridiuumProvider)) {{ 'selected' }} @endif>Unassigned</option>
                                        @if($tridiuumProvider)
                                            <option value="{{ $tridiuumProvider->id }}" selected>{{ $tridiuumProvider->first_name }} {{ $tridiuumProvider->last_name }}</option>
                                        @endif
                                        @foreach($tridiuumProviders as $provider)
                                            <option value="{{$provider->id}}" @if ($provider->id == $user->tridiuum_provider_id){{ 'selected' }}@endif>{{$provider->provider_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(\Auth::user()->isAdmin() && !\Auth::user()->isSecretary())
                                    <div class="sync-group">
                                        <div class="checkbox">
                                            <label>
                                                <input
                                                    type="checkbox"
                                                    id="appointment_sync"
                                                    class="sync-checkbox"
                                                    value=""
                                                    name="tridiuum_sync_appointments"
                                                    @if ($user->provider->tridiuum_sync_appointments)
                                                    checked
                                                    @endif
                                                >
                                                Appointment Synchronization
                                            </label>
                                        </div>
                                        <div class="checkbox">
                                            <label>
                                                <input
                                                    type="checkbox"
                                                    id="availability_sync"
                                                    class="sync-checkbox"
                                                    value=""
                                                    name="tridiuum_sync_availability"
                                                    @if ($user->provider->tridiuum_sync_availability)
                                                    checked
                                                    @endif
                                                >
                                                Availability Synchronization
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane @if($activeTab == 'tab_supervising') active @endif" id="tab_supervising">
                                @include('profile.tabs.supervising', [
                                    'user' => $user,
                                    'supervisors' => $supervisors,
                                    'currentSupervisor' => $currentSupervisor
                                ])
                            </div>

                            <div class="tab-pane @if($activeTab == 'tab_comments') active @endif" id="tab_comments">
                                <provider-comments 
                                    :provider-comments="{{ json_encode($comments) }}" 
                                    :current-user-id="{{ \Auth::user()->id }}" 
                                    :provider-id="{{ $user->provider->id }}"
                                />
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- <change-password-modal /> -->

    @include('profile.modals.signature-preview')
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/profile-image-uploader.js') }}"></script>
    <script src="{{ asset('js/doctor-profile.js?v=2') }}"></script>
@endsection
