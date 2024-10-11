@extends('layouts.app')

@section('content')
    <div class="full-height-wrapper-login">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">Reset Password</div>

                        <div class="panel-body">
                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form role="form" method="POST" id="auth-form" data-validator="true"
                                  action="{{ route('password.request') }}" data-toggle="validator" novalidate>
                                {{ csrf_field() }}

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <div class="col-md-10 col-md-offset-1">
                                        <label for="email" class="control-label required">E-Mail</label>

                                        <input id="email" type="email" class="form-control" name="email"
                                               value="{{ $email or old('email') }}" data-required-error="@lang('validation.client-required')" data-error="@lang('validation.client-email')" required autofocus>

                                        <span class="help-block with-errors">
                                            @if ($errors->has('email'))
                                            <strong>{{ $errors->first('email') }}</strong>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <div class="col-md-10 col-md-offset-1">
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

                                <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                    <div class="col-md-10 col-md-offset-1">
                                        <label for="password-confirm" class="control-label required">Confirm New
                                            Password</label>

                                        <input id="password-confirm" type="password" class="form-control"
                                               name="password_confirmation" data-required-error="@lang('validation.client-required')" required>

                                        <span class="help-block with-errors">
                                            @if ($errors->has('password_confirmation'))
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                            @endif
                                        </span>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-10 col-md-offset-1">
                                        <button type="submit" class="btn btn-primary pull-right">
                                            Reset Password
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
