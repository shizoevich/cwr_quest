<table class="statistic-table table table-condenced table-bordered dataTable">
    <thead>
        <tr>
            <td>CPT Codes</td>
            <td># of Visits</td>
            <td>Fee per Visit</td>
            <td>Amount Paid</td>
            <td>Notes</td>
        </tr>
    </thead>
    <tbody>
        @php
            $total = [
                'regular' => [
                    'visits_per_month' => 0,
                    'overtime_visits_per_month' => 0,
                    'amount_paid' => 0,
                    'overtime_amount_paid' => 0,
                    'overtime_amount_paid_diff' => 0,
                    'needs_display' => false,
                ],
                'missing_pn' => [
                    'data' => [],
                    'visits_per_month' => 0,
                    'overtime_visits_per_month' => 0,
                    'amount_paid' => 0,
                    'overtime_amount_paid' => 0,
                    'needs_display' => false,
                ],
                'refunds_for_completed_pn' => [
                    'visits_per_month' => 0,
                    'amount_paid' => 0,
                    'needs_display' => false,
                ],
                'additional_compensation' => [
                    'visits_per_month' => 0,
                    'amount_paid' => 0,
                    'needs_display' => false,
                ],
            ];
        @endphp

        @if (array_key_exists($provider->id, $salary) && count($salary[$provider->id]) > 0)
            @php
                if (array_key_exists($provider->id, $salaryTotal)) {
                    $total['regular'] = [
                        'visits_per_month' => $salaryTotal[$provider->id]['visits_per_month'] ?? 0,
                        'overtime_visits_per_month' => $salaryTotal[$provider->id]['overtime_visits_per_month'] ?? 0,
                        'amount_paid' => $salaryTotal[$provider->id]['amount_paid'] ?? 0,
                        'overtime_amount_paid' => $salaryTotal[$provider->id]['overtime_amount_paid'] ?? 0,
                        'overtime_amount_paid_diff' => $salaryTotal[$provider->id]['overtime_amount_paid_diff'] ?? 0,
                        'needs_display' => true,
                    ];
                }
            @endphp

            <tr>
                <td colspan="5" class="text-center"><b>Compensation for work during Current Pay Period</b></td>
            </tr>

            @foreach ($salary[$provider->id] as $plan)
                <tr>
                    <td>
                        {{ $plan['insurance'] }}, {{ $plan['plan_name'] }},
                        {{ $plan['procedure_code'] }}@if ($plan['is_telehealth'])
                            <span style="color:blue;">(Telehealth)</span>
                            @endif @if ($plan['is_overtime'])
                                <span style="color:#E6A23C;">(Overtime)</span>
                                @endif @if ($plan['is_created_from_timesheet'])
                                    <span style="color:#E6A23C;">(Added by Therapist)</span>
                                @endif
                    </td>
                    <td>{{ $plan['visits_per_month'] }}</td>
                    <td>${{ number_format($plan['paid_fee'], 2) }}</td>
                    <td>${{ number_format($plan['amount_paid'], 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
        @endif
        
        @if (array_key_exists($provider->id, $missingNotes) && count($missingNotes[$provider->id]) > 0)
            @php
                if (array_key_exists($provider->id, $missingNotesTotal)) {
                    $total['missing_pn'] = [
                        'data' => $missingNotesTotal[$provider->id]['data'] ?? [],
                        'visits_per_month' => $missingNotesTotal[$provider->id]['visits_per_month'] ?? 0,
                        'overtime_visits_per_month' => $missingNotesTotal[$provider->id]['overtime_visits_per_month'] ?? 0,
                        'amount_paid' => $missingNotesTotal[$provider->id]['amount_paid'] ?? 0,
                        'overtime_amount_paid' => $missingNotesTotal[$provider->id]['overtime_amount_paid'] ?? 0,
                        'needs_display' => true,
                    ];
                }
            @endphp

            @foreach ($total['missing_pn']['data'] as $item)
                <tr style="color:red;">
                    <td>
                        <span>Visits with Missing Progress Notes / Initial Assessments</span>
                        @if ($item['overtime_visits_per_month'] > 0)
                            <span style="color:#E6A23C;">({{ $item['overtime_visits_per_month'] }} Overtime)</span>
                        @endif
                    </td>
                    <td>{{ $item['visits_per_month'] }}</td>
                    <td>${{ number_format($item['fee_per_visit'], 2) }}</td>
                    <td>${{ number_format($item['amount_paid'], 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
        @endif

        @php
            $totalVisitsPerMonth = $total['regular']['visits_per_month'] + $total['missing_pn']['visits_per_month'];
            $totalAmountPaid = $total['regular']['amount_paid'] + $total['missing_pn']['amount_paid'];
            $totalOvertimeVisitsPerMonth = $total['regular']['overtime_visits_per_month'] + $total['missing_pn']['overtime_visits_per_month'];
            $totalOvertimeAmountPaid = $total['regular']['overtime_amount_paid'] + $total['missing_pn']['overtime_amount_paid'];
            $totalOvertimeAmountPaidDiff = $total['regular']['overtime_amount_paid_diff'];
            $overtimePercentage = 0;

            if ($totalVisitsPerMonth > 0) {
                $overtimePercentage = number_format($totalOvertimeVisitsPerMonth / $totalVisitsPerMonth * 100, 2);
            }

            $overtimeTooltipText = 'Overtime Percentage: <b>' . $overtimePercentage . '%</b> <br />
                                    Overtime Amount Paid:  <b>' . format_money($totalOvertimeAmountPaid) . '</b> <br />
                                    Overpayment:  <b>' . format_money($totalOvertimeAmountPaidDiff) . ' </b> <br />';
        @endphp

        @php
            $totalVisitsForYear = 0;
            $totalOvertimeVisitsForYear = 0;
            $totalOvertimeAmountPaidForYear = 0;
            $totalOvertimeAmountPaidDiffForYear = 0;
            $overtimePercentageForYear = 0;

            if (isset($salaryForYearTotal[$provider->id])) {
                $totalVisitsForYear += $salaryForYearTotal[$provider->id]['visits_per_month'] ?? 0;
                $totalOvertimeVisitsForYear += $salaryForYearTotal[$provider->id]['overtime_visits_per_month'] ?? 0;
                $totalOvertimeAmountPaidForYear += $salaryForYearTotal[$provider->id]['overtime_amount_paid'] ?? 0;
                $totalOvertimeAmountPaidDiffForYear += $salaryForYearTotal[$provider->id]['overtime_amount_paid_diff'] ?? 0;
            }
            if (isset($missingNotesForYearTotal[$provider->id])) {
                $totalVisitsForYear += $salaryForYearTotal[$provider->id]['visits_per_month'] ?? 0;
                $totalOvertimeVisitsForYear += $salaryForYearTotal[$provider->id]['overtime_visits_per_month'] ?? 0;
                $totalOvertimeAmountPaidForYear += $salaryForYearTotal[$provider->id]['overtime_amount_paid'] ?? 0;
            }

            if ($totalVisitsForYear > 0) {
                $overtimePercentageForYear = number_format($totalOvertimeVisitsForYear / $totalVisitsForYear * 100, 2);
            }

            $overtimeTooltipText .= 'Overtime Percentage (TTM):  <b>' . $overtimePercentageForYear . '%</b> <br />
                                    Overtime Amount Paid (TTM):  <b>' . format_money($totalOvertimeAmountPaidForYear) . '</b> <br />
                                    Overpayment (TTM):  <b>' . format_money($totalOvertimeAmountPaidDiffForYear) . '</b>';
        @endphp

        @if ($totalVisitsPerMonth > 0 || $totalAmountPaid > 0)
            <tr style="background-color:#f9f9f9;">
                <td><b>Total</b></td>
                <td>
                    <b>{{ $totalVisitsPerMonth }}</b>
                    @if ($totalOvertimeVisitsForYear > 0 || $totalOvertimeAmountPaidForYear > 0)
                        <span style="color:#E6A23C;">
                            (
                            <span>{{ $totalOvertimeVisitsPerMonth }}</span>
                            <span style="color: rgb(62, 72, 85)">
                                <help :offset="10" content="{{ $overtimeTooltipText }}" />
                            </span>
                            )
                        </span>
                    @endif
                </td>
                <td></td>
                <td><b>{{ format_money($totalAmountPaid) }}</b></td>
                <td></td>
            </tr>
        @endif

        @if (array_key_exists($provider->id, $refundsForMissingNotes) && count($refundsForMissingNotes[$provider->id]) > 0)
            <tr>
                <td colspan="5" class="text-center"><b>Balance Payout for Visits in Previous Pay Periods</b></td>
            </tr>

            @foreach ($refundsForMissingNotes[$provider->id] as $plan)
                @php
                    $total['refunds_for_completed_pn']['visits_per_month'] += $plan['visits_per_month'];
                    $total['refunds_for_completed_pn']['amount_paid'] += $plan['amount_paid'];
                    $total['refunds_for_completed_pn']['needs_display'] = true;
                @endphp
                <tr>
                    <td>
                        {{ $plan['insurance'] }}, {{ $plan['plan_name'] }},
                        {{ $plan['procedure_code'] }}@if ($plan['is_telehealth'])
                            <span style="color:blue;">(Telehealth)</span>
                            @endif @if ($plan['is_overtime'])
                                <span style="color:#E6A23C;">(Overtime)</span>
                                @endif @if ($plan['is_created_from_timesheet'])
                                    <span style="color:#E6A23C;">(Added by Therapist)</span>
                                @endif
                    </td>
                    <td>{{ $plan['visits_per_month'] }}</td>
                    <td>${{ number_format($plan['paid_fee'], 2) }}</td>
                    <td>${{ number_format($plan['amount_paid'], 2) }}</td>
                    <td></td>
                </tr>
            @endforeach
        @endif

        @foreach ($total as $key => $item)
            @if ($key == 'refunds_for_completed_pn')
                @if ($item['needs_display'])
                    @php
                        $total[$key]['needs_display'] = false;
                    @endphp
                    <tr style="background-color:#f9f9f9;">
                        <td><i><b>Total</b></i></td>
                        <td><i><b>{{ $item['visits_per_month'] }}</b></i></td>
                        <td></td>
                        <td><i><b>{{ format_money($item['amount_paid']) }}</b></i></td>
                        <td></td>
                    </tr>
                @endif
            @endif
        @endforeach

        <tr>
            <td colspan="5" class="text-center" style="position:relative;">
                <b style="line-height:30px;">Additional Compensation</b>
                <salary-management :provider-id="{{ $provider->id }}"
                    :billing-period-id="{{ $selectedFilterType == 4 || $selectedFilterType == 5 ? $billingPeriodId : 'null' }}" />
            </td>
        </tr>
        @if (array_key_exists($provider->id, $additionalCompensation) && count($additionalCompensation[$provider->id]) > 0)
            @foreach ($additionalCompensation[$provider->id] as $item)
                @if ($item['type_slug'] !== 'sick_time')
                    @php
                        $total['additional_compensation']['amount_paid'] += $item['paid_fee'];
                        $total['additional_compensation']['needs_display'] = true;
                    @endphp
                    <tr>
                        <td>{{ $item['title'] }}</td>
                        <td>{{ data_get($item, 'additional_data.visit_count') }}</td>
                        <td></td>
                        <td>${{ number_format($item['paid_fee'], 2) }}</td>
                        <td>{{ $item['notes'] }}</td>
                    </tr>
                @else
                    @php
                        $sickTime = $item;
                    @endphp
                @endif
            @endforeach
        @else
            <tr>
                <td colspan="5" class="text-center">Nothing found.</td>
            </tr>
        @endif
        @if ($total['additional_compensation']['needs_display'])
            @php
                $total['additional_compensation']['needs_display'] = false;
            @endphp
            <tr style="background-color:#f9f9f9;">
                <td><i><b>Total</b></i></td>
                <td></td>
                <td></td>
                <td><i><b>{{ format_money($total['additional_compensation']['amount_paid']) }}</b></i></td>
                <td></td>
            </tr>
        @endif
        @php
            $totalVisitsPerMonth = 0;
            $totalAmountPaid = 0;

            foreach ($total as $item) {
                $totalVisitsPerMonth += $item['visits_per_month'];
                $totalAmountPaid += $item['amount_paid'];
            }
        @endphp
        @if ($totalVisitsPerMonth > 0 || $totalAmountPaid > 0)
            <tr>
                <td colspan="5">&nbsp;</td>
            </tr>
            <tr style="background-color:#f9f9f9;">
                <td><b>Total</b></td>
                <td></td>
                <td></td>
                <td><b>{{ format_money($totalAmountPaid) }}</b></td>
                <td></td>
            </tr>
        @endif
        @isset($sickTime)
            <tr>
                <td><i>{{ $sickTime['title'] }}</i></td>
                <td><i>{{ data_get($sickTime, 'additional_data.visit_count') }}</i></td>
                <td></td>
                <td></td>
                <td><i>{{ $sickTime['notes'] }}</i></td>
            </tr>
        @endisset
    </tbody>
</table>
<br>

<br>
<br>
<table class="statistic-table table table-condenced table-bordered dataTable mt-3">
    <tbody>
        <tr>
            <td colspan="5" class="text-center" style="position:relative;">
                <b style="line-height:30px;">Additional Information About Therapist</b>
            </td>
        </tr>

        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;">
                Contractor Payment Plan
            </td>
            <td class="text-justify" style="position:relative;">
                {{ isset($provider->tariffPlan) ? $provider->tariffPlan->name : '-' }}
            </td>
        </tr>

        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;">
                Sick Hours Per Year
            </td>
            <td class="text-justify" style="position:relative;">
                @isset($sickTimeMapping[$provider->id])
                    {{$sickTimeMapping[$provider->id]['total_visit_count']}} hour{{$sickTimeMapping[$provider->id]['total_visit_count'] != 1 ? 's' : ''}}
                    @php
                        $seekTimePaid = $sickTimeMapping[$provider->id]['seek_time_paid'];
                        $formattedSeekTimePaid = intval($seekTimePaid) == $seekTimePaid ? intval($seekTimePaid) : number_format($seekTimePaid, 2);
                    @endphp
                    (${{ $formattedSeekTimePaid }})
                @else
                    0
                @endisset
            </td>
        </tr>

        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;">
                <span>
                    <help :offset="10" content="Value is calculated based on appointments with status 'Visit Created'" />
                </span>
                Appointments Per Year
            </td>
            <td class="text-justify" style="position:relative;">
                {{ isset($appointmentsCountMapping) ? $appointmentsCountMapping[$provider->id] ?? 0 : 0 }}
            </td>
        </tr>

        @if (isset($totalWorkedYearsMapping) && isset($totalWorkedYearsMapping[$provider->id]))
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;">
                Therapist First Visit in CWR
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $totalWorkedYearsMapping[$provider->id]['date'] }}
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;">
                Therapist Total Time
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $totalWorkedYearsMapping[$provider->id]['totalWorkedYears'] }}
            </td>
        </tr>
        @endif

        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                Minimum Work Hours Per Week
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $provider->work_hours_per_week ?? '-' }}
            </td>
        </tr>
    </tbody>
