<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close close-confirm-save-fee-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Enter your Password and Date</h4>
            </div>
            <div class="modal-body">
                <span></span>
                <p>Please choose the date starting from which newly assigned Fee Schedule Rate will be used in calculation of Payouts for service, and enter the admin password to confirm and save the new fee amount.</p>
                <div class="form-group date-form-group date-filter-item">
                    <label class="control-label">Date</label>
                    <form-datepicker 
                        name="date"
                        date-format="MM/dd/yyyy"
                        default-value="{{ \Carbon\Carbon::now()->format('m/d/Y')}}"
                    />
                    <span class="help-block with-errors"><strong></strong></span>
                </div>
                <div class="form-group password-form-group">
                    <label for="name" class="control-label">Password</label>
                    <input type="password" class="form-control password" name="password" placeholder="Password" autofocus>
                    <span class="help-block with-errors"><strong></strong></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default close-confirm-save-fee-modal">Close</button>
                <button class="btn btn-primary save-table">Confirm</button>
            </div>
        </div>
    </div>
</div>