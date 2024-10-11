<?php
$activeTab = request()->get('tab', 'appointments-tab');
?>

@extends('layouts.app')

@section('content')
    <div class="wrapper" id="salary-wrapper">
        <div class="container-fluid">
            <ul class="nav nav-tabs">
                <li @if(!$activeTab || $activeTab === 'appointments-tab')class="active"@endif>
                    <a data-toggle="tab" href="#appointments-tab">
                        Appointments
                    </a>
                </li>
                <li @if($activeTab === 'posting-tab')class="active"@endif>
                    <a data-toggle="tab" href="#posting-tab">
                        Posting
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                @php
                    $class = '';
                    if(!$activeTab || $activeTab === 'appointments-tab') {
                        $class = 'in active';
                    }
                @endphp
                <div id="appointments-tab" class="tab-pane fade {{$class}}">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <completed-appointments :init-appointments="{{$appointments}}"
                                                    csrf-token="{{csrf_token()}}"
                                                    prop-month="{{$month}}"
                                                    prop-filter-type="{{$selectedFilterType}}"
                                                    prop-date-from="{{$dateFrom}}"
                                                    prop-date-to="{{$dateTo}}"
                                                    :statuses-filter="{{$statusesFilter}}"
                                                    :visit-inprogress-count="{{$visitInprogressCount}}"/>
                        </div>
                    </div>
                </div>

                @php
                    $class = '';
                    if(!$activeTab || $activeTab === 'posting-tab') {
                        $class = 'in active';
                    }
                @endphp
                <div id="posting-tab" class="tab-pane fade {{$class}}">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <payment-posting></payment-posting>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection