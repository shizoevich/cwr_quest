<template>
    <el-dialog
            title="Invitation to join in a Google Meet Telehealth Session"
            top="30vh"
            :visible.sync="showDialog"
            class="video-session bootstrap-modal">
        <div class="video-session-body">
            <p>
                Once you complete your telehealth session, you can close this window and change the status
                of this appointment in EHR.
            </p>
            <p>
                <b>IMPORTANT:</b> Please make sure that you have a microphone and speakers or headset
                connected to your computer prior to starting the Telehealth session. Also, keep in mind
                that you will need to click "Admit" button to let your patient in. To learn more about how
                to use Google Meet please go to
                <a href="https://support.google.com/a/users/answer/9282720?hl=en" target="_blank">https://support.google.com/a/users/answer/9282720?hl=en</a>
            </p>
            <div class="text-center">
                <el-button type="success" @click="openVideoSession">CONTINUE</el-button>
            </div>
        </div>
        <div class="video-session-footer">
            <el-button @click="closeDialog">Close</el-button>
        </div>
    </el-dialog>
</template>

<script>
import { eventBus } from '../app';

    export default {
        name: 'StartVideoSession',
        props: {
            isShowDialog: {
                type: Boolean,
                default: false,
            },
            conferenceUri: {
                type: String,
                default: ''
            }
        },
        computed: {
            showDialog: {
                get() {
                    return this.isShowDialog;
                },
                set(value) {
                    if (!value) {
                        this.$emit('closeDialog');
                    }
                }
            },
            appointmentId() {
                return this.$store.state.videoSessionAppointment && this.$store.state.videoSessionAppointment.appointment_id;
            }
        },
        methods: {
            openVideoSession() {
                window.open(this.conferenceUri, '_blank');

                this.$store.dispatch('getAppointmentDialogData', this.appointmentId).then(res => {
                    this.startResetTimerInterval(res.data.appointment.visit_length);
                });
            },

            closeDialog() {
                this.showDialog = false;
            },

            startResetTimerInterval(visitLength) {
                const visit_length_mc = visitLength * 60 * 1000;

                let intervalId = setInterval(() => {
                    eventBus.$emit('reset-logout-timer');
                    this.$store.dispatch('sendEmptyRequest');
                }, 60 * 1000);

                setTimeout(() => {
                    clearInterval(intervalId);
                }, visit_length_mc);
            }
        }
    }
</script>

<style lang="scss">
    .video-session {
        .el-dialog {
            width: 95%;
            max-width: 530px;

            &__body {
                padding-bottom: 20px;
            }
        }

        &-body {
            word-break: break-word;

            p {
                margin-bottom: 15px;
            }
        }

        &-footer {
            padding: 10px 0 0;
            display: flex;
            justify-content: flex-end;
        }
    }
</style>