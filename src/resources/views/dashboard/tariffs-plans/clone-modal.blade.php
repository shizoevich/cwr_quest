<div class="modal fade" id="clone-tariff-plan-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        {{ Form::open(['url' => url('dashboard/tariffs-plans'),'method' => 'POST' ]) }}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Duplicate tariff plan</h4>
            </div>
            <div class="modal-body">
                <span></span>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Name">
                </div>
                <input type="text" class="hidden" name="tariff_plan_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Duplicate</button>
            </div>
        </div>
        {{ Form::close() }}

    </div>
</div>