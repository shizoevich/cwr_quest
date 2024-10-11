<template>
  <div
    id="alertModal"
    class="modal modal-vertical-center fade"
    data-backdrop="static"
    data-keyboard="false"
    tabindex="-1"
    role="dialog"
  >
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <span class="counter">{{ skipped_alert_modals + 1 }}/{{ alertModals.length }}</span>
          <h4 class="modal-title">Send Document</h4>
        </div>
        <div class="modal-body">
          <p v-if="alertModals && alertModals[skipped_alert_modals]">
            Are you pretty sure that the next form is not necessary for sending <b>{{ alertModals[skipped_alert_modals] }}</b>?
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" @click="showNextAlertModal">
            Yes
          </button>
          <button
            class="btn btn-default"
            @click="closeModal"
          >
            No
          </button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
</template>

<script>

export default {
  name: "DocumentAlertModal",
  props: {
    alertModals: {
      type: Array,
      required: true,
    }
  },

  data() {
    return {
      skipped_alert_modals: 0,
    }
  },

  mounted() {

  },
  computed: {

  },
  methods: {
    showNextAlertModal() {
      if(this.alertModals.length - 1 === this.skipped_alert_modals) {
        this.closeModal(false);
        this.$emit('allModalsSkipped');
      } else {
        this.skipped_alert_modals++;
      }
    },
    closeModal(emit = true) {
      $('#alertModal').modal('hide');
      this.skipped_alert_modals = 0;
      if(emit) {
        this.$emit('close');
      }
    },
  },
  watch: {

  },
};
</script>

<style scoped>
  .counter {
    float: right;
    font-size: 18px;
  }
</style>
