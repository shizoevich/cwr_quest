<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Change Within Reach, Inc. - Secure Document Download</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}?v=24" rel="stylesheet">
    <link href="{{ asset('css/document-download.css') }}?v=11" rel="stylesheet">
</head>
<body>

@yield('content')

<!-- Scripts -->
@section('scripts')
    <script src="{{ asset('js/document_download.js') }}"></script>
@show

</body>
</html>