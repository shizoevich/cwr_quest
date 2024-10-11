<template>
  <el-dialog
    title="Invitation to join in a Google Meet Telehealth Session"
    :visible.sync="showDialog"
    v-loading.fullscreen.lock="isLoading"
    :close-on-click-modal="false"
    class="google-meet bootstrap-modal"
  >
    <div class="google-meet-wrapper" v-if="currentTab">
      <div class="google-meet-header">
        <div
          class="google-meet-header__description"
          v-html="currentTab.description"
        ></div>
        <el-form
          :model="sendPatientForm"
          :rules="sendPatientFormRule"
          ref="sendPatientForm"
        >
          <div class="sent-form-row">
            <el-form-item
              class="form-item-virtual-data"
              :label="currentTab.send_via_email"
              prop="email"
            >
              <el-checkbox
                v-model="sendPatientForm.send_via_email"
                @change="
                  changeCheckVirtualData(
                    sendPatientForm.send_via_email,
                    'email'
                  )
                "
              />
              <el-input
                type="email"
                :disabled="!sendPatientForm.send_via_email"
                v-model="sendPatientForm.email"
                name="email"
                id="email"
                placeholder="Email"
              >
              </el-input>
            </el-form-item>
            <el-form-item
              class="form-item-virtual-data"
              :label="currentTab.send_via_secondary_email"
              prop="secondary_email"
            >
              <el-checkbox
                v-model="sendPatientForm.send_via_secondary_email"
                @change="
                  changeCheckVirtualData(
                    sendPatientForm.send_via_secondary_email,
                    'secondary_email'
                  )
                "
              />
              <el-input
                type="email"
                :disabled="!sendPatientForm.send_via_secondary_email"
                v-model="sendPatientForm.secondary_email"
                name="secondary_email"
                id="secondary_email"
                placeholder="Add Email"
              >
              </el-input>
            </el-form-item>
            <el-form-item
              class="form-item-virtual-data"
              :label="currentTab.send_via_sms"
              prop="phone"
            >
              <el-checkbox
                v-model="sendPatientForm.send_via_sms"
                @change="
                  changeCheckVirtualData(sendPatientForm.send_via_sms, 'phone')
                "
              />
              <el-input
                type="tel"
                :disabled="!sendPatientForm.send_via_sms"
                v-model="sendPatientForm.phone"
                v-mask="'(###)-###-####'"
                :masked="true"
                name="phone"
                id="phone"
                placeholder="Phone"
              >
              </el-input>
            </el-form-item>
          </div>
        </el-form>
      </div>
      <div class="google-meet-body">
        <div v-show="currentTab.slug === 'patientForm'">
          <patient-forms-collapse
            v-if="isPatientData"
            :patient="patient"
            :forModal="true"
            :documents-sent="false"
            :validate="startPatientFormsValid"
            @prepared-forms="prepareForms"
            @selected-forms="selectForms"
          />
        </div>
        <div v-show="currentTab.slug === 'appointmentList'">
          <google-meet-appointments
            :patient="patient"
            :is-start-valid="isStartAppointmentsValid"
            :is-valid-send-form="validationSendForm"
            :appointments="appointmentsData"
            :error-send-form-message="errorSendFormMessage"
            @openAppointmentDialog="openAppointmentDialog"
            @changeAppointment="changeAppointment"
            @startVideoSession="startVideoSession"
            @sendInvite="sendInvite"
          />
        </div>
        <div v-show="currentTab.slug === 'videoSessionAppointment'">
          <google-meet-video-session
            :patient="patient"
            :appointment="videoSessionAppointmentData"
          />
        </div>
      </div>
      <div class="google-meet-footer">
        <template v-if="currentTab.slug !== 'videoSessionAppointment'">
          <el-button v-if="currentTab.slug !== 'patientForm'" @click="prevTab"
            >Back</el-button
          >
          <el-tooltip
            class="item"
            effect="dark"
            :content="errorSendFormMessage"
            :disabled="validationSendForm"
            placement="top"
          >
            <div>
              <el-button
                type="primary"
                @click.prevent="nextTab"
                :disabled="!validationSendForm"
              >
                Next
              </el-button>
            </div>
          </el-tooltip>
        </template>
        <template v-else>
          <template
            v-if="
              videoSessionAppointmentData.google_meet &&
              videoSessionAppointmentData.google_meet.invitations && 
              videoSessionAppointmentData.google_meet.invitations.length
            "
          >
            <el-button
              type="primary"
              @click="
                startVideoSession(
                  videoSessionAppointmentData.google_meet.conference_uri
                )
              "
            >
              Join
            </el-button>
            <el-tooltip
              class="item"
              effect="dark"
              :content="errorSendFormMessage"
              :disabled="validationSendForm"
              placement="top"
            >
              <div>
                <el-button
                  type="primary"
                  @click="sendInvite(videoSessionAppointment)"
                  :disabled="!validationSendForm"
                >
                  Resend Invitation & Join
                </el-button>
              </div>
            </el-tooltip>
          </template>
          <template v-else>
            <el-tooltip
              class="item"
              effect="dark"
              :content="errorSendFormMessage"
              :disabled="validationSendForm"
              placement="top"
            >
              <div>
                <el-button
                  type="primary"
                  @click="sendInvite(videoSessionAppointment)"
                  :disabled="!validationSendForm"
                >
                  Send & Join
                </el-button>
              </div>
            </el-tooltip>
          </template>
        </template>
      </div>
    </div>
    
    <email-unsubscribed-dialog
        :patient-id="patient.id"
        :email="restoreEmail"
        :is-admin="!!isUserAdmin"
        :show="showEmailUnsubscribedDialog"
        :close="closeEmailUnsubscribedDialog"
        @emailRemovedFromRejectList="onEmailRemovedFromRejectList"
    />
  </el-dialog>
