@extends('layouts.app')

@section('content')
    @include('modals.update-notifications.confirm-deletion', ['message' => 'Are you sure you want to delete this template?'])
    @include('modals.update-notifications.preview', ['closeOnly' => true])

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <a href="/update-notification-templates/create" class="btn btn-primary">Create Template</a>
                </div>
                
                <div class="col-md-12">
                    <div id="status-alert"></div>

                    <div class="table-wrapper">
                        <table id="templatesTable" class="table table-clickable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Notification Title</th>
                                    <th>Actions</th>
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
    <script src="{{ asset('js/update-notification-templates/index.js') }}"></script>
@endsection