<template>
    <div class="modal modal-vertical-center fade"
         data-backdrop="static"
         data-keyboard="false"
         id="appointment-notification"
         role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    New Appointments
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Start Date</th>
                            <th>Duration</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="notification in notifications" :key="notification.id">
                            <td>{{notification.id}}</td>
                            <td>
                                {{notification.appointment.patient.first_name}} {{notification.appointment.patient.last_name}}
                            </td>
                            <td>  {{ getFormattedDateTime(notification.appointment.start_time) }}</td>
                            <td>{{notification.appointment.visit_length}} minutes</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" @click="confirmNotifications">Confirm</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Remind later</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DatetimeFormated from '../mixins/datetime-formated';
    import BootstrapModal from '../mixins/bootstrap-modal';

    export default {
        mixins: [
            DatetimeFormated,
            BootstrapModal
        ],
        data() {
            return {
                notifications: []
            };
        },
        methods: {
            loadAppointmentsNotifications(params) {
                axios.get('/provider/availability-calendar/get-notifications', {
                    params: params
                }).then(response => {
                    if (response.data && typeof response.data === 'object' && response.data.length > 0) {
                        this.notifications = response.data;
                        this.openWithoutOverlapping();
                    }
                });
            },
            confirmNotifications() {
                axios.post('/provider/availability-calendar/confirm-notifications', {
                    notifications: this.notifications.map(notification => notification.id),
                }).then(() => {
                    this.closeModal();
                });
            },
            openModal() {
                $('#appointment-notification').modal('show');
            },
            closeModal() {
                $('#appointment-notification').modal('hide');
            }
        },
        mounted() {
            this.loadAppointmentsNotifications();
        }
    }
</script>

