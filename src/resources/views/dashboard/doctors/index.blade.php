@extends('layouts.app')

@section('content')
    @include('modals.confirm-deletion-user')
    @include('modals.secretary-edit')
    @include('modals.tridiuum-credentials')
    <div class="modal modal-vertical-center fade" id="confirmDialog" tabindex="-1" role="dialog"
         aria-labelledby="confirmDialogLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDialogLabel"></h5>
                    {{--<button type="button" class="close" data-dismiss="modal" aria-label="Close">--}}
                    {{--<span aria-hidden="true">&times;</span>--}}
                    {{--</button>--}}
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id">
                    <input type="hidden" id="provider_id">
                    Are you sure you want to assign <span class="text-bold" id="user-name"></span> to <span
                            class="text-bold" id="provider-name"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirm-selection" class="btn btn-primary">Yes</button>
                    <button type="button" id="cancel-selection" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-vertical-center fade" id="confirmDisablingDialog" tabindex="-1" role="dialog"
         aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="user_id">
                    <input type="hidden" id="provider_id">
                    Are you sure you want to enable/disable <span class="text-bold"
                                                                                   id="user-name2"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirm-disabling-user" class="btn btn-primary">Yes</button>
                    <button type="button" id="cancel-disabling-user" class="btn btn-secondary" data-dismiss="modal">No
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="wrapper">
        <div class="container">
            <div class="row">
                @if(\Auth::user()->isAdmin())
                <div class="col-md-12">
                    <a href="{{ route('dashboard.users.create') }}" class="btn btn-primary">Create User</a>
                </div>
                @endif
                <div class="col-md-12">
                    <div id="status-alert"></div>

                    <div class="table-wrapper">
                        {{-- @if(count($users) > 0)
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="first-name">Provider</th>
                                    <th>Email</th>
                                    <th class="status">Tridiuum</th>
                                    @if(auth()->user()->isOnlyAdmin())
                                        <th class="role">Role</th>
                                    @endif
                                    <th class="text-center actions">Actions</th>
                                </tr>
                                </thead>
                            <tbody>
                            @foreach ($users as $index => $user)
                                <tr class="{{ $user->isProviderAttached() || $user->isSecretary() || $user->isPatientRelationManager() ? '' : 'danger' }}">
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td id="user-name-{{ $user->id }}">
                                        @if($user->isSecretary())
                                            {{ $user->secretary_name }} {{ $user->secretary_lastname }} 
                                        @else
                                            {{ $user->isProviderAttached() ? $user->provider->provider_name : '' }}
                                        @endif
                                    </td>
                                    <td><a href="mailto:{{ $user->email }}" class="user-email">{{ $user->email }}</a></td>
                                    <td class="tridiuum {{optional($user->provider)->tridiuum_username ? 'text-success' :'text-danger'}}">
                                        @if(!$user->isAdmin())
                                            @if(optional($user->provider)->tridiuum_username)
                                                active
                                            @else
                                                @if($user->provider)
                                                    <button class="btn btn-primary add-tridiuum-credentials" data-user-id="{{$user->getKey()}}">add credentials</button>
                                                @else
                                                    need provider
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    @if(auth()->user()->isOnlyAdmin())
                                        <td>
                                            <select name="secretary" data-placement="top" data-trigger="focus" {{ $user->trashed() ? 'disabled' : '' }} id="select-role-{{ $user->id }}" data-user="{{ $user->id }}" class="form-control select-role">
                                                <option value="user">User</option>
                                                <option value="secretary" @if($user->isSecretary()) selected @endif >Secretary</option>
                                            </select>
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        <a @if ($user->isSecretary()) class="edit-secretary-action" @endif
                                            id="user-edit-{{ $user->id }}"
                                            href="{{ route('profile.index', ['id' => $user->id]) }}" 
                                            data-user="{{ $user->id }}"
                                            >
                                            <span class="glyphicon glyphicon-pencil"></span>
                                        </a>
                                        @if($user->isProviderAttached())
                                        <a class="enable-disable" data-mode="{{$user->trashed() ? 'enable':'disable' }}" data-user="{{$user->id}}" href="javascript:void(0);">
                                            <span class="glyphicon glyphicon-{{$user->trashed() ?'refresh':'remove'}}"></span>
                                        </a>
                                        @else
                                            <a class="show-confirm-deletion-user-modal" data-user="{{$user->id}}" href="javascript:void(0);">
                                                <span class="glyphicon glyphicon-remove"></span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            </table>
                            {{ $users->links() }}
                        @else
                            <div class="alert alert-info" role="alert">
                                <h5>User list is empty.</h5>
                            </div>
                        @endif --}}

                        <table class="table" id="doctorsTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Provider</th>
                                    <th class="email">Email</th>
                                    <th>Tridiuum</th>
                                    <th>Status</th>
                                    <th>Supervision</th>
                                    <th>Total time in CWR</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/doctor-provider_profile-tridiuum.js') }}"></script>
    <script src="{{ asset('js/doctor-provider_relationship.js') }}"></script>
    <script src="{{ asset('js/doctors-sort.js') }}"></script>
@endsection