<div class="modal modal-vertical-center fade" tabindex="-1" role="dialog" id="confirm-delete-tridiuum-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Delete Tridiuum Credentials</h4>
        </div>
        <div class="modal-body">
            Are you sure you want to delete Tridiuum credentials of this profile?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-user="{{ $user->id }}" 
                    data-url="{{ route('profile.delete_tridiuum') }}"
                    id="tridiuumCredentialsDelete">Confirm</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->