</template>

<script>
import GoogleMeetAppointments from "./GoogleMeetAppointments";
import GoogleMeetVideoSession from "./GoogleMeetVideoSession";
import { eventBus } from "../../app";

export default {
  name: "GoogleMeetDialog",
  data() {
    return {
      isLoading: false,
      isPatientData: false,
      selectedForms: [],
      preparedForms: [],
      allPatientForm: [],
      tabsData: [],
      appointmentsData: [],
      videoSessionAppointmentData: {},
      selectedAppointments: [],
      sendPatientForm: {
        email: "",
        secondary_email: "",
        phone: "",
        send_via_email: false,
        send_via_secondary_email: false,
        send_via_sms: false,
      },
      sendPatientFormRule: {
        email: [
          {
            required: false,
          },
        ],
        secondary_email: [
          {
            required: false,
          },
        ],
        phone: [
          {
            required: false,
          },
        ],
      },
      errorSendFormMessage: "Enter Email and / or Phone",
      isStartAppointmentsValid: false,
      startPatientFormsValid: false,
      showEmailUnsubscribedDialog: false,
      restoreEmail: null
    };
  },
  props: {
    patient: {
      type: Object,
      default() {
        return {};
      },
    },
    provider: {
      type: Object,
      default() {
        return {};
      },
    },
    isShowDialog: {
      type: Boolean,
      default: false,
    },
    updateAppointments: {
      type: Boolean,
      default: false,
    },
    videoSessionAppointmentId: {
      type: Number || String || null,
      default: null,
    },
  },
  components: {
    GoogleMeetVideoSession,
    GoogleMeetAppointments,
  },
  watch: {
    patient() {
      this.updateDialogData();
    },
    updateAppointments() {
      this.updateAppointmentList();
    },
    currentTab(tab) {
      if (Boolean(tab)) {
        this.isLoading = false;
      }
    },
    isLoading(value) {
      if (!value) {
        this.isLoading = !Boolean(this.currentTab);
      }
    },
    showEmailUnsubscribedDialog(value) {
      if (value) {
        return;
      }

      $('.v-modal').remove();
    }
  },
  computed: {
    isUserAdmin() {
        return this.$store.state.isUserAdmin;
    },
    showDialog: {
      get() {
        return this.isShowDialog;
      },
      set(value) {
        if (!value) {
          this.$emit("closeDialog");
        }
      },
    },
    currentTab() {
      return this.tabsData.find((item) => item.active);
    },
    fullPatientName() {
      return this.patient.first_name + " " + this.patient.last_name;
    },
    providerEmail() {
      return this.provider ? this.provider.email : "";
    },
    validationSendForm() {
      if (
        this.sendPatientForm.send_via_email &&
        !Boolean(this.sendPatientForm.email)
      ) {
        return false;
      }
      if (
        this.sendPatientForm.send_via_secondary_email &&
        !Boolean(this.sendPatientForm.secondary_email)
      ) {
        return false;
      }
      if (
        this.sendPatientForm.send_via_sms &&
        !Boolean(this.sendPatientForm.phone)
      ) {
        return false;
      }
      return (
        (this.sendPatientForm.send_via_email &&
          Boolean(this.sendPatientForm.email)) && (this.sendPatientForm.send_via_secondary_email &&
          Boolean(this.sendPatientForm.secondary_email)) ||
        (this.sendPatientForm.send_via_email &&
          Boolean(this.sendPatientForm.email))||
        (this.sendPatientForm.send_via_secondary_email &&
          Boolean(this.sendPatientForm.secondary_email))||
        (this.sendPatientForm.send_via_sms &&
          Boolean(this.sendPatientForm.phone)) ||
        (!this.selectedForms.length &&
          this.currentTab &&
          this.currentTab.slug === "patientForm")
      );
    },
    isVideoSessionAppointment() {
      return Boolean(this.videoSessionAppointmentId);
    },
    videoSessionAppointment() {
      return {
        id: this.videoSessionAppointmentId,
        date: null,
        isToday: true,
        allow_to_join_by_phone:
          this.videoSessionAppointmentData.allow_to_join_by_phone,
      };
    },
  },
  methods: {
    initTabsData() {
      this.tabsData = [
        {
          id: 0,
          slug: "patientForm",
          send_via_email: "Send Forms via Email",
          send_via_secondary_email: "Send Forms via Add Email",
          send_via_sms: "Send Forms via SMS",
          description:
            "<p>Before sending the invitation to the Telehealth session, please send all required documents to the patient</p>",
          active: false,
        },
        {
          id: 1,
          slug: "appointmentList",
          send_via_email: "Send Invitation via Email",
          send_via_secondary_email: "Send Invitation via Add Email",
          send_via_sms: "Send Invitation via SMS",
          description: `<p>Please provide an email address or phone number to invite <b>${this.fullPatientName}</b> to join you in a Telehealth session.</p><p><b>IMPORTANT:</b> Before you send this invitation, please make sure that <b>in this browser session</b> you are logged in to the Gmail account provided to you by Change Within Reach.Your company email is: <b>${this.providerEmail}</b>. If you are not sure how to do this, please go to <a href="http://help.cwr.care" target="_blank">help.cwr.care</a> to learn more. </p>`,
          active: false,
        },
        {
          id: 2,
          slug: "videoSessionAppointment",
          send_via_email: "Send Invitation via Email",
          send_via_secondary_email: "Send Invitation via Add Email",
          send_via_sms: "Send Invitation via SMS",
          description: `<p>Please provide an email address or phone number to invite <b>${this.fullPatientName}</b> to join you in a Telehealth session.</p><p><b>IMPORTANT:</b> Before you send this invitation, please make sure that <b>in this browser session</b> you are logged in to the Gmail account provided to you by Change Within Reach.Your company email is: <b>${this.providerEmail}</b>. If you are not sure how to do this, please go to <a href="http://help.cwr.care" target="_blank">help.cwr.care</a> to learn more. </p>`,
          active: false,
        },
      ];
    },
    initPatientFormData() {
      this.isLoading = true;
      this.$store
        .dispatch("getPatientFormsForModal", this.patient.id)
        .then(({ data }) => {
          this.allPatientForm = data.filter((item) => item.visible_in_modal);
          let allSelectedPatientForms = this.allPatientForm.filter(
            (item) => item.requests.length
          );
          if (
            allSelectedPatientForms &&
            allSelectedPatientForms.length === this.allPatientForm.length
          ) {
            this.isVideoSessionAppointment
              ? this.changeTab("videoSessionAppointment")
              : this.changeTab("appointmentList");
          } else {
            this.changeTab("patientForm");
          }
          this.isPatientData = true;
          this.isLoading = false;
        })
        .catch(() => (this.isLoading = false));
    },
    initSentPatientFormData() {
      this.sendPatientForm.email = this.patient.email;
      this.sendPatientForm.secondary_email = this.patient.secondary_email;
      this.sendPatientForm.phone = this.patient.cell_phone;
      this.sendPatientForm.send_via_email = false;
      this.sendPatientForm.send_via_secondary_email = false;
      this.sendPatientForm.send_via_sms = false;
    },
    updateAppointmentList() {
      this.isLoading = true;
      this.$store
        .dispatch("getTelehealthAppointments", this.patient.id)
        .then(({ data }) => {
          this.appointmentsData = [
            {
              id: 0,
              title: "Today",
              appointments: data.today,
              isToday: true,
            },
            {
              id: 1,
              title: "Upcoming",
              appointments: data.upcoming,
              isToday: false,
            },
          ];
        })
        .finally(() => (this.isLoading = false));
    },
    updateVideoSessionAppointmentData() {
      this.isLoading = true;
      this.$store
        .dispatch("getAppointmentDialogData", this.videoSessionAppointmentId)
        .then(({ data }) => {
          this.videoSessionAppointmentData = data.appointment;
        })
        .finally(() => (this.isLoading = false));
    },
    updateDialogData() {
      this.initTabsData();
      this.initSentPatientFormData();
      this.videoSessionAppointmentId
        ? this.updateVideoSessionAppointmentData()
        : this.updateAppointmentList();
      this.initPatientFormData();
    },
    nextTab() {
      switch (this.currentTab.slug) {
        case "patientForm":
          this.sendingPatientForm();
          break;
        case "appointmentList":
          this.sendingAppointment();
          break;
        default:
          this.skipTab();
          break;
      }
    },
    skipTab() {
      for (let index = 0; index < this.tabsData.length; index++) {
        let item = this.tabsData[index];
        if (item.active && index !== this.tabsData.length - 1) {
          item.active = false;
          this.tabsData[index + 1].active = true;
          break;
        }
      }
    },
    prevTab() {
      this.tabsData.forEach((item, index) => {
        if (item.active && index !== 0) {
          item.active = false;
          this.tabsData[index - 1].active = true;
        }
      });
    },
    changeTab(slug) {
      this.isLoading = true;
      this.tabsData.forEach((item) => {
        item.active = item.slug === slug;
      });
      this.isLoading = false;
    },
    changeCheckVirtualData(check, field) {
      if (check) {
        this.sendPatientFormRule[field] = [
          {
            required: true,
            message: `The ${field} field is required`,
            trigger: "change",
          },
        ];
        if (field === "phone") {
          this.sendPatientFormRule[field].push({
            len: 14,
            message: "The phone field must be at least 14 characters",
            trigger: "change",
          });
        }
      } else {
        this.sendPatientFormRule[field] = [
          {
            required: false,
          },
        ];
      }
    },
    selectForms(value) {
      this.selectedForms = value;
    },
    prepareForms(value) {
      this.preparedForms = value;
    },
    handleErrorMessage(errors) {
      for (const errorsName in errors) {
        if (errors.hasOwnProperty(errorsName)) {
          errors[errorsName].forEach((error) => {
            setTimeout(() => {
              this.$message({
                type: "error",
                message: error,
                duration: 10000,
              });
            }, 300);
          });
        }
      }
    },
    handleSendPatientForm() {
      let startHandle = true;
      this.isLoading = true;
      this.startPatientFormsValid = !this.startPatientFormsValid;
      let payload = {
        patient_id: this.patient.id,
      };
      payload.data = _.assign(this.sendPatientForm, {
        forms: this.preparedForms,
      });
      eventBus.$on("patientFormsValid", (isPatientFormsValid) => {
        if (startHandle && isPatientFormsValid) {
          this.$refs.sendPatientForm.validate((valid) => {
            if (valid) {
              this.$store
                .dispatch("sendPatientForms", payload)
                .then(() => {
                  this.isVideoSessionAppointment
                    ? this.changeTab("videoSessionAppointment")
                    : this.changeTab("appointmentList");
                  this.isLoading = false;
                })
                .catch((error) => {
                  this.isLoading = false;
                  if (
                    error.response &&
                    error.response.data &&
                    error.response.status === 422
                  ) {
                    this.handleErrorMessage(error.response.data.errors);
                  } else {
                    this.$message({
                      type: "error",
                      message: "Oops, something went wrong!",
                      duration: 10000,
                    });
                  }
                });
            } else {
              this.isLoading = false;
              if (this.isPatienFormsValid) {
                this.$message({
                  type: "error",
                  message: "Please fill in the required field",
                  duration: 10000,
                });
              }
            }
          });
        } else if (!isPatientFormsValid) {
          this.isLoading = false;
        }
        startHandle = false;
      });
    },
    sendingPatientForm() {
      if (this.selectedForms.length === 0) {
        this.isVideoSessionAppointment
          ? this.changeTab("videoSessionAppointment")
          : this.changeTab("appointmentList");
        return;
      }
      this.handleSendPatientForm();
    },
    sendingAppointment() {
      this.isLoading = true;
      this.isStartAppointmentsValid = true;
      this.isAppointmentsValid()
        .then(() => {
          this.$refs.sendPatientForm.validate((valid) => {
            if (valid) {
              let payload = {
                patient_id: this.patient.id,
              };
              payload.data = _.assign(this.sendPatientForm, {
                appointments: this.selectedAppointments,
              });
              this.$store
                .dispatch("storeVideoSession", payload)
                .then(() => {
                  this.showDialog = false;
                  this.updateDialogData();
                })
                .catch((error) => {
                  this.isLoading = false;
                  if (
                    error.response &&
                    error.response.data &&
                    error.response.status === 422
                  ) {
                    this.handleErrorMessage(error.response.data);
                  } else {
                    this.$message({
                      type: "error",
                      message: "Oops, something went wrong!",
                      duration: 10000,
                    });
                  }
                })
                .finally(() => (this.isLoading = false));
            } else {
              this.isLoading = false;
              this.$message({
                type: "error",
                message: "Please fill in the required field",
                duration: 10000,
              });
            }
          });
        })
        .catch((error) => {
          this.isLoading = false;
          this.$message({
            type: "error",
            message: error.message,
            duration: 10000,
          });
        });
    },
    sendInvite(appointment) {
      this.isLoading = true;
      let payload = {
        patient_id: this.patient.id,
      };
      payload.data = _.assign(this.sendPatientForm, {
        appointments: [appointment],
      });
      this.$store
        .dispatch("storeVideoSession", payload)
        .then(({ data }) => {
          let googleMeeting = data.google_meetings.find(
            (item) => Number(item.appointment_id) === Number(appointment.id)
          );
          this.startVideoSession(googleMeeting.conference_uri);
          this.updateDialogData();
        })
        .catch((error) => {
          this.isLoading = false;
          if (
            error.response &&
            error.response.data &&
            (error.response.status === 422)
          ) {
            this.handleErrorMessage(error.response.data);
          } else if (error.response.status === 409) {
            if (error.response.data.error.exception_type === 'EmailInRejectListException') {
              this.restoreEmail = error.response.data.error.email;
              this.showEmailUnsubscribedDialog = true;
            } else {
              this.$message({
                type: "error",
                message: error.response.data.error.message,
                duration: 10000,
              });
            }
          }
          else {
            this.$message({
              type: "error",
              message: "Oops, something went wrong!",
              duration: 10000,
            });
          }
        })
        .finally(() => (this.isLoading = false));
    },
    startVideoSession(conference_uri) {
      this.showDialog = false;
      this.$emit("startVideoSession", conference_uri);
    },
    isAppointmentsValid() {
      return new Promise((resolve, reject) => {
        let isAllWithoutCheck = this.selectedAppointments.length === 0,
          withoutSchedule = [];
        this.selectedAppointments.forEach((item) => {
          if (item.date === null && !item.isToday) {
            withoutSchedule.push(item);
          }
        });
        if (isAllWithoutCheck)
          reject({
            message: "Please select at least one appointment",
          });
        withoutSchedule.length
          ? reject({
              message: "Please fill out all required fields",
            })
          : resolve();
      });
    },
    changeAppointment({ appointments, selectedAppointment }) {
      this.appointmentsData = appointments;
      this.selectedAppointments = selectedAppointment;
    },
    openAppointmentDialog() {
      this.$emit("openAppointmentDialog");
    },
    closeEmailUnsubscribedDialog() {
      this.showEmailUnsubscribedDialog = false;
    },
    onEmailRemovedFromRejectList() {
        if (!this.$route.name === 'patient-chart' || !this.$route.params.id) {
            return;
        }

        this.$store.dispatch('getPatient', {patientId: this.$route.params.id})
            .catch(() => {
                //
            });
        this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {id: this.$route.params.id})
            .catch(() => {
                //
            });
    },
  },
  mounted() {
    this.updateDialogData();
  },
};
</script>

<style lang="scss">
.google-meet {
  .el-dialog {
    width: 95%;
    max-width: 900px;
  }

  .sent-form-row {
    display: flex;
    justify-content: space-between;

    .el-form-item {
      width: 50%;
      padding-right: 15px;
      padding-left: 15px;
    }
  }

  &-footer {
    padding-top: 20px;
    display: flex;
    justify-content: flex-end;
    align-items: center;

    .el-button {
      margin-left: 15px;
    }
  }
}
</style>
