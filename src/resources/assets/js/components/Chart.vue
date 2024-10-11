<template>
    <div>
        <sidebar @confirm-patient="setPatient" @showStartVideoSessionModal="showStartVideoSessionModal" @showTelehealthChannelModal="showTelehealthChannelModal"></sidebar>
        
        <router-view @updateAppointments="onUpdateAppointments" @telehealth-confirmed="setTelehealth">
            <slot/>
        </router-view>
        
        <note></note>
        <assessment-form-modal/>
        <complete-appointment :is-telehealth="isTelehealth" :is-data-fetching="fetchingAppointmentData"/>
        <reschedule-appointment/>
        <cancel-appointment/>
        <confirm-telehealth @telehealth-confirmed="setTelehealth"/>
        <!-- <system-messages />-->
        <initial-assessment></initial-assessment>

        <!-- @todo remove google-meet-dialog when "upheal" integration will be finished -->
        <google-meet-dialog
            v-if="googleMeetPatient"
            :key="`google-meet-${googleMeetDialogKey}`"
            :is-show-dialog="isShowGoogleMeetDialog"
            :patient="googleMeetPatient"
            :provider="provider"
            :updateAppointments="updateAppointments"
            :video-session-appointment-id="googleMeetAppointmentId"
            @openAppointmentDialog="openAppointmentDialog"
            @startVideoSession="startVideoSession"
            @closeDialog="closeGoogleMeetDialog"
        />

        <upheal-dialog
            v-if="videoSessionPatient"
            :key="videoSessionDialogKey"
            :is-show-dialog="isShowVideoSessionDialog"
            :patient="videoSessionPatient"
            :provider="provider"
            :updateAppointments="updateAppointments"
            :video-session-appointment-id="videoSessionAppointmentId"
            @openAppointmentDialog="openAppointmentDialog"
            @startVideoSession="startUphealVideoSession"
            @closeDialog="closeVideoSessionDialog"
        />

        <!-- @todo remove start-video-session when "upheal" integration will be finished -->
        <start-video-session
            v-if="isShowStartVideoSession"
            :is-show-dialog="isShowStartVideoSession"
            :conference-uri="videoSessionUrl"
            @closeDialog="closeStartVideoSessionDialog"
        />

        <start-upheal-video-session
            v-if="isShowStartUphealVideoSession"
            :is-show-dialog="isShowStartUphealVideoSession"
            :conference-uri="videoSessionUrl"
            @closeDialog="closeStartUphealVideoSessionDialog"
        />

        <CreateAppointmentModal
            v-if="isShowAppointmentDialog"
            dialog-title="Schedule appointment"
            :google-meet="videoSession"
            :visibleAppointmentModal="isShowAppointmentDialog"
            :isEditable="true"
            :isCreated="true"
            :isTherapist="true"
            @updateAppointments="updateAppointmentsList"
            @close="closeAppointmentDialog"
        />

        <ChooseTelehealthSessionChannelModal
            v-if="chooseTelehealthChannelModalData"
            :showChooseTelehealthSessionModal="isShowChooseTelehealthSessionChannelDialog"
            :appointment="chooseTelehealthChannelModalData.appointment"
            :patient="chooseTelehealthChannelModalData.patient"
            :provider="provider"
            @closeTelehealthChannelModal="closeTelehealthChannelModal"
            @telehealth-confirmed="setTelehealth"
            @closePhoneCallModal="closePhoneCallModal"
        />
    </div>
</template>

