<div class="modal fade" id="ungroup-insurance-plans-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        {{ Form::open(['url' => route('dashboard-ungroup-insurance-plans'),'method' => 'POST' ]) }}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Ungroup insurance plans</h4>
            </div>
            <div class="modal-body">
                You want to ungroup plans:
                <ul>
                </ul>

                <span></span>
                <div class="form-group">
                    <input type="text" class="hidden" name="plan_id">
                    <input type="text" class="hidden" name="tariff_plan_id">
                    <input type="text" class="hidden" name="insurance_id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Unroup</button>
            </div>
        </div>
        {{ Form::close() }}

    </div>
</div>