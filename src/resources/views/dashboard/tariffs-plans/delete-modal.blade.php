<div class="modal fade" id="delete-tariff-plan-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        {{ Form::open(['url' => url('dashboard/tariffs-plans'),'method' => 'delete' ,'data-default_url' => url('dashboard/tariffs-plans')]) }}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Delete tariff plan</h4>
            </div>
            <div class="modal-body">
                <span>

                </span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</div>