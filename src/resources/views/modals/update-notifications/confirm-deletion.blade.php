<div
    id="confirm-deleting-dialog"
    class="modal modal-vertical-center fade"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
    data-backdrop="static"
    data-keyboard="false"
>
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-head">Confirm operation</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="user_id">
                <input type="hidden" id="provider_id">
                {{ $message ?? 'Are you sure you want to continue?' }}
            </div>
            <div class="modal-footer">
                <button type="button" id="confirm-deleting-btn" class="btn btn-primary">Yes</button>
                <button type="button" id="cancel-deleting-btn" class="btn btn-secondary" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>
