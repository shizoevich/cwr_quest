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
            
            <div class="modal-footer">
                @if ($closeOnly ?? false)
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                @else
                <button type="button" id="submit-btn" class="btn btn-primary">Submit</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                @endif
            </div>
        </div>
    </div>
</div>
