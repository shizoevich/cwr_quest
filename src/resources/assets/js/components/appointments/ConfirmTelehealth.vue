<template>
    <div class="modal appt-modal-vertical-center fade appt-modal appt-modal-success appt-telehealth" id="confirm-telehealth" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click.prevent="closeModal()">&times;</button>
                    <h4 class="modal-title">
                        Complete Appointment
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <p class="">Was this a Telehealth session?</p>
                        </div>
                        <div class="col-xs-12">
                            <div class="comment-block">
                                <button role="button" class="btn appt-modal-btn btn-yes" @click.prevent="telehealthConfirmed(true)">Yes</button>
                                <button role="button" class="btn appt-modal-btn btn-no" @click.prevent="telehealthConfirmed(false)">No</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  export default {
    name: "ConfirmTelehealth",
    methods: {
      telehealthConfirmed(status) {
        this.closeModal();
        this.$emit('telehealth-confirmed', status);
      },
      closeModal() {
        $('#confirm-telehealth').modal('hide');
      }
    },

  }
</script>

<style scoped>
    .btn-yes {
        background-color: #02a756 !important;
    }

    .btn-no {
        background-color: #d9534f !important;
    }

</style>