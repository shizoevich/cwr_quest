<template>
    <div class="google-meet-appointments">
        <div class="google-meet-appointments-item">
            <template v-if="Object.keys(appointment).length">
                <div class="google-meet-appointments-item__title">
                    Appointment
                </div>
                <el-collapse class="collapse-appointments" v-model="activeAppointment">
                    <el-collapse-item class="collapse-appointments-item" :name="appointment.id" :disabled="true">
                        <template slot="title">
                            <div class="collapse-appointments-item__title">
                                <div class="collapse-appointments-item__title-description">
                                    <p>
                                        {{ appointmentTime(appointment.date_of_service) }}
                                    </p>
                                    <div v-html="appointmentInvite(appointment.upheal_meet)"/>
                                </div>
                            </div>
                        </template>
                    </el-collapse-item>
                </el-collapse>
            </template>
            <template v-else>
                <div class="google-meet-appointments-no-data">
                    No data, please create an appointment first
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import GoogleMeetAppointments from '../../mixins/google-meet-appointments';

    export default {
        name: 'UphealDialogVideoSession',
        mixins: [GoogleMeetAppointments],
        props: {
            appointment: {
                type: Object,
                default() {
                    return {};
                }
            }
        },
        data() {
            return {
                activeAppointment: [],
            }
        },
    }
</script>
