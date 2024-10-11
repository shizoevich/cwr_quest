@extends('layouts.app')

@section('content')
<div class="wrapper" id="salary-wrapper">
    <div class="container">
        <el-tabs type="border-card">
            <el-tab-pane>
                <span slot="label" id="payment-plans-label"><i class="el-icon-money"></i>Contractor Payment Plans</span>
                <div class="row" style="margin-bottom:15px;">
                    <div class="col-xs-12 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#create-tariff-plan-modal">Create</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered " id="provider-tariffs-plans-table">
                                <thead>
                                <tr>
                                    <td>Contractor Payment Plan</td>
                                    <td style="min-width:40px!important;width:40px!important;">Providers</td>
                                    <td style="min-width:140px!important;width:140px!important;"></td>
                                </tr>
                                </thead>
                                <tbody>
                                @if($tariffsPlans ->count() > 0)
                                    @foreach($tariffsPlans ->sortBy('name') as $TariffPlan)
                                        <tr>
                                            <td>
                                                {{$TariffPlan->name}}
                                            </td>
                                            <td>
                                                @php
                                                    $providers = $TariffPlan->providers->map(function ($provider) { return $provider->provider_name;})->toArray();
                                                @endphp
                                                <span
                                                    class="badge"
                                                    style="cursor: pointer"
                                                    data-toggle="tooltip"
                                                    data-html="true"
                                                    data-placement="right"
                                                    title="{{implode('<br>', $providers)}}"
                                                >
                                                    {{ $TariffPlan->providers->count() }}
                                                </span>
                                            </td>
                                            <td
                                                data-tariff_plan_id="{{$TariffPlan->id}}"
                                                data-providers_count="{{$TariffPlan->providers->count()}}"
                                                class="text-center"
                                            >
                                                <div class="btn-group" role="group" aria-label="...">
                                                    <a type="button"
                                                       class="btn btn-default tariff-plan-edit-button"
                                                       title="Edit"
                                                       href="{{url('dashboard/tariffs-plans',['id'=>$TariffPlan->id])}}"
                                                    >
                                                        <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                                    </a>
                                                    <a type="button"
                                                       class="btn btn-default tariff-plan-clone-button"
                                                       title="Duplicate"
                                                       data-toggle="modal"
                                                       data-target="#clone-tariff-plan-modal"
                                                    >
                                                        <span class="glyphicon glyphicon-duplicate" aria-hidden="true"></span>
                                                    </a>
                                                    <a type="button"
                                                       class="btn btn-default tariff-plan-delete-button"
                                                       title="Delete"
                                                       data-toggle="modal"
                                                       data-target="#delete-tariff-plan-modal"
                                                    >
                                                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4">
                                            Nothing found.
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </el-tab-pane>

            <el-tab-pane>
                <span id="insurance-configuration-label" slot="label"><i class="el-icon-setting"></i> Insurance Configuration</span>
                <insurance-configuration></insurance-configuration>
            </el-tab-pane>
        </el-tabs>
    </div>
</div>

@include('dashboard.tariffs-plans.create-modal')
@include('dashboard.tariffs-plans.delete-modal')
@include('dashboard.tariffs-plans.clone-modal')

@endsection

@section('scripts')
@parent
    <script src="/js/salary.js?v=2"></script>
    <script>
        $(document).ready(function() {
            const content = $('.el-tabs__content');
            content.css('overflow', 'visible');
            
            $('.el-tabs__item').click(function() {
                if ($(this).find('#payment-plans-label').length) {
                    content.css('overflow', 'visible');
                } else if ($('#insurance-configuration-label').length) {
                    content.css({
                    'overflow': 'visible',
                    'flex-grow': '1'
                    });
                } else {
                    content.css('overflow', '');
                }
            });
        });
    </script>

@endsection