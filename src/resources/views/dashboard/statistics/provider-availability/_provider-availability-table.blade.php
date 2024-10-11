<table class="statistic-table table table-condenced table-bordered dataTable mt-3">
    <tbody>
        <tr>
            <td colspan="5" class="text-center" style="position:relative;">
                <b style="line-height:30px;">Availability Information</b>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                Remaining Availability
            </td>
            <td class="text-justify" style="position:relative;">
                
            </td>
            <td class="text-justify" style="position:relative;">
                {{ isset($provider->total_availability['remainingAvailabilityLength']) ? $provider->total_availability['remainingAvailabilityLength'] / 60 : 0 }} hours 
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                "Active" Appointments
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $provider->total_availability['activeAppointmentsCount'] ?? 0 }}
            </td>
            <td class="text-justify" style="position:relative;">
                {{ isset($provider->total_availability['activeAppointmentsLength']) ? $provider->total_availability['activeAppointmentsLength'] / 60 : 0 }} hours
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                "Canceled" Appointments
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $provider->total_availability['canceledAppointmentsCount'] ?? 0 }}
            </td>
            <td class="text-justify" style="position:relative;">
                {{ isset($provider->total_availability['canceledAppointmentsLength']) ? $provider->total_availability['canceledAppointmentsLength'] / 60 : 0 }} hours
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                "Completed" Appointments
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $provider->total_availability['completedAppointmentsCount'] ?? 0 }}
            </td>
            <td class="text-justify" style="position:relative;">
                {{ isset($provider->total_availability['completedAppointmentsLength']) ? $provider->total_availability['completedAppointmentsLength'] / 60 : 0 }} hours
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                "Visit Created" Appointments
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $provider->total_availability['visitCreatedAppointmentsCount'] ?? 0 }}
            </td>
            <td class="text-justify" style="position:relative;">
                {{ isset($provider->total_availability['visitCreatedAppointmentsLength']) ? $provider->total_availability['visitCreatedAppointmentsLength'] / 60 : 0 }} hours
            </td>
        </tr>

        @php
            $totalLength = 0;
            if (isset($provider->total_availability['remainingAvailabilityLength'])) {
                $totalLength += $provider->total_availability['remainingAvailabilityLength'];
            }
            if (isset($provider->total_availability['activeAppointmentsLength'])) {
                $totalLength += $provider->total_availability['activeAppointmentsLength'];
            }
            if (isset($provider->total_availability['canceledAppointmentsLength'])) {
                $totalLength += $provider->total_availability['canceledAppointmentsLength'];
            }
            if (isset($provider->total_availability['completedAppointmentsLength'])) {
                $totalLength += $provider->total_availability['completedAppointmentsLength'];
            }
            if (isset($provider->total_availability['visitCreatedAppointmentsLength'])) {
                $totalLength += $provider->total_availability['visitCreatedAppointmentsLength'];
            }
        @endphp

        <tr style="background-color:#f9f9f9; {{ ($totalLength / 60) < $provider->minimum_work_hours ? 'color:#fb0007;' : 'color:#02a756;' }}">
            <td colspan="2"><b>Total</b></td>
            <td></td>
            <td><b>{{ $totalLength / 60 }} hours</b></td>
        </tr>
    </tbody>
</table>
<br>

<table class="statistic-table table table-condenced table-bordered dataTable mt-3">
    <tbody>
        <tr>
            <td colspan="5" class="text-center" style="position:relative;">
                <b style="line-height:30px;">Additional Information About Therapist</b>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="text-justify" style="position:relative; width:79.5%;" >
                <span>
                    <help :offset="10" content="{{ $provider->work_hours_per_week }} work hours per week" />
                </span>
                Minimum Work Hours For Period
            </td>
            <td class="text-justify" style="position:relative;">
                {{ $provider->minimum_work_hours }} 
            </td>
        </tr>
    </tbody>
</table>
<br>

<provider-availability-detail
    :provider-id="{{ $provider->id }}"
>
    @if (isset($provider->user))
        <template v-slot:buttons>
            <div style="flex-grow:1;">
                @if (auth()->user()->isOnlyAdmin())
                <a href="/update-notifications/create?user_id={{ $provider->user->id }}" class="btn btn-primary">Notify</a>
                @endif
                <a href="/users/{{ $provider->user->id }}/update-notifications" class="btn btn-primary">Notifications List</a>
            </div>
        </template>
    @endif
</provider-availability-detail>
