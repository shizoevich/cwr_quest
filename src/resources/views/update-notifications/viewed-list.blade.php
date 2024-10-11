@extends('layouts.app')

@section('content')
    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div id="status-alert"></div>

                    <div class="table-wrapper">
                        <table id="notificationsTable" class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
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
    <script src="{{ asset('js/update-notifications/viewed-list.js') }}"></script>
@endsection