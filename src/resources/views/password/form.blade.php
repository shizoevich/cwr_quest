@extends('layouts.app')

@section('content')

    <div class="form-wrapper">
        <div class="container">
            <div class="row">

                <div class="col-md-8 col-md-offset-2">
                    @if(Session::has('success'))
                        <div class="alert alert-success">
                            <i class="fa fa-exclamation-circle">&nbsp;</i>
                            {{ Session::get('success') }}
                        </div>
                    @endif
                    <h3>Change Password</h3>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <form method="POST" action="{{route('change-password.store')}}">
                                {{ csrf_field() }}
                                <input name="user_id" value="{{ $user->id }}" hidden/>
                                <div class="col-md-10 col-md-offset-1">
                                    <div class="form-group {{ $errors->has('old_password') ? ' has-error' : '' }}">
                                        <label for="old_password" class="required">Old Password</label>
                                        <input type="password" class="form-control" id="old_password"
                                               name="old_password"
                                               data-required-error="@lang('validation.client-required')" required>

                                        <span class="help-block with-errors">
                            @if ($errors->has('old_password'))
                                                <strong>{{ $errors->first('old_password') }}</strong>
                                            @endif
                        </span>
                                    </div>
                                </div>
                                <div class="col-md-10 col-md-offset-1">
                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label for="password" class="control-label required">New Password</label>

                                        <input id="password" type="password" class="form-control" name="password"
                                               data-required-error="@lang('validation.client-required')" required>

                                        <span class="help-block with-errors">
                            @if ($errors->has('password'))
                                                <strong>{{ $errors->first('password') }}</strong>
                                            @endif
                        </span>

                                    </div>
                                </div>
                                <div class="col-md-10 col-md-offset-1">
                                    <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                        <label for="password-confirm" class="control-label required">
                                            Confirm Password
                                        </label>

                                        <input id="password-confirm" type="password" class="form-control"
                                               name="password_confirmation"
                                               data-required-error="@lang('validation.client-required')"
                                               required>

                                        <span class="help-block with-errors">
                                    @if ($errors->has('password_confirmation'))
                                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            @endif
                                </span>
                                    </div>
                                </div>
                                <div class="col-md-10 col-md-offset-1">
                                <button type="submit" class="btn btn-primary pull-right">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection