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
    <link href="{{ asset('css/forms-app.css') }}?v=24" rel="stylesheet">
    <script src="https://use.fontawesome.com/7e2fa2d587.js"></script>
</head>
<body>
<div id="app-forms">

    @yield('content')

</div>


<!-- Scripts -->
@section('scripts')
    @include ('js_settings')

    <script type="text/javascript" src="{{ config('square.sdk_url') }}"></script>
    <script src="{{ asset('js/forms-app.js') }}?v=13"></script>
    <script src="{{ asset('js/forms-plugins.js') }}"></script>

    @include ('zendesk')
@show

</body>
</html>

