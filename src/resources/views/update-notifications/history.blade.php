@extends('layouts.app')

@section('content')
    <div
        id="preview-dialog"
        class="modal modal-vertical-center fade"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <h4 class="modal-head"></h4>
                </div>

                <div class="modal-body"></div>
                
                <div class="modal-footer modal-footer--not-viewed">
                    <div class="form-group notification-viewed-field">
                        <label>
                            <input type="checkbox" id="notification-viewed-checkbox">
                            <div>
                                I, <span id="notification-viewed-user"></span>, certify that I have read and understand the contents of this notification. <br/>
                                By checking this box, I acknowledge the following:
                                <ul>
                                    <li>I have reviewed and understand the information provided above.</li>
                                    <li>My electronic signature will be recorded and stored on file for compliance purposes.</li>
                                </ul>
                            </div>
                        </label>
                    </div>

                    <button type="button" id="notification-viewed-btn" class="btn btn-primary" disabled>Confirm</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
                <div class="modal-footer modal-footer--viewed">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div id="status-alert"></div>

                    <div class="table-wrapper">
                        <table id="notificationsTable" class="table table-clickable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Required</th>
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
    <script src="{{ asset('js/update-notifications/history.js') }}"></script>
@endsection