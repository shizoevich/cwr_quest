@extends('layouts.app')

@section('content')
    <div class="wrapper" id="salary-wrapper">
        <div class="container">
            @if (session('message'))
                <div class="row">
                    <div class="col-xs-12">
                        <div class="alert alert-success alert-dismissible">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{ session('message') }}
                        </div>
                    </div>

                </div>
            @endif
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="text-center">
                        New
                    </h2>
                </div>
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @if(!count($newRequests))
                                <div class="alert alert-info" style="margin-bottom:0;">
                                    Removal Request list is empty.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-condensed table-striped remove-request-table"
                                        id="new-requests-table">
                                        <thead>
                                        <tr>
                                            <th>Therapist Name</th>
                                            <th>Patient Name</th>
                                            <th>Reason</th>
                                            <th>Requested At</th>
                                            <th style="width:150px;"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($newRequests as $item)
                                            <tr>
                                                <td>{{ $item->provider->name }}</td>
                                                <td>
                                                    <a href="/chart/{{ $item->patient->id }}"
                                                       style="color:{{ '#' . $item->patient->status->hex_color }};">
                                                        {{ $item->patient->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $item->reason }}</td>
                                                <td data-order="{{ $item->created_at->timestamp }}">
                                                    {{ $item->created_at->format('m/d/Y h:i:s A') }}
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-success"
                                                            data-request-id="{{ $item->id }}"
                                                            onclick="showConfirmAcceptionModal('{{ $item->id }}')">
                                                        Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger"
                                                            data-request-id="{{ $item->id }}"
                                                            onclick="showConfirmCancellationModal('{{ $item->id }}')">
                                                        Decline
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <h2 class="text-center">
                        Patients Removal History
                    </h2>
                </div>
                <div class="col-xs-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            @if(!count($checkedRequests))
                                <div class="alert alert-info" style="margin-bottom:0;">
                                    Removal Request list is empty.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-condensed table-striped remove-request-table"
                                           id="old-requests-table">
                                        <thead>
                                        <tr>
                                            <th>Therapist Name</th>
                                            <th>Patient Name</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Approved by</th>
                                            <th>Comment</th>
                                            <th>Date of Removal</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($checkedRequests as $item)
                                            <tr>
                                                <td>{{ $item->provider->name }}</td>
                                                <td>
                                                    <a href="/chart/{{ $item->patient->id }}"
                                                       style="color:{{ '#' . $item->patient->status->hex_color }};">
                                                        {{ $item->patient->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $item->reason }}</td>
                                                <td>{{ $item->status_text }}</td>
                                                <td>
                                                    @if($item->status == \App\Models\Patient\PatientRemovalRequest::STATUS_CANCELED_BY_THERAPIST)
                                                        {{ $item->provider->name }}
                                                    @elseif(!is_null($item->approver) && !is_null($item->approver->meta))
                                                        {{ $item->approver->meta->name }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $item->approver_comment }}
                                                </td>
                                                <td data-order="@if(!is_null($item->approved_at)){{ $item->approved_at->timestamp }}@endif">
                                                    @if(!is_null($item->approved_at))
                                                        {{ $item->approved_at->format('m/d/Y h:i:s A') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                {{ $checkedRequests->links() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--Modals-->
    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         id="accept-remove-request-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Removal Request</h5>
                </div>
                <div class="modal-body">
                    <p>You are about to remove the patient from the list. Please confirm.</p>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('patient-removal-requests.accept') }}" method="post">
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}
                        <input type="hidden" name="request_id" value="">
                        <button type="submit" class="btn btn-primary">
                            Confirm
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <cancel-remove-request csrf="{{ csrf_token() }}"></cancel-remove-request>
@endsection

@section('scripts')
    @parent
    <script>

        $('#new-requests-table').DataTable({
            lengthChange: false,
            ordering: true,
            paging: false,
            info: false,
            order: [
                [3, 'desc'],
            ],
            columns: [
                null,
                null,
                null,
                null,
                { searchable: false, sortable: false, },
            ]
        });

        $('#old-requests-table').DataTable({
            lengthChange: false,
            ordering: true,
            paging: false,
            info: false,
            order: [
                [6, 'desc'],
            ],
        });

        function showConfirmAcceptionModal(request_id) {
            let $modal = $('#accept-remove-request-modal');
            $modal.find('input[name="request_id"]').val(request_id);
            $modal.modal('show');
        }

        function showConfirmCancellationModal(request_id) {
            let $modal = $('#cancel-remove-request-modal');
            $modal.find('input[name="request_id"]').val(request_id);
            $modal.modal('show');
        }
    </script>
@endsection