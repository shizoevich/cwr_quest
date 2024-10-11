@extends('layouts.app')

@section('content')
    <div class="wrapper">
        <div class="container panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-12">
                        <provider-availability-filters 
                            style="display:inline-block;"
                            prop-filter-type="{{ $selectedFilterType }}"
                            prop-week="{{ $week }}"
                            prop-billing-period-id="{{ $billingPeriodId }}"
                            :billing-periods="{{ json_encode($billingPeriods) }}"
                        />
                    </div>
                </div>
            </div>

            <div class="panel-body">
                @foreach($providers as $provider)
                    <div class="row">
                        <div class="provider-salary-block col-md-12" data-provider_id="{{$provider->id}}">
                            <h3 class="text-center">{{$provider->provider_name}}</h3>

                            <div class="table-responsive">
                                @include('dashboard.statistics.provider-availability._provider-availability-table')
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
