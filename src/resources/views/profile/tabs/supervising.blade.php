<div>
    <p id="supervisor_message" class=""></p>

    <div class="form-group">
        <h5 style="margin-top:0;">
            <strong>Therapist is supervisor:</strong>
        </h5>
        <div class="radio-group">
            <div class="radio-group__item">
                <input
                    id="is_supervisor-yes"
                    type="radio"
                    name="is_supervisor"
                    value="1"
                    data-provider-id="{{ isset($user->provider) ? $user->provider->id : '' }}"
                    {{ (isset($user->provider) && $user->provider->is_supervisor) ? 'checked' : '' }}
                >
                <label for="is_supervisor-yes" class="normal-weight">Yes</label>
            </div>

            <div class="radio-group__item">
                <input
                    id="is_supervisor-no"
                    type="radio"
                    name="is_supervisor"
                    value="0"
                    data-provider-id="{{ isset($user->provider) ? $user->provider->id : '' }}"
                    {{ (isset($user->provider) && !$user->provider->is_supervisor) ? 'checked' : '' }}
                >
                <label for="is_supervisor-no" class="normal-weight">No</label>
            </div>
        </div>
    </div>

    <div class="form-group form-group--half-width supervisors-field {{isset($user->provider) && $user->provider->is_supervisor ? 'hidden' : ''}}">
        <label for="supervisors" class="control-label">Assigned Supervisor:</label>
        <select
            id="supervisors"
            data-provider-id="{{ isset($user->provider) ? $user->provider->id : '' }}"
            name="supervisors" 
            class="form-control"
            data-initial-value="{{isset($currentSupervisor) ? $currentSupervisor->supervisor_id : ''}}"
        >
            <option value="" @if(empty($currentSupervisor)) {{ 'selected' }} @endif>
                Unassigned
            </option>

            @foreach($supervisors as $supervisor)
                <option value="{{ $supervisor->id }}" @if ($supervisor->id == optional($currentSupervisor)->supervisor_id){{ 'selected' }}@endif>
                    {{$supervisor->provider_name}}
                </option>
            @endforeach
        </select>
    </div>

    @if(isset($user->provider) && $user->provider->is_supervisor)
        <div id="supervisees_table_wrapper" class="table-wrapper">
            <h5 style="margin-top:0;">
                <strong>Supervisees:</strong>
            </h5>

            <table id="supervisees_table" class="table table-condenced table-striped table-bordered" style="background-color: #fff;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Provider</th>
                        <th>Assigned at</th>
                        <th>Patients count</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endif

    @include('profile.tabs.confirm-supervisor-modal')
</div>

@section('scripts')
    @parent
    <script src="{{ asset('js/tabs-supervising.js') }}"></script>
@endsection