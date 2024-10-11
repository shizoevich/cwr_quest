@extends('layouts.app')

@section('content')
    <div class="wrapper" id="salary-wrapper">
        <div class="container panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-2 col-md-push-10">
                        <div class="form-group text-right">
                            <label style="display:block;">&nbsp;</label>
                            @if(!$isParserRunning)
                                <button class="btn btn-success" style="width:92px;" data-toggle="modal" data-target="#sync-visits-modal" disabled>Sync</button>
                            @else
                                <pageloader add-classes="parser-loader" />
                            @endif
                        </div>
                    </div>
                    <div class="col-md-10 col-md-pull-2">
                        <salary-filters style="display:inline-block;"
                                        csrf-token="{{csrf_token()}}"
                                        prop-month="{{$month}}"
                                        prop-filter-type="{{$selectedFilterType}}"
                                        prop-date-from="{{$dateFrom}}"
                                        prop-date-to="{{$dateTo}}"
                                        prop-billing-period-id="{{$billingPeriodId}}"
                                        :providers="{{ $providers->toJson() }}"
                                        prop-provider-id="{{ $selectedProvider }}"
                                        :billing-periods="{{ json_encode($billingPeriods) }}"
                        />
                    </div>
                </div>



            </div>
            <div class="panel-body">
                @php
                    if($selectedProvider) {
                        $providerList = $providers->where('id', $selectedProvider);
                    } else {
                        $providerList = $providers;
                    }
                @endphp
                @foreach($providerList as $provider)
                    @php
                    $checkIfNeedToShow = count(__data_get($salary, $provider->id, []))
                    + count(__data_get($refundsForMissingNotes, $provider->id, []))
                    + count(__data_get($missingNotes, $provider->id, []))
                    + count(__data_get($additionalCompensation, $provider->id, []));
                    @endphp
                    @if($checkIfNeedToShow > 0 || isset($selectedProvider))
                    <div class="row">
                        <div class="provider-salary-block col-md-12" data-provider_id="{{$provider->id}}">
                            <h3 class="text-center">{{$provider->provider_name}}</h3>

                            <div class="table-responsive">
                                @include('dashboard.salary._provider-salary-table', ['isParserRunning' => $isParserRunning])
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    <salary-sync />
@endsection

@section('scripts')
    @parent
    <script src="/js/salary.js?v=2"></script>
    <script>
        if("{{$isParserRunning}}" == 1) {
            var interval = window.setInterval(function() {
                axios({
                    method: 'get',
                    url: '/dashboard/check-visits-parser',
                }).then(response => {
                    if(response.status === 200 && !parseInt(response.data.status)) {
                        window.clearInterval(interval);
                        window.location.href = window.location.href
                    }
                });
            }, 30000);
        }
    </script>

    <script>
        function sendIsResolveComplaint() {
            let reviewedObj = { 
                _token:$('meta[name="csrf-token"]').attr('content'),
                billing_period_id:  parseInt(document.getElementById('billing_period_id').value),
                provider_id:  parseInt(document.getElementById('provider_id').value),
                is_resolve_complaint: document.getElementById('is_resolve_complaint').checked,
            }

            let reviewed = JSON.stringify(reviewedObj)
 
            const url = "https://admin.cwr.care/dashboard/complaint-reviewed"
            let xhr = new XMLHttpRequest()
 
            xhr.open('POST', url, true)
            xhr.setRequestHeader('Content-type', 'application/json; charset=UTF-8')
            xhr.send(reviewed);
 
           xhr.onload = function () {
           if(xhr.status === 201) {
              console.log("Reviewed Obj successfully created!") 
           }
          }
        }
    </script>
@endsection