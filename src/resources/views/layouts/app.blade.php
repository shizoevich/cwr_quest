<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    {{--TODO: Change including css and js using mix()--}}
    <link href="{{ mix('css/app.css') . '?v=24' }}" rel="stylesheet">
    <link href="{{ asset('css/plugins.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/fine-uploader/fine-uploader-new.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/fastselect/fastselect.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/exams.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-submenu/2.0.4/css/bootstrap-submenu.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css"/>
    <link rel="stylesheet" href="{{ asset('css/element-ui/index.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropper/2.3.3/cropper.css">
    <script src="https://use.fontawesome.com/7e2fa2d587.js"></script>

    @yield('style')
</head>
<body>
<div id="app">
    @php
        $appName = json_encode(config('app.name'));
        $menuLinks = isset($menu_links) ? json_encode($menu_links) : 'null';
        $isAdmin = optional(Auth::user())->isAdmin() ? 'true' : "false" ;
    @endphp

    <div v-cloak>
        <navbar :app-name="{{ $appName }}" :menu-links="{{ $menuLinks }}" :is-admin="{{ $isAdmin }}" />
    </div>

    @auth
        <span><are-you-still-here-modal /></span>
    @endauth
    
    {{--@yield('content')--}}
    @section('content')
        <router-view>
            @if(Session::has('password-successfully-changed'))
                <div class="alert alert-success alert-dismissable">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{Session::get('password-successfully-changed')}}
                </div>
            @endif
        </router-view>

        {{--<change-password-modal />--}}
    @show

    @auth
        @if(!auth()->user()->isSecretary() && !auth()->user()->isAdmin() && auth()->user()->profile_completed_at != null && !in_array(Route::currentRouteName(), ['past-appointments', 'provider-timesheet']))
        
            <span><modal-appointment-notification /></span>
        
            @if(!auth()->user()->provider->is_new)
                <span><modal-week-confirmation /></span>

                <span><timesheet-confirmation /></span>
            @endif
        @endif

        @if(!auth()->user()->isOnlyAdmin())
            <span><modal-update-notification /></span>
        @endif        
    @endauth
</div>

{{--@if(config('app.env') == 'local')--}}
{{--<script src="http://localhost:35729/livereload.js"></script>--}}
{{--@endif--}}

<!-- Scripts -->
@section('scripts')
    @include ('js_settings')

    <script src="{{ asset('js/app.js') }}?{{time()}}"></script>
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('plugins/fine-uploader/fine-uploader.min.js') }}"></script>
    <script src="{{ asset('plugins/fastselect/fastsearch.min.js') }}"></script>
    <script src="{{ asset('plugins/fastselect/fastselect.min.js') }}"></script>
    <script src="{{asset("plugins/jquery.easing/jquery.easing.1.3.min.js")}}"></script>
    <script src="{{asset("js/exams.js")}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.js"></script>
    <!--
        This is a legacy template and is not meant to be used in new Fine Uploader integrated projects.
        Read the "Getting Started Guide" at http://docs.fineuploader.com/quickstart/01-getting-started.html
        if you are not yet familiar with Fine Uploader UI.
    -->
    <script type="text/template" id="qq-template">
        <div class="qq-uploader-selector qq-uploader" qq-drop-area-text="Drop files here">
            <div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container">
                <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                     class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar"></div>
            </div>
            <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
                <span class="qq-upload-drop-area-text-selector"></span>
            </div>
            <div class="buttons">
                <div class="qq-upload-button-selector qq-upload-button" style="border-radius:4px;">
                    <div>Select file</div>
                </div>
            </div>
            <span class="qq-drop-processing-selector qq-drop-processing">
                <span>Processing dropped files...</span>
                <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
            </span>
            <ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
                <li>
                    <div class="qq-progress-bar-container-selector">
                        <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                             class="qq-progress-bar-selector qq-progress-bar"></div>
                    </div>
                    <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
                    <img class="qq-thumbnail-selector" qq-max-size="100" qq-server-scale>
                    <span class="qq-upload-file-selector qq-upload-file"></span>
                    <span class="qq-edit-filename-icon-selector qq-edit-filename-icon"
                          aria-label="Edit filename"></span>
                    <input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
                    <span class="qq-upload-size-selector qq-upload-size"></span>
                    <button type="button" class="qq-btn qq-upload-cancel-selector qq-upload-cancel">Cancel</button>
                    <button type="button" class="qq-btn qq-upload-retry-selector qq-upload-retry">Retry</button>
                    <button type="button" class="qq-btn qq-upload-delete-selector qq-upload-delete">Delete</button>
                    <span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
                </li>
            </ul>

            <dialog class="qq-alert-dialog-selector">
                <div class="qq-dialog-message-selector"></div>
                <div class="qq-dialog-buttons">
                    <button type="button" class="btn btn-default qq-cancel-button-selector">Close</button>
                </div>
            </dialog>

            <dialog class="qq-confirm-dialog-selector">
                <div class="qq-dialog-message-selector"></div>
                <div class="qq-dialog-buttons">
                    <button type="button" class="qq-cancel-button-selector">No</button>
                    <button type="button" class="qq-ok-button-selector">Yes</button>
                </div>
            </dialog>

            <dialog class="qq-prompt-dialog-selector">
                <div class="qq-dialog-message-selector"></div>
                <input type="text">
                <div class="qq-dialog-buttons">
                    <button type="button" class="qq-cancel-button-selector">Cancel</button>
                    <button type="button" class="qq-ok-button-selector">Ok</button>
                </div>
            </dialog>
        </div>
    </script>

    <script src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-submenu/2.0.4/js/bootstrap-submenu.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
    <script type="text/javascript" src="{{ config('square.sdk_url') }}"></script>
    @include ('zendesk')
    @if(Auth::check() && Auth::user()->isAdmin())
        <script>
            window.Echo.private('removal-requests').listen('.removal-request.updated' , (data) => {
                if(data.count <= 0) {
                    $('#removal-request-count').text('');
                } else {
                    $('#removal-request-count').text('(' + data.count + ')');
                }
            });
            window.Echo.private('tridiuum-appointments')
                .listen('.appointments.tridiuum.updated', (data) => {
                    console.log(data);
                    if(data.count <= 0) {
                        $('#tridiuum-appointment-count').text('');
                    } else {
                        $('#tridiuum-appointment-count').text('(' + data.count + ')');
                    }
                });
        </script>
    @endif
@show

</body>
</html>
