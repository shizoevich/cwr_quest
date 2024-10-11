@extends('layouts.formsApp')

@section('content')

    <router-view></router-view>

    @if(Auth::check())
        <span><are-you-still-here-modal /></span>
    @endif
@endsection