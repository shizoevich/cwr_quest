@extends('layouts.app')

@section('content')
    <div>

        <doctors-availability :filters_values="{{json_encode($filtersValues)}}"/>
    </div>

@endsection

@section('scripts')
    @parent

@endsection