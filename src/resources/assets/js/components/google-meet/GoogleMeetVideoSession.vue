<template>
    <div class="google-meet-appointments">
        <div class="google-meet-appointments-item">
            <template v-if="Object.keys(appointment).length">
                <div class="google-meet-appointments-item__title">
                    Appointment
                </div>
                <el-collapse class="collapse-appointments"
                             v-model="activeAppointment">
                    <el-collapse-item class="collapse-appointments-item"
                                      :name="appointment.id" :disabled="true">
                        <template slot="title">
                            <div class="collapse-appointments-item__title">
                                <div class="collapse-appointments-item__title-description">
                                    <p>
                                        {{appointmentTime(appointment.date_of_service) }}
                                    </p>
                                    <div v-html="appointmentInvite(appointment.google_meet)"/>
                                    <el-checkbox style="margin-top:8px;" v-model="appointment.allow_to_join_by_phone">Allow to Join by Phone</el-checkbox>
                                    <p class="help-block"
                                       style="margin-top:0;font-weight:normal;font-size:11px;">
                                      Checking the "Allow to Join by Phone" box above will allow patients to join this Telehealth session by dialing a phone number provided and entering a PIN code included in this invitation.</p>
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
        name: 'GoogleMeetVideoSession',
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
