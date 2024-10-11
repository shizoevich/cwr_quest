<template>
  <div
    class="modal appt-modal-vertical-center fade appt-modal appt-modal-success"
    id="no-past-appointments"
    data-backdrop="static"
    data-keyboard="false"
  >
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">No appointments with "Active" status</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-xs-12">
              <p class="">
                You currently have no appointments with “Active” status on
                schedule prior to today’s date that require your attention.
              </p>
            </div>
            <div class="col-xs-12">
              <div class="comment-block">
                <a href="/" role="button" class="btn btn-success btn-ok">
                  OK
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style scoped lang="scss">
.btn-ok {
  border-color: #02a756;
  background-color: #02a756;
  min-width: 100px;
}

.modal-body {
  padding: 15px;

  p {
    font-size: 14px;
  }
}
</style>
