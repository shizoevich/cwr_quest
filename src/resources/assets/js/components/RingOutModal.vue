<template>
  <el-dialog
    title="Phone Call"
    :visible.sync="showModal"
    :close-on-click-modal="false"
    class="patient-dialog bootstrap-modal">
    <div v-loading="is_loading">
      <el-form :rules="form_rules" ref="ringOutForm" :model="form_data" :disabled="call_in_progress">
        <div class="form-group">
          <div class="form-group__title">Appointment Information</div>
          <div class="row" v-if="appointment">
            <div class="col-sm-6">
              <div class="appointment-info">
                <p class="appointment-info__label">Patient</p>
                <p class="appointment-info__content">
                  <a :href="`/chart/${appointment.patient.id}`" target="_blank">
                    {{ appointment.patient.first_name }} {{ appointment.patient.last_name }}
                  </a>
                </p>
              </div>
              <div class="appointment-info">
                <p class="appointment-info__label">Date of Birth</p>
                <p class="appointment-info__content">{{ getFormattedDateSimple(appointment.patient.date_of_birth) }}</p>
              </div>
              <div class="appointment-info" v-if="appointment.patient.preferred_language">
                <p class="appointment-info__label">Preferred Language</p>
                <p class="appointment-info__content">{{ appointment.patient.preferred_language.title }}</p>
              </div>
              <div class="appointment-info">
                <p class="appointment-info__label">Sex</p>
                <p class="appointment-info__content">{{ getPatientSex(appointment.patient.sex) }}</p>
              </div>
              <div class="appointment-info">
                <p class="appointment-info__label">MRN</p>
                <p class="appointment-info__content">{{ appointment.patient.subscriber_id }}</p>
              </div>
              <div class="appointment-info" v-if="appointment.patient.cell_phone">
                <p class="appointment-info__label">Cell Phone</p>
                <p class="appointment-info__content">
                  <el-tooltip v-if="formatPhone(appointment.patient.cell_phone) !== form_data.phone_to" class="item" effect="dark" content="Click to paste this phone to 'To' field.">
                    <a href="#" @click.prevent="setPhoneTo(appointment.patient.cell_phone)">{{ formatPhone(appointment.patient.cell_phone) }}</a>
                  </el-tooltip>

                  <template v-else>
                    {{ formatPhone(appointment.patient.cell_phone) }}
                  </template>
                </p>
              </div>
              <div class="appointment-info" v-if="appointment.patient.home_phone">
                <p class="appointment-info__label">Home Phone</p>
                <p class="appointment-info__content">
                  <el-tooltip v-if="formatPhone(appointment.patient.home_phone) !== form_data.phone_to" class="item" effect="dark" content="Click to paste this phone to 'To' field.">
                    <a href="#" @click.prevent="setPhoneTo(appointment.patient.home_phone)">{{ formatPhone(appointment.patient.home_phone) }}</a>
                  </el-tooltip>
                  <template v-else>
                    {{ formatPhone(appointment.patient.home_phone) }}
                  </template>
                </p>
              </div>
              <div class="appointment-info" v-if="appointment.patient.work_phone">
                <p class="appointment-info__label">Work Phone</p>
                <p class="appointment-info__content">
                  <el-tooltip v-if="formatPhone(appointment.patient.work_phone) !== form_data.phone_to" class="item" effect="dark" content="Click to paste this phone to 'To' field.">
                    <a href="#" @click.prevent="setPhoneTo(appointment.patient.work_phone)">{{ formatPhone(appointment.patient.work_phone) }}</a>
                  </el-tooltip>
                  <template v-else>
                    {{ formatPhone(appointment.patient.work_phone) }}
                  </template>
                </p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="appointment-info">
                <p class="appointment-info__label">Date</p>
                <p class="appointment-info__content">{{ getFormattedDateTime(appointment.time * 1000) }}</p>
              </div>
              <div class="appointment-info">
                <p class="appointment-info__label">Duration</p>
                <p class="appointment-info__content">{{ appointment.visit_length }} min.</p>
              </div>
              <div class="appointment-info">
                <p class="appointment-info__label">Visit Reason</p>
                <p class="appointment-info__content">{{ appointment.reason_for_visit }}</p>
              </div>
              <div class="appointment-info">
                <p class="appointment-info__label">Co Pay</p>
                <p class="appointment-info__content">{{ getFormattedCoPay(appointment.visit_copay) }}</p>
              </div>
            </div>
          </div>
        </div>


        <div class="form-group">
          <div class="form-group__title">Call Params</div>
          <div class="form-row">
            <div class="form-col form-col-6">
              <el-form-item label="From" prop="phone_from">
                <input v-model="form_data.phone_from" v-mask="'(###)-###-####'" :masked="true" hidden>
                <el-input
                  type="tel"
                  v-model="form_data.phone_from"
                  v-mask="'(###)-###-####'"
                  :masked="true"></el-input>
              </el-form-item>
            </div>
            <div class="form-col form-col-6">
              <el-form-item label="To" prop="phone_to">
                <input v-model="form_data.phone_to" v-mask="'(###)-###-####'" :masked="true" hidden>
                <el-input
                  type="tel"
                  v-model="form_data.phone_to"
                  v-mask="'(###)-###-####'"
                  :masked="true"/>
              </el-form-item>
            </div>
          </div>
          <div class="form-row">
            <div class="form-col form-col-12">
              <el-checkbox v-model="form_data.play_prompt">Prompt me to press 1 before connecting the call</el-checkbox>
            </div>
          </div>
        </div>
      </el-form>

      <div class="form-group" v-if="call_log">
        <div class="form-group__title">Call Status</div>
        <div class="call-status-container">
          <template v-if="call_log.telephony_status_name === 'Ringing'">
            <h4>Calling your current location...</h4>
            <h5>Please answer your phone when it rings.</h5>
          </template>
          <template v-else-if="call_log.telephony_status_name === 'CallConnected'">
            <h4>First party connected.</h4>
            <h5>Calling destination number...</h5>
          </template>
          <template v-else-if="call_log.telephony_status_name === 'NoCall'">
            <h4>
              Call has been completed
              <template v-if="call_log.call_ends_at">
                at {{ getFormattedDateTime(call_log.call_ends_at) }}
              </template>
              ({{ call_log.call_status_title }})
            </h4>
            <h5 v-if="call_log.duration">Duration: {{ getDurationFormat(call_log.duration * 1000) }}</h5>
            <div>
              <el-dropdown @command="handleAction">
                <el-button type="primary">
                  Action<i class="el-icon-arrow-down el-icon--right"></i>
                </el-button>
                <el-dropdown-menu slot="dropdown">
                  <el-dropdown-item command="openCompleteAppointmentModal">Complete Appointment</el-dropdown-item>
                  <el-dropdown-item command="openCancelAppointmentModal">Cancel Appointment</el-dropdown-item>
                  <a :href="`/chart/${appointment.patient.id}?action=add_progress_note&appointment_id=${appointment.id}`" class="send-forms-link" style="text-decoration:none!important;">
                    <el-dropdown-item>
                      Add Progress Note
                    </el-dropdown-item>
                  </a>
                  <a :href="`/chart/${appointment.patient.id}?tab=patientForms`" class="send-forms-link" style="text-decoration:none!important;">
                    <el-dropdown-item>
                      Send Patient Forms
                    </el-dropdown-item>
                  </a>
                </el-dropdown-menu>
              </el-dropdown>
            </div>


            <div>