</table>
<br>

@if(isset($totalAvailabilityMapping) && isset($totalAvailabilityMapping[$provider->id]))
    <br>
    <br>
    <table class="statistic-table table table-condenced table-bordered dataTable mt-3">
        <tbody>
            <tr>
                <td colspan="5" class="text-center" style="position:relative;">
                    <b style="line-height:30px;">Availability Information</b>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                    Initial Availability For Period
                </td>
                <td class="text-justify" style="position:relative;">
                   
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ isset($totalAvailabilityMapping[$provider->id]['initialAvailabilityLength']) ? ($totalAvailabilityMapping[$provider->id]['initialAvailabilityLength'] / 60) : 0 }} hours
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                    Remaining Availability For Period
                </td>
                <td class="text-justify" style="position:relative;">
                   
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ isset($totalAvailabilityMapping[$provider->id]['remainingAvailabilityLength']) ? ($totalAvailabilityMapping[$provider->id]['remainingAvailabilityLength'] / 60) : 0 }} hours
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                    "Active" Appointments
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ $totalAvailabilityMapping[$provider->id]['activeAppointmentsCount'] ?? 0 }}
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ isset($totalAvailabilityMapping[$provider->id]['activeAppointmentsLength']) ? ($totalAvailabilityMapping[$provider->id]['activeAppointmentsLength'] / 60) : 0 }} hours
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                    "Canceled" Appointments
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ $totalAvailabilityMapping[$provider->id]['canceledAppointmentsCount'] ?? 0 }}
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ isset($totalAvailabilityMapping[$provider->id]['canceledAppointmentsLength']) ? ($totalAvailabilityMapping[$provider->id]['canceledAppointmentsLength'] / 60) : 0 }} hours
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                    "Completed" Appointments
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ $totalAvailabilityMapping[$provider->id]['completedAppointmentsCount'] ?? 0 }}
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ isset($totalAvailabilityMapping[$provider->id]['completedAppointmentsLength']) ? ($totalAvailabilityMapping[$provider->id]['completedAppointmentsLength'] / 60) : 0 }} hours
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                    "Visit Created" Appointments
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ $totalAvailabilityMapping[$provider->id]['visitCreatedAppointmentsCount'] ?? 0 }}
                </td>
                <td class="text-justify" style="position:relative;">
                    {{ isset($totalAvailabilityMapping[$provider->id]['visitCreatedAppointmentsLength']) ? ($totalAvailabilityMapping[$provider->id]['visitCreatedAppointmentsLength'] / 60) : 0 }} hours
                </td>
            </tr>
        </tbody>
    </table>
    <br>
