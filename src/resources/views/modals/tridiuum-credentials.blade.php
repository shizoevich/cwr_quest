<div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" id="tridiuum-credentials-modal" tabindex="-1"
     role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tridiuum</h4>
            </div>
            <div class="modal-body">
                <form class="tridiumm-form" method="POST" action="{{route('profile.store_tridiuum')}}">
                    {{ csrf_field() }}
                    <input name="user_id" value="" hidden/>
                        <div class="hide-loader">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="tridiuum_username" class="control-label">Username</label>
                                        <input type="text" class="form-control" id="tridiuum_username" name="tridiuum_username" placeholder="" required>
                                        <span class="help-block with-errors"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="tridiuum_password" class="control-label">Password</label>
                                        <input type="text" class="form-control" id="tridiuum_password" name="tridiuum_password" placeholder="" required>
                                        <span class="help-block with-errors"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="show-loader">
                            <img src="/images/pageloader.gif" alt="">
                        </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="tridiuum-save" class="btn btn-danger">Save</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>