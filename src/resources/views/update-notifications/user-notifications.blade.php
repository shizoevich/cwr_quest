@extends('layouts.app')

@section('content')
    @include('modals.update-notifications.preview', ['closeOnly' => true])

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div id="status-alert"></div>

                    <div class="table-wrapper">
                        <table id="notificationsTable" class="table table-clickable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Required</th>
                                    <th>Opened At</th>
                                    <th>Confirmed At</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="{{ asset('js/update-notifications/user-notifications.js') }}"></script>
@endsection