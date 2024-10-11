<div class="">
    <div class="panel-heading">
        <div class="row">
            <div class="col-xs-12 text-center">
                <h4>
                    Tridiuum credentials
                </h4>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <form class="tridiumm-form" id="profileTridiuum" method="POST" action="{{route('profile.store_tridiuum')}}" novalidate>
            
            {{ csrf_field() }}
            <input name="user_id" value="{{ $user->id }}" hidden/>
            <div class="hide-loader">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group{{ $errors->has('tridiuum_username') ? ' has-error' : '' }}">
                            <label for="tridiuum_username" class="control-label">Username</label>
                            <input type="text" class="form-control" id="tridiuum_username" name="tridiuum_username" placeholder=""
                                   value="{{ old('tridiuum_username', isset($provider->tridiuum_username) ? $provider->tridiuum_username : '')}}"
                                   required
                                    {{ $edit ? "" : "disabled" }}
                            >
    
                            <span class="help-block with-errors">
                        @if ($errors->has('tridiuum_username'))
                                    <strong>{{ $errors->first('tridiuum_username') }}</strong>
                                @endif
                        </span>
                        </div>
                    </div>
                </div>
    
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group{{ $errors->has('tridiuum_password') ? ' has-error' : '' }}">
                            <label for="tridiuum_password" class="control-label">Password</label>
                            <input type="text" class="form-control" id="tridiuum_password" name="tridiuum_password"
                                   value="{{$provider->tridiuum_password ? decrypt($provider->tridiuum_password) : '' }}"
                                   required
                                    {{ $edit ? "" : "disabled" }}
                            >
    
                            <span class="help-block with-errors">
                        @if ($errors->has('tridiuum_password'))
                                    <strong>{{ $errors->first('tridiuum_password') }}</strong>
                                @endif
                        </span>
                        </div>
                    </div>
                </div>
    
                <button type="submit" class="btn btn-primary">Save</button>
                @if(isset($provider->tridiuum_password) && isset($provider->tridiuum_username))
                    <button type="button" class="btn btn-danger"
                            id="confirm-delete-tridiuum"
                            data-toggle="modal"
                            data-target="#confirm-delete-tridiuum-modal">Delete credentials</button>
                    @include('profile.modals.confirm-delete')
                @endif
            </div>
            <div class="show-loader">
                <img src="/images/pageloader.gif" alt="">
            </div>
        </form>

        @if($kaiserAppointments->isNotEmpty())
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient</th>
                        <th>Start Date</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($kaiserAppointments as $index => $kaiserAppointment)
                        <tr>
                            <td>{{ count($kaiserAppointments) - $index }}</td>
                            <td>
                                @if($kaiserAppointment->patient)
                                    @if($kaiserAppointment->patient->patient_id != 11111111 )
                                        <a href="/chart/{{ $kaiserAppointment->patient->id }}" target="_blank">
                                            {{ $kaiserAppointment->patient->getFullName() }}
                                        </a>
                                    @else
                                        {{ $kaiserAppointment->patient->getFullName() }}
                                    @endif
                                @else
                                    {{ $kaiserAppointment->first_name }} {{ $kaiserAppointment->last_name }}
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($kaiserAppointment->start_date)->format('m/d/Y h:i A') }}</td>
                            <td>{{ \Carbon\CarbonInterval::minutes($kaiserAppointment->duration)->forHumans() }}</td>
                            <td>
                                @if($kaiserAppointment->patient)
                                    {{ $kaiserAppointment->status_label }}
                                @else
                                    <p style="color:red;">The patient has not been created in OA yet, but would be created automatically soon</p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>

@section('scripts')
    @parent
    <script src="{{ asset('js/doctor-provider_profile-tridiuum.js') }}"></script>
@endsection