<!--              <el-cascader :options="actions" clearable></el-cascader>-->
            </div>
          </template>
          <el-button type="danger" icon="el-icon-close" v-if="call_log.telephony_status_name !== 'NoCall'" @click="cancelCall" circle></el-button>
        </div>
      </div>
      <div class="form-footer">
        <div class="form-footer-control">
          <el-button type="primary" @click="call" v-if="!call_in_progress">Call</el-button>
          <el-button @click="closeModal" :disabled="call_in_progress">Close</el-button>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script>
import DatetimeFormated from '../mixins/datetime-formated';

export default {
  mixins: [DatetimeFormated],
  props : {
    isShowModal  : {
      required: true,
      type    : Boolean,
    },
    phoneFrom    : {
      required: false
    },
    phoneTo      : {
      required: false
    },
    appointmentId: {
      required: true,
      type    : Number
    }
  },
  data() {
    return {
      form_rules            : {
        phone_from: [{
          len    : 14,
          message: 'The from field must be at least 14 characters',
          trigger: 'blur'
        }],
        phone_to  : [{
          len    : 14,
          message: 'The to field must be at least 14 characters',
          trigger: 'blur'
        }],
      },
      form_data             : {
        phone_from      : null,
        phone_to        : null,
        play_prompt     : true,
        appointment_type: '',
        appointment_id  : null
      },
      appointment           : null,
      call_log              : null,
      call_in_progress      : false,
      is_loading            : false,
      websocket_channel_name: null,
    };
  },

  methods: {
    handleAction(action) {
      if(!this[action]) {
        return;
      }
      this[action]();
    },
    openCompleteAppointmentModal() {
      this.is_loading = true;
      this.$store.dispatch('getCompleteAppointmentData', {
        appointment_id: this.appointment.id,
        patient_id: this.appointment.patient.id,
        action: 'complete'
      }).then(response => {
        if (response.status === 200) {
          this.closeModal();
          this.$emit('telehealth-confirmed', true);
        }
      }).finally(() => {
        this.is_loading = false;
      });
    },
    openCancelAppointmentModal() {
      this.is_loading = true;
      this.$store.dispatch('getCompleteAppointmentData', {
        appointment_id: this.appointment.id,
        patient_id: this.appointment.patient.id,
        action: 'cancel'
      }).then(response => {
        if (response.status === 200) {
          this.closeModal();
          $('#cancel-appointment').modal('show');
        }
      }).finally(() => {
        this.is_loading = false;
      });
    },
    init() {
      this.is_loading = true;
      this.$store.dispatch('getRingOutCallByAppointment', this.appointmentId).then(response => {
        this.appointment = response.data.appointment;
        this.call_log = response.data.call_log;
        if (!this.form_data.phone_from) {
          this.form_data.phone_from = response.data.appointment.provider.phone;
        }
        if (!this.form_data.phone_to) {
          this.form_data.phone_to = response.data.appointment.patient.cell_phone || response.data.appointment.patient.home_phone || response.data.appointment.patient.work_phone;
        }
      }).finally(() => {
        this.is_loading = false;
      });
    },

    setPhoneTo(phone) {
      this.form_data.phone_to = phone;
    },

    getFormattedCoPay(copay) {
      if (copay) {
        copay = parseFloat(copay);
        if (copay > 0) {
          return '$' + copay;
        }
      }

      return '-';
    },

    formatPhone(value) {
      if (value) {
        return value.replace(/(\d{3})(\d{3})(\d{4})/, '($1)-$2-$3');
      }

      return '';
    },

    getPatientSex(sex) {
      switch (sex) {
        case 'M':
          return 'Male';
        case 'F':
          return 'Female';
        default:
          return 'Unknown';
      }
    },
    closeModal() {
      this.showModal = false;
    },
    clearData() {
      if(this.websocket_channel_name) {
        window.Echo.leave(this.websocket_channel_name);
      }
      this.form_data = {
        phone_from      : '',
        phone_to        : '',
        play_prompt     : true,
        appointment_type: '',
        appointment_id  : null
      };
      this.call_log = null;
      this.call_in_progress = false;
      this.is_loading = false;
      this.websocket_channel_name = null;
    },
    cancelCall() {
      if (!this.call_log) {
        return false;
      }
      this.is_loading = true;
      this.$store.dispatch('cancelRingOutCall', this.call_log.id)
        .then(response => {
          this.call_log = response.data.call_log;
        })
        .finally(() => {
          this.is_loading = false;
        });
    },
    unsubscribeWebsocket() {
      if (this.websocket_channel_name) {
        window.Echo.leave(this.websocket_channel_name);
        this.websocket_channel_name = null;
      }
    },
    subscribeWebsocket() {
      this.websocket_channel_name = `users.${this.call_log.user_id}.ring-out.${this.call_log.id}`;
      window.Echo.private(this.websocket_channel_name)
        .listen('.ring-out.call.updated', (data) => {
          this.call_log = data.call_log;
        });
    },
    call() {
      this.$refs.ringOutForm.validate((valid) => {
        if (!valid) {
          return false;
        }
        this.unsubscribeWebsocket();
        this.call_in_progress = true;
        this.is_loading = true;
        this.call_log = null;
        this.$store.dispatch('appointmentRingOutCall', {
          appointment_type: 'appointment',
          appointment_id  : this.appointment.id,
          phone_from      : this.form_data.phone_from,
          phone_to        : this.form_data.phone_to,
          play_prompt     : this.form_data.play_prompt,
        }).then(response => {
          this.call_log = response.data.call_log;
        }).catch((e) => {
          this.call_in_progress = false;
          console.error(e);
        }).finally(() => {
          this.is_loading = false;
        });
      });
    },
  },

  computed: {
    showModal: {
      get() {
        return this.isShowModal;
      },
      set(value) {
        if (!value) {
          this.clearData();
          this.$emit('closeModal');
        }
      }
    },
  },

  mounted() {
    // this.init();
  },

  watch: {
    appointmentId: {
      immediate: true,
      handler(value) {
        if (value) {
          this.init();
        }
      }
    },
    phoneFrom    : {
      immediate: true,
      handler(value) {
        this.form_data.phone_from = value;
      }
    },
    phoneTo      : {
      immediate: true,
      handler(value) {
        this.form_data.phone_to = value;
      }
    },
    call_log(value) {
      if(value) {
        this.subscribeWebsocket();
        if (value.telephony_status_name === 'NoCall') {
          this.call_in_progress = false;
        } else {
          this.call_in_progress = true;
        }
      } else {
        this.unsubscribeWebsocket();
      }
      // if (!value) {
      //   return;
      // }
      // if (value.telephony_status_name === 'NoCall') {
      //   this.call_in_progress = false;
      //   if (this.websocket_channel_name) {
      //     window.Echo.leave(this.websocket_channel_name);
      //     this.websocket_channel_name = null;
      //   }
      // }
    },
  }
}
</script>

<style lang="scss" scoped>
.call-status-container {
  height: 125px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding-bottom: 15px;
}

.appointment-info {
  display: flex;
  align-items: center;

  &__label {
    text-align: right;
    min-width: 150px;
    margin-bottom: 5px;

    &:after {
      content: ':';
    }
  }

  &__content {
    font-weight: bold;
    padding-left: 10px;
    margin-bottom: 5px;
  }
}
</style>