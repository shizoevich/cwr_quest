@extends('layouts.app')

@section('content')
    <div class="full-height-wrapper">
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
                                  action="{{ route('password.email') }}" data-toggle="validator" novalidate>
                                {{ csrf_field() }}

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <div class="col-md-10 col-md-offset-1">
                                        <label for="email" class="control-label required">E-Mail</label>
                                        <input id="email" type="email" class="form-control" name="email"
                                               value="{{ old('email') }}" data-required-error="@lang('validation.client-required')" data-error="@lang('validation.client-email')" required>

                                        <span class="help-block with-errors">
                                            @if ($errors->has('email'))
                                                <strong>{{ $errors->first('email') }}</strong>
                                            @endif
                                        </span>

                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-10 col-md-offset-1">
                                        <button type="submit" class="btn btn-primary pull-right">
                                            Send Password Reset Link
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
