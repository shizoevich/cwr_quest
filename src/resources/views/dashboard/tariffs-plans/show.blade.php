@extends('layouts.app')

@section('content')
    @php
    use App\PatientInsurancePlanProcedure;
    $openInsurance = request()->has('insurance_id') ? request()->get('insurance_id') : $insurances->sortBy('insurance')->first()->id;

    @endphp

    <div class="wrapper" id="insurances-wrapper">
        <div class="container panel panel-default">
            <div class="panel-heading clearfix">
                <div class="row">
                    <div class="col-xs-6">
                        <label for="tariff_plan_name">Plan Name</label>
                        <div class="input-group">
                            <input type="text"
                                   value="{{$tariffPlan->name}}"
                                   data-old_value="{{$tariffPlan->name}}"
                                   class="form-control"
                                   name="tariff_plan_name"
                                   id="tariff_plan_name">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" id="save_plan_name" data-tariff_plan_id="{{$tariffPlan->id}}" disabled>Save</button>
                            </span>
                        </div>
                        <input type="hidden" id="tariff_plan_id" value="{{ $tariffPlan->id }}">
                    </div>
                    <div class="col-xs-6">
                        <label for="missing_progress_note_fee">Minimum Wage Payout</label>
                        <div class="input-group">
                            <input type="number"
                                   value="{{$tariffPlan->fee_per_missing_pn ? number_format($tariffPlan->fee_per_missing_pn, 2) : ''}}"
                                   data-old_value="{{$tariffPlan->fee_per_missing_pn}}"
                                   class="form-control"
                                   name="missing_progress_note_fee"
                                   id="missing_progress_note_fee">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" id="confirm_save_missing_progress_note_fee" data-tariff_plan_id="{{$tariffPlan->id}}" disabled>Save</button>
                            </span>
                        </div>
                        <span class="help-block" style="margin-top:0;">(for sessions without Progress Notes or Initial Assessments)</span>
                    </div>
                </div>
            </div>

            <div class="panel-body">
                <div class="col-xs-12 visible-xs visible-sm">
                    <select class="form-control insurance-selector">
                        @foreach($insurances->sortBy('insurance') as $key => $insurance)
                            @php
                                $pricesCount = $insurance->plans->sum(function($plan) {
                                                                       return $plan->proceduresPrices->where('price','>', 0)->count();
                                                                   });
                            @endphp

                            <option class="{{$insurance->id == $openInsurance ? 'active' : ''}} {{$pricesCount ? '' : 'text-danger'}}" value="{{$insurance->id}}" href="#insurance_{{$insurance->id}}" data-toggle="tab">
                                {{$insurance->insurance}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 hidden-xs hidden-sm">
                    <div class="row">

                        <ul class="nav nav-tabs tabs-left">
                            @foreach($insurances->sortBy('insurance') as $key => $insurance)
                                @php
                                    $plansIds = $insurance->plans->map(function($plan) { return $plan->id;})->toArray();
                                    $pricesCount = $tariffPlan->prices()
                                    ->where('tariff_plan_id', $tariffPlan->id)
                                    ->whereIn('plan_id', $plansIds)
                                    ->where('price','>', 0)
                                    ->count();
                                @endphp
                                <li class="{{$insurance->id == $openInsurance ? 'active' : ''}} ">
                                    <a href="#insurance_{{$insurance->id}}" data-toggle="tab" class="{{$pricesCount ? '' : 'text-danger'}}">
                                        {{$insurance->insurance}}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-10 col-sm-12">
                    <div class="row">
                        <div class="tab-content" style="padding-left:10px;">


                            @foreach($insurances->sortBy('insurance') as $key => $insurance)
                                <div class="tab-pane {{$insurance->id == $openInsurance ? 'active' : ''}}" id="insurance_{{$insurance->id}}">
                                    {{--{{$insurance->insurance}}--}}

                                    <div class="buttons-block pull-left">
                                        <button
                                                type="button"
                                                class="btn btn-primary group-plans hidden"
                                                data-tariff_plan_id="{{$tariffPlan->id}}"
                                                data-insurance_id="{{$insurance->id}}"
                                        >Group</button>

                                        <button
                                                type="button"
                                                class="btn btn-primary ungroup-plans hidden"
                                                data-tariff_plan_id="{{$tariffPlan->id}}"
                                                data-insurance_id="{{$insurance->id}}"
                                        >Ungroup</button>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-6">
                                            <span>11 - Office Visit</span>
                                            <br/>
                                            <span style="color:blue;">02 - Telehealth/Home</span>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="buttons-block text-right">
                                                <button type="button" class="btn btn-primary edit-table" id="edit-table">Edit</button>
                                                <button type="button" class="btn btn-default cancel-edit-table" id="cancel-edit-table">Cancel</button>
                                                <button type="button" class="btn btn-success confirm-save-table">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="insurance_{{$insurance->id}}_table">
                                            <thead>
                                            <tr>
                                                <td style="width: 20px;"></td>
                                                <td style="min-width:100px;"></td>
                                                @foreach($procedures as $procedure)
                                                    <td title="{{$procedure->name}}">
                                                        {{$procedure->code}}
                                                        {{--<span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>--}}
                                                    </td>
                                                @endforeach
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @if(count($insurance->plans) > 0)
                                                @foreach($insurance->plans->sortBy('name') as $plan)
                                                    <tr>
                                                        <td rowspan="2" style="width: 20px;vertical-align:middle;">
                                                            <input type="checkbox"
                                                                   class="checkbox grouped_plans_checkox"
                                                                   name="grouped_plans[]"
                                                                   value="{{$plan->id}}"
                                                                   />
                                                        </td>
                                                        @php
                                                         $pricesCount = $tariffPlan->prices()
                                                                    ->where('tariff_plan_id', $tariffPlan->id)
                                                                    ->where('plan_id', $plan->id)
                                                                    ->where('price','>', 0)
                                                                    ->count();
                                                        @endphp
                                                        <td rowspan="2" class="{{$pricesCount ? '' : 'text-danger'}}" style="vertical-align:middle;">
                                                            <span class="name"> {{$plan->name}} </span>

                                                                @if($plan->childPlans->count() > 0)
                                                                    @php
                                                                        $childPlansNames = $plan->childPlans->map(function ($plan) { return $plan->name;})->toArray();
                                                                    @endphp
                                                                    <span class="badge"
                                                                          data-toggle="tooltip"
                                                                          data-html="true"
                                                                          data-placement="right"
                                                                          title="{{implode('<br>', $childPlansNames)}}"
                                                                    >{{$plan->childPlans->count()}}</span>
                                                            @endif

                                                        </td>
                                                        @foreach($procedures as $procedure)
                                                            <td title="11 - Office Visit">
                                                                <?php
                                                                $procedurePrice = $tariffPlan->prices()
                                                                    ->where('tariff_plan_id', $tariffPlan->id)
                                                                    ->where('plan_id', $plan->id)
                                                                    ->where('procedure_id', $procedure->id)
                                                                    ->where('type', PatientInsurancePlanProcedure::TYPE_MASTER)
                                                                    ->first();
                                                                ?>
                                                                <span class="inline-edit {{$procedurePrice ? : 'empty'}}">
                                                                    <input
                                                                            type="number"
                                                                            value="{{$procedurePrice && $procedurePrice->price !== null ? number_format ($procedurePrice->price,2) : ''}}"
                                                                            class="form-control"
                                                                            name="prices[{{$insurance->id}}][{{$plan->id}}][{{$procedure->id}}]"
                                                                            data-tariff_plan_id="{{$tariffPlan->id}}"
                                                                            data-insurance_id="{{$insurance->id}}"
                                                                            data-plan_id="{{$plan->id}}"
                                                                            data-procedure_id="{{$procedure->id}}"
                                                                            data-plan_procedure_price_id="{{$procedurePrice ? $procedurePrice->id : ''}}"
                                                                            data-type="{{PatientInsurancePlanProcedure::TYPE_MASTER}}"
                                                                            style="min-width:75px;"
                                                                    >
                                                                    <span>
                                                                        {{$procedurePrice && $procedurePrice->price !== null ? number_format($procedurePrice->price,2) : ' - '}}
                                                                    </span>
                                                                </span>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    <tr>
                                                        @foreach($procedures as $procedure)
                                                            <td title="02 - Telehealth/Home" style="color:blue;">
                                                                <?php
                                                                $procedurePrice = $tariffPlan->prices()
                                                                    ->where('tariff_plan_id', $tariffPlan->id)
                                                                    ->where('plan_id', $plan->id)
                                                                    ->where('procedure_id', $procedure->id)
                                                                    ->where('type', PatientInsurancePlanProcedure::TYPE_MASTER)
                                                                    ->first();
                                                                ?>
                                                                <span class="inline-edit {{$procedurePrice ? : 'empty'}}">
                                                                    <input
                                                                            type="number"
                                                                            value="{{$procedurePrice && $procedurePrice->telehealth_price !== null ? number_format ($procedurePrice->telehealth_price,2) : ''}}"
                                                                            class="form-control"
                                                                            name="prices[{{$insurance->id}}][{{$plan->id}}][{{$procedure->id}}]"
                                                                            data-tariff_plan_id="{{$tariffPlan->id}}"
                                                                            data-insurance_id="{{$insurance->id}}"
                                                                            data-plan_id="{{$plan->id}}"
                                                                            data-procedure_id="{{$procedure->id}}"
                                                                            data-plan_procedure_price_id="{{$procedurePrice ? $procedurePrice->id : ''}}"
                                                                            data-type="{{PatientInsurancePlanProcedure::TYPE_MASTER}}"
                                                                            data-is_telehealth="1"
                                                                            style="min-width:75px;color:blue;"
                                                                    >
                                                                    <span>
                                                                        {{$procedurePrice && $procedurePrice->telehealth_price !== null ? number_format($procedurePrice->telehealth_price,2) : ' - '}}
                                                                    </span>
                                                                </span>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="{{count($procedures) + 2}}" style="text-align: center;">
                                                        <h5>Nothing Found</h5>
                                                    </td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>

                                </div>

                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>

        @include('dashboard.tariffs-plans.group-plans-modal')
        @include('dashboard.tariffs-plans.ungroup-plans-modal')
        @include('dashboard.tariffs-plans.confirm-update-fee-modal', ['modalId' => 'confirm-save-fee-modal'])
        @include('dashboard.tariffs-plans.confirm-update-fee-modal', ['modalId' => 'confirm-save-missing-pn-fee-modal'])
    </div>
@endsection

@section('scripts')
    @parent
    <script src="/js/salary.js?v=2"></script>
@endsection