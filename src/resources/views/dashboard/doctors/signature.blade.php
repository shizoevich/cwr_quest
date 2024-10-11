@extends('layouts.app')

@section('content')
    <div class="signature-wrapper">
        <div id="view" class="container">
            <div class="row">
                <div class="col-md-12">
                    @if(isset($error))
                        <div id="status-alert" class="alert alert-dismissible alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert">
                                <span>x</span>
                            </button>
                            {{ $error }}
                        </div>
                    @else
                        <div id="status-alert"></div>
                    @endif
                </div>

                <div class="col-sm-2 col-md-2 col-xs-3 signature-button-panel">
                    @if(isset($user) && \Auth::check())
                        <a class="btn btn-primary" href="{{ route('profile.index', ['id' => $user->id != \Auth::user()->id ? $user->id : null ]) }}">
                            Back
                        </a>
                    @endif
                </div>

                @if(isset($user))
                    <div class="col-sm-3 col-xs-9 col-sm-push-7 col-md-2 col-md-push-8 signature-button-panel">
                        <div class="pull-right">
                            @if(isset($token))
                                <button data-token="{{ $token }}" id="export-signature" class="btn btn-success">Save</button>
                            @else
                                <button data-userid="{{ $user->id }}" id="export-signature" class="btn btn-success">Save</button>
                            @endif
                            
                            <button id="clear-signature" class="btn btn-danger">Clear</button>
                        </div>
                    </div>
                    <div class="col-sm-7 col-md-8 col-xs-12 col-sm-pull-3 col-md-pull-2">
                        <h3 class="text-center">{{ $user->provider['provider_name'] }}, please sign in the field below.</h3>
                    </div>

                    <div class="col-md-12 text-center">
                        <div id="signature"></div>
                        <hr class="signature-line">
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/profile-jSignature.js') }}"></script>
@endsection