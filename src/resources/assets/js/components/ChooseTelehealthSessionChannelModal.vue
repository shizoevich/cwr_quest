<template>
    <div style="display: inline-block;">
        <el-dialog
            title="Start Telehealth Session"
            :visible.sync="is_show_choose_telehealth_session_modal"
            :close-on-click-modal="false"
            class="patient-dialog bootstrap-modal choose-telehealth-session-channel__dialog"
        >
            Please Choose an Option
            <div class="form-footer">
                <div class="form-footer-control" v-if="provider && provider.upheal_user_id">
                    <el-button type="primary" @click="startGoogleMeetSession">Google Meet Session</el-button>
                    <el-button type="warning" @click="startUphealSession">Upheal Session</el-button>
                    <el-button type="success" @click="openPhoneCallModal">Phone Call</el-button>
                </div>
                <div class="form-footer-control" v-else>
                    <el-button type="primary" @click="startGoogleMeetSession">Video Session</el-button>
                    <el-button type="success" @click="openPhoneCallModal">Phone Call</el-button>
                </div>
            </div>
        </el-dialog>

        <RingOutModal
            v-if="is_show_phone_call_modal"
            :is-show-modal="is_show_phone_call_modal"
            :appointment-id="appointment.id"
            @closeModal="closePhoneCallModal"
            @telehealth-confirmed="telehealthConfirmed"
        />
    </div>
</template>

<script>
import RingOutModal from './RingOutModal';

export default {
    components: {RingOutModal},
    props: {
        showChooseTelehealthSessionModal: {
            required: true,
            type: Boolean,
        },
        appointment: {
            required: true,
            type: Object,
        },
        patient: {
            required: true,
            type: Object,
        },
        provider: {
            type: Object,
        }
    },

    data() {
        return {
            is_show_phone_call_modal: false,
        };
    },

    computed: {
        is_show_choose_telehealth_session_modal: {
            get() {
                return this.showChooseTelehealthSessionModal;
            },
            set(value) {
                if (!value) {
                    this.$emit('closeTelehealthChannelModal');
                }
            }
        },
    },

    methods: {
        startGoogleMeetSession() {
            this.closeChooseTelehealthSessionChannelDialog();
            this.$store.dispatch('setVideoSessionAppointment', {patient: this.patient, appointment_id: this.appointment.id});
        },

        startUphealSession() {
            this.closeChooseTelehealthSessionChannelDialog();
            this.$store.dispatch('setVideoSessionAppointment', {patient: this.patient, appointment_id: this.appointment.id, type: 'upheal'});
        },

        telehealthConfirmed(status) {
            this.$emit('telehealth-confirmed', status);
        },

        openPhoneCallModal() {
            this.is_show_phone_call_modal = true;
            this.closeChooseTelehealthSessionChannelDialog();
        },

        closePhoneCallModal() {
            this.is_show_phone_call_modal = false;
            this.$emit('closePhoneCallModal');
        },

        closeChooseTelehealthSessionChannelDialog() {
            this.is_show_choose_telehealth_session_modal = false;
        },
    },
}
</script>