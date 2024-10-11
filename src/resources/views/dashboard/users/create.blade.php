@extends('layouts.app')

@section('content')
<div class="wrapper">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h3>Create User</h3>
                <div class="panel panel-default">
                    {{--<div class="panel-heading">Register</div>--}}
                    <div class="panel-body">
                        <input type="hidden" id="domain" value="{{ config('app.email-domain') }}">
                        <form role="form" method="POST" id="auth-form" class="create-user" action="{{ route('dashboard.users.store') }}">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
                                <div class="col-md-10 col-md-offset-1">
                                    <label for="first_name" class="control-label required">First Name</label>
                                    <input id="first_name" type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" required autofocus>
                                    <span class="help-block with-errors">
                                        @if ($errors->has('first_name'))
                                        <strong>{{ $errors->first('first_name') }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
                                <div class="col-md-10 col-md-offset-1">
                                    <label for="last_name" class="control-label required">Last Name</label>
                                    <input id="last_name" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" required>
                                    <span class="help-block with-errors">
                                        @if ($errors->has('last_name'))
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <div class="col-md-10 col-md-offset-1">
                                    <label for="email" class="control-label required">E-Mail</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <input type="checkbox" id="generate" value="1" name="generate" {{ null === old('email') || null !== old('generate') ? 'checked' : '' }}>
                                            <label for="generate" style="margin-bottom:0;"> Generate</label>
                                        </span>
                                        <input name="email" id="email" type="email" class="form-control" value="{{ old('email') }}" required>
                                    </div><!-- /input-group -->

                                    <span class="help-block with-errors">
                                        @if ($errors->has('email'))
                                        <strong>{{ $errors->first('email') }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('personal_email') ? ' has-error' : '' }}">
                                <div class="col-md-10 col-md-offset-1">
                                    <label for="personal_email" class="control-label required">Personal
                                        E-Mail</label>
                                    <input id="personal_email" type="email" class="form-control" name="personal_email" value="{{ old('personal_email') }}" required>
                                    <span class="help-block with-errors">
                                        @if ($errors->has('personal_email'))
                                        <strong>{{ $errors->first('personal_email') }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            @if(auth()->user()->isOnlyAdmin())
                            <div class="form-group">
                                <div class="col-md-10 col-md-offset-1">
                                    <label for="user_role">
                                        User role
                                    </label>
                                    <select name="user_role" id="user_role" class="form-control">
                                        <option value="provider" @if(old('user_role')=='provider' ) selected @endif>Provider</option>
                                        <option value="secretary" @if(old('user_role')=='secretary' ) selected @endif>Secretary</option>
                                        <option value="patient_relation_manager" @if(old('user_role')=='patient_relation_manager' ) selected @endif>Patient Relation Manager</option>
                                    </select>
                                    <span class="help-block with-errors">
                                        @if ($errors->has('user_role'))
                                        <strong>{{ $errors->first('user_role') }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            @endif
                            <div class="form-group{{ $errors->has('provider_id') ? ' has-error' : '' }}">
                                <div class="col-md-10 col-md-offset-1">
                                    <label for="provider_id" class="control-label required">Provider</label>
                                    <select name="provider_id" class="form-control" @if(old('user_role') == 'secretary' || old('user_role') == 'patient_relation_manager') disabled @endif>
                                        <option value="-1" disabled selected></option>
                                        @foreach($providers as $provider)
                                        <option value="{{$provider->id}}" @if (old('provider_id')==$provider->id){{ 'selected' }}@endif>{{$provider->provider_name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="help-block with-errors">
                                        @if ($errors->has('provider_id'))
                                        <strong>{{ $errors->first('provider_id') }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('tariff_plan_id') ? ' has-error' : '' }}">
                                <div class="col-md-10 col-md-offset-1">
                                    <label for="tariff_plan_id" class="control-label">Contractor payment
                                        plan</label>
                                    <select name="tariff_plan_id" class="form-control" @if(old('user_role') == 'secretary' || old('user_role') == 'patient_relation_manager') disabled @endif>
                                        <option selected></option>
                                        @foreach($tariffPlans as $tariffPlan)
                                        <option value="{{$tariffPlan->id}}" @if (old('tariff_plan_id')==$tariffPlan->id){{ 'selected' }}@endif>{{$tariffPlan->name}}</option>
                                        @endforeach
                                    </select>
                                    <span class="help-block with-errors">
                                        @if ($errors->has('tariff_plan_id'))
                                        <strong>{{ $errors->first('tariff_plan_id') }}</strong>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-10 col-md-offset-1">
                                    <button type="submit" class="btn btn-primary pull-right">
                                        Create
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        if ($('#generate').prop('checked')) {
            $('#email').prop('readonly', true)
        }

        function generateEmail() {
            let email = $('#first_name').val().toLowerCase() + '.' + $('#last_name').val().toLowerCase() + '@' + $('#domain').val();
            email = email.replace(' ', '');
            $('#email').val(email);
        }

        $('#generate').change(function() {
            console.log('generate change');
            let $this = $(this);
            if ($this.prop('checked')) {
                $('#email').prop('readonly', true);
                generateEmail();
            } else {
                $('#email').prop('readonly', false);
            }
        });
        $('#first_name, #last_name').change(function() {
            console.log('name change');
            if ($('#generate').prop('checked')) {
                console.log('name change checked');
                generateEmail();
            }
        });
    });
</script>
@endsection