@endif

@if (count($complaints) > 0)
    <br>
    <br>
    <table class="statistic-table table table-condenced table-bordered dataTable mt-3">
        <tbody>
            @foreach ($complaints as $complaint)
                @if ($complaint->provider_id === $provider->id && $complaint->complaint !== null)
                    <tr>
                        <td colspan="5" class="text-center" style="position:relative;">
                            <b style="line-height:30px;">Current Timesheet Comments & Corrections Requested</b>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-justify" style="position:relative;width:79.5%;">
                            <p>{!! str_replace("\n", '<br>', $complaint->complaint) !!}</p>
                        </td>
                        <td class="text-justify" style="position:relative;">

                            <form action="{{ route('dashboard-salary.complaint-reviewed') }}" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" id="provider_id" name="provider_id"
                                    value="{{ $complaint->provider_id }}">
                                <input type="hidden" id="billing_period_id" name="billing_period_id"
                                    value="{{ $complaint->billing_period_id }}">

                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="is_resolve_complaint" id="is_resolve_complaint"
                                            onchange="sendIsResolveComplaint()" value="1"
                                            {{ $complaint->is_resolve_complaint == 1 ? ' checked' : '' }}> Reviewed
                                    </label>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <br>
@endif

@if ((array_key_exists($provider->id, $salary) && count($salary[$provider->id]) > 0) ||
    (int) data_get($missingNotes, $provider->id . '.0.visits_per_month') > 0 ||
    count(data_get($refundsForMissingNotes, $provider->id) ?? []) > 0 ||
    count(data_get($additionalCompensation, $provider->id) ?? []) > 0)
    <details-table download-url="{{ route('dashboard-salary-download', ['id' => $provider->id]) }}"
        sync-url="{{ route('sync-visits') }}" :is-parser-running="{{ $isParserRunning ? 'true' : 'false' }}"
        :provider-id="{{ $provider->id }}"
        :form-data="{
            selectedFilterType: '{{ $selectedFilterType }}',
            dateFrom: '{{ $dateFrom }}',
            dateTo: '{{ $dateTo }}',
            month: '{{ $month }}',
            billingPeriodId: '{{ $billingPeriodId }}'
        }" />
@endif

