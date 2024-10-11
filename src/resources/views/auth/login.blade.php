@extends('layouts.app')

@section('content')
    <div class="full-height-wrapper-login">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    @if(\Session::has('message'))
                    <div class="alert alert-{{ \Session::has('success') && true === \Session::get('success') ? 'success' : 'danger' }}">
                        {!! \Session::get('message') !!}
                    </div>
                    @endif
                    <h1 class="text-center custom-page-header">Document Management System</h1>
                    <h3>Login</h3>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <form role="form" method="POST" id="auth-form" action="{{ route('login') }}" data-validator="true" novalidate>
                                {{ csrf_field() }}

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <div class="col-md-10 col-md-offset-1">
                                        <label for="email" class="control-label required">E-Mail</label>

                                        <input id="email" type="email" class="form-control" name="email"
                                               value="{{ old('email') }}" data-required-error="@lang('validation.client-required')" required autofocus>

                                        <span class="help-block with-errors">
                                            @if ($errors->has('email'))
                                                <strong>{{ $errors->first('email') }}</strong>
                                            @endif
                                        </span>
                                    </div>

                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <div class="col-md-10 col-md-offset-1">
                                        <label for="password" class="control-label required">Password</label>

                                        <input id="password" type="password" class="form-control" name="password" data-required-error="@lang('validation.client-required')"
                                               required>

                                        <span class="help-block with-errors">
                                            @if ($errors->has('password'))
                                                <strong>{{ $errors->first('password') }}</strong>
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{--<div class="form-group">--}}
                                    {{--<div class="col-md-10 col-md-offset-1">--}}
                                        {{--<div class="checkbox">--}}
                                            {{--<label>--}}
                                                {{--<input type="checkbox"--}}
                                                       {{--name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me--}}
                                            {{--</label>--}}
                                        {{--</div>--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                <div class="form-group">
                                    <div class="col-md-10 col-md-offset-1">
                                        <button type="submit" class="btn btn-primary pull-right">
                                            Login
                                        </button>

                                        <a class="btn btn-link pull-right" href="{{ route('password.request') }}">
                                            Forgot Your Password?
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <div class="col-md-10 col-md-offset-1">
                                <a href="{{ route('login.google') }}" class="btn btn-danger" style="width:100%;margin-top:15px;">
                                    <span class="fa fa-google"></span>
                                    Login with Google
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
