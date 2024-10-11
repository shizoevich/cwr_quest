<template>
    <div
        id="timesheet-confirmation-attention"
        class="modal modal-vertical-center fade"
        data-backdrop="static"
        data-keyboard="false"
        role="dialog"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" aria-label="Close" @click="onClose">
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <h4 class="modal-head">Timesheet</h4>
                </div>
                <div class="modal-body">
                    <h5>
                        Dear CWR Staff Member, <br/>
                        We kindly remind you to fill in your timesheet for the past pay period. <br/><br/>
                        PLEASE NOTE: You can postpone this task and be reminded in two hours by clicking the "Remind Me Later" button. No further reminders will be sent if you close this notification by clicking the "X" in the top right corner. <br/><br/>
                        <b v-if="submitRequiredDate">IMPORTANT! The option to postpone timesheet submission will be available until {{ submitRequiredDate.format('hh:mm A') }} on {{ submitRequiredDate.format('dddd') }} ({{ submitRequiredDate.format('MMMM') }} {{ submitRequiredDate.format('DD') }}, {{ submitRequiredDate.format('YYYY') }}). After this, the system access will be restricted until the timesheet submission has been completed.</b>
                    </h5>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary" href="/salary/time-records">Go to Timesheet</a>
                    <button class="btn btn-secondary" @click="onRemindLater">Remind Me Later</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BootstrapModal from '../mixins/bootstrap-modal';

export default {
    mixins: [
        BootstrapModal,
    ],
    data: () => ({
        submitRequiredDate: null,
    }),
    computed: {
        currentProvider() {
            return this.$store.state.currentProvider;
        },
    },
    methods: {
        openModal() {
            $('#timesheet-confirmation-attention').modal('show');
        },
        closeModal() {
            $('#timesheet-confirmation-attention').modal('hide');
        },
        onClose() {
            this.closeModal();
            this.markAsViewed();
        },
        onRemindLater() {
            this.closeModal();
            this.remindLater();
        },
        markAsViewed() {
            this.$store.dispatch('timesheetNotificationMarkAsViewed')
        },
        remindLater() {
            this.$store.dispatch('timesheetNotificationRemindLater');
        },
    },
    mounted() {
        if(this.$route.path.includes('update-notifications/history')) {
            return;
        }

        this.$store.dispatch('getTimesheetConfirmation').then(response => {
            if(response.data.confirmed) {
                return;
            }

            if(response.data.submit_required_date) {
                this.submitRequiredDate = moment(response.data.submit_required_date, 'MM/DD/YYYY hh:mm A');
            }
            
            this.openWithoutOverlapping();
        });
    }
}
</script>

