<button type="button" id="invite" class="btn btn-primary btn-large pull-right">Invite</button>

<div class="modal modal-vertical-center fade" id="invite-dialog" tabindex="-1" role="dialog"
     aria-labelledby="invite-dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invite-dialog"></h5>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                    <span class="help-block">
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="send" class="btn btn-primary">Send</button>
                <button type="button" id="cancel" class="btn btn-secondary" data-dismiss="modal">Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    @parent
    <script src="{{ asset('js/invite-btn.js') }}"></script>
@endsection