<script>
    import GoogleMeetDialog from "./google-meet/GoogleMeetDialog";
    import UphealDialog from "./upheal-dialog/UphealDialog";
    import CreateAppointmentModal from "./appointments/CreateAppointmentModal"; 
    import StartVideoSession from "./StartVideoSession";
    import StartUphealVideoSession from "./StartUphealVideoSession";
    import CommentBlock from "./comments/CommentBlock";
    import { Notification } from "element-ui";

    export default {
        components: {
            CommentBlock,
            GoogleMeetDialog,
            UphealDialog,
            CreateAppointmentModal,
            StartVideoSession,
            StartUphealVideoSession
        },

        data: () => ({
            patient: null,
            telehealth: false,
            fetchingAppointmentData: false,
            start_video_session_with_patient: {},
            start_video_session_with_appointment_id: null,
            isShowAppointmentDialog: false,
            isShowChooseTelehealthSessionChannelDialog: false,
            updateAppointments: false,
            isUpdatedAppointments: false,
            videoSessionUrl: '',
            chooseTelehealthChannelModalData: null,
            videoSession: null,

            isShowStartUphealVideoSession: false,
            isShowVideoSessionDialog: false,
            videoSessionPatient: null,
            videoSessionAppointmentId: null,
            videoSessionDialogKey: 1,

            // @todo remove when "upheal" integration will be finished
            isShowStartVideoSession: false,
            isShowGoogleMeetDialog: false,
            googleMeetPatient: null,
            googleMeetAppointmentId: null,
            googleMeetDialogKey: 1,
        }),
        computed: {
            provider() {
                return this.$store.state.currentProvider;
            },
            isTelehealth: {
                get() {
                    return this.telehealth;
                },
                set(value) {
                    this.telehealth = value;
                }
            },
            videoSessionAppointmentData() {
                return this.$store.state.videoSessionAppointment;
            }
        },
        watch: {
            patient() {
                this.videoSession = {patient: this.patient};

                this.videoSessionPatient = this.patient;
                
                // @todo remove when "upheal" integration will be finished
                this.googleMeetPatient = this.patient;
            },
            videoSessionAppointmentData(data) {
                this.videoSession = {patient: data.patient};

                // @todo change logic when "upheal" integration will be finished
                if (data.type === 'upheal') {
                    this.videoSessionPatient = data.patient;
                    this.videoSessionAppointmentId = data.appointment_id;
                    this.updateVideoSessionDialog();
                    this.openVideoSessionDialog();
                } else {
                    this.googleMeetPatient = data.patient;
                    this.googleMeetAppointmentId = data.appointment_id;
                    this.updateGoogleMeetDialog();
                    this.openGoogleMeetDialog();
                }
            }
        },
        mounted() {
            this.checkErrorPhoneQueryParam();
        },
        methods: {
            checkErrorPhoneQueryParam() {
                const errorPhone = this.$route.query['error-phone'];
                if (!errorPhone) {
                    return;
                }

                const patientNotFoundMessage = `The patient with the given phone number ${errorPhone} was not found`
                Notification.error({
                    title: "Error",
                    message: patientNotFoundMessage,
                    type: "error",
                });
            },

            closeTelehealthChannelModal() {
              this.isShowChooseTelehealthSessionChannelDialog = false;
              // this.chooseTelehealthChannelModalData = null;
            },
            closePhoneCallModal() {
              this.chooseTelehealthChannelModalData = null;
            },
            showTelehealthChannelModal(data) {
              this.chooseTelehealthChannelModalData = data;
              this.isShowChooseTelehealthSessionChannelDialog = true;
            },
            showStartVideoSessionModal(data) {
                this.start_video_session_with_patient = data.patient || {};
                this.start_video_session_with_appointment_id = data.appointment_id || null;
                $('#start-video-session').modal('show');
            },
            setPatient(patient) {
                this.patient = patient;
                this.confirm();
            },
            setTelehealth(status) {
                this.isTelehealth = status;
                window.setTimeout(function () {
                    $('#complete-appointment').modal('show');
                }, 500);
            },
            confirm() {
                this.fetchingAppointmentData = true;
                this.$store.dispatch('getCompleteAppointmentData', {
                    appointment_id: this.patient.a_id,
                    patient_id: this.patient.id,
                    action: 'complete'
                })
                    .then((response) => {
                        if (!window.axios.isCancel(response)) {
                            this.fetchingAppointmentData = false;
                        }
                    });
            },
            openVideoSessionDialog() {
                this.isShowVideoSessionDialog = true;
            },
            closeVideoSessionDialog() {
                this.updateVideoSessionDialog();
                this.isShowVideoSessionDialog = false;
                this.chooseTelehealthChannelModalData = null;
            },
            updateVideoSessionDialog() {
                this.videoSessionDialogKey++;
            },

            // @todo remove when "upheal" integration will be finished
            openGoogleMeetDialog() {
                this.isShowGoogleMeetDialog = true;
            },
            closeGoogleMeetDialog() {
                this.updateGoogleMeetDialog();
                this.isShowGoogleMeetDialog = false;
                this.chooseTelehealthChannelModalData = null;
            },
            updateGoogleMeetDialog() {
                this.googleMeetDialogKey++;
            },
            
            openAppointmentDialog() {
                this.isUpdatedAppointments = false;
                this.isShowVideoSessionDialog = false;

                // @todo remove when "upheal" integration will be finished
                this.isShowGoogleMeetDialog = false;

                this.isShowAppointmentDialog = true;
            },
            updateAppointmentsList() {
                this.isUpdatedAppointments = true;
                this.updateAppointments = !this.updateAppointments;

                // @todo change logic when "upheal" integration will be finished
                this.openGoogleMeetDialog();
            },
            closeAppointmentDialog() {
                if(!this.isUpdatedAppointments) {
                    this.updateVideoSessionDialog();
                    
                    // @todo remove when "upheal" integration will be finished
                    this.updateGoogleMeetDialog();
                }
                this.isShowAppointmentDialog = false;
            },
            startVideoSession(url) {
                this.videoSessionUrl = url;
                this.isShowStartVideoSession = true;
            },
            closeStartVideoSessionDialog() {
                this.isShowStartVideoSession = false;
            },

            // @todo change logic when "upheal" integration will be finished
            startUphealVideoSession(url) {
                this.videoSessionUrl = url;
                this.isShowStartUphealVideoSession = true;
            },
            closeStartUphealVideoSessionDialog() {
                this.isShowStartUphealVideoSession = false;
            },

            onUpdateAppointments() {
                this.updateVideoSessionDialog();
                    
                // @todo remove when "upheal" integration will be finished
                this.updateGoogleMeetDialog();
            },
        },
    }
</script>

