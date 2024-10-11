<div
    id="confirmDialogSupervising"
    class="modal modal-vertical-center fade"
    tabindex="-1"
    role="dialog"
    aria-labelledby="confirmDialogLabel"
    aria-hidden="true"
    data-backdrop="static"
    data-keyboard="false"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDialogLabel"></h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="provider_id">
                <input type="hidden" id="supervisor_id">
                <span id="assign_message"></span>
                <br/>
                <span>Please choose the date starting from which the changes will take effect.</span>
                <br/><br/>
                <div class="form-group date-form-group date-filter-item">
                    <label class="control-label">Date</label>
                    <form-datepicker
                        name="date"
                        date-format="MM/dd/yyyy"
                        default-value="{{ \Carbon\Carbon::now()->format('m/d/Y')}}"
                        from-date="{{ \Carbon\Carbon::now()->format('m/d/Y')}}"
                    />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="cancel-selection-supervising" class="btn btn-secondary">Close</button>
                <button type="button" id="confirm-selection-supervising" class="btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>
</div>
