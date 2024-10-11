<template>
  <div
    class="modal appt-modal-vertical-center fade appt-modal appt-modal-danger"
    id="cancel-appointment"
    data-backdrop="static"
    data-keyboard="false"
    v-if="patient"
  >

    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button
            type="button"
            class="close"
            @click.prevent="closeApptModal()"
            :disabled="is_saving"
          >
            &times;
          </button>
          <h4 class="modal-title">Appointment Status Change</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div
              class="col-xs-6"
              style="padding-right: 10%; margin-bottom: 40px"
            >
              <h4 class="title">Appointment Info</h4>
              <div class="row">
                <div class="col-xs-12">
                  {{ getFullPatientName() }},
                  <small class="years-old" v-if="patient.years_old > 0"
                    >{{ patient.years_old }} years old</small
                  >
                </div>
                <div class="col-xs-6">
                  {{ patient.appointment_date }}
                </div>
                <div class="col-xs-6 text-right">
                  {{ patient.appointment_time }}
                </div>
              </div>
            </div>

            <div class="col-xs-6">
              <h4 class="title">Insurance</h4>
              <div class="row">
                <div class="col-xs-12" v-if="patient.primary_insurance">
                  {{ patient.primary_insurance }}
                </div>
                <div class="col-xs-12" v-if="patient.insurance_plan && patient.insurance_plan.name">
                  {{ patient.insurance_plan.name }}
                </div>
                <template v-if="patient.insurance_plan && patient.insurance_plan.is_verification_required">
                  <div class="col-xs-12">
                    <span
                      :class="{'almost-overdue-eff-stop-date': patient.visits_auth_left > 0 && patient.visits_auth_left <= upcomingReauthorizationRequestsMinVisitsCount, 'overdue-eff-stop-date': patient.visits_auth_left <= 0}"
                    >
                      {{ getVisitsVal(patient.visits_auth_left) }}
                    </span>
                    out of {{ getVisitsVal(patient.visits_auth) }} visits left
                  </div>
                  <div
                    class="col-xs-12"
                    v-if="patient.eff_start_date && patient.eff_stop_date"
                  >
                    {{ getFormattedDate(patient.eff_start_date) }} -
                    <span :class="getEffClass(patient)">
                      {{ getFormattedDate(patient.eff_stop_date) }}
                    </span>
                  </div>
                </template>
              </div>
            </div>

            <div class="col-xs-12">
              <h4 class="title" style="margin-bottom: 5px">Cancellation Reason</h4>

              <CancellationFeeRestrictions v-if="is_status_for_pay_selected && patientLateCancellationFeeInfo" :patientLateCancellationFeeInfo="patientLateCancellationFeeInfo" />

              <div class="reason-block text-center">
                <label for="cancellation-reason">Select Reason</label>
                <el-select
                  id="cancellation-reason"
                  class="custom-input-width"
                  :disabled="is_saving"
                  v-model="reason"
                  placeholder=""
                >
                  <el-option v-for="status in cancel_statuses" :key="status.id" :value="status.id" :label="status.status">
                  </el-option>
                </el-select>

                <template v-if="is_status_for_pay_selected && canChargeLateCancellationFee">
                    <div>
                      <el-radio-group v-model="collectLateCancellationFee" id="cancellation-fee">
                        <div class="d-flex flex-column">
                          <el-radio :label="true" :checked="collectLateCancellationFee === true"  style="color: inherit">
                            <span class="cancellation-fee-radio">
                                I recommend to collect ${{ patientLateCancellationFeeInfo.charge_for_cancellation }} cancellation fee
                            </span>
                          </el-radio>
                          <el-radio :label="false" :checked="collectLateCancellationFee === false" style="color: inherit">
                            <span class="cancellation-fee-radio">
                                I don't recommend to collect cancellation fee
                            </span>
                          </el-radio>
                        </div>
                      </el-radio-group>
                    </div>
                </template>
              </div>
              
              <div v-if="reasonIsCancelledByPatient" class="reason-block text-center" style="margin: 25px 0 30px -150px">
                <label for="patient-requested-cancellation-at">Patient Requested Cancellation At</label>
                <el-date-picker 
                  id="patient-requested-cancellation-at"
                  class="custom-input-width"
                  popper-class="appointment-date-picker"
                  v-model="patientRequestedCancellationAt" 
                  format="MM/dd/yyyy hh:mm A"
                  value-format="MM/dd/yyyy hh:mm A" 
                  type="datetime" 
                  placeholder="Select date and time"
                  :picker-options="pickerOptions"
                  :editable="false"
                  @input="handlePatientRequestedCancellationAtChange"
                />
              </div>
            </div>

            <!--<div class="col-xs-12 text-center" style="margin-top:37px;">-->
            <!--<p class="temp-text">тут можно что то написать  тут можно что то написать тут можно что то написать тут можно что то написать тут можно что то написатьтут можно что то написать тут можно что то написать</p>-->
            <!--</div>-->

            <div class="col-xs-12" style="margin-top: 20px">
              <h4 class="title">Comment</h4>
              <div class="comment-block">
                <div class="form-group" style="margin-bottom: 5px">
                  <textarea
                    id="cancel-comment"
                    placeholder="Comment..."
                    class="form-control no-resize"
                    v-model="comment"
                    rows="4"
                    :disabled="is_saving"
                    maxlength="255"
                  ></textarea>
                </div>
                <p v-if="errorMessage" class="text-red validation-error-msg error-message">
                  {{ errorMessage }}
                </p>
                <el-button
                  @click.prevent="showConfirmationDialog"
                  :loading="is_saving"
                  type="danger"
                  >Change Status</el-button
                >
                <!--                                <button role="button" class="btn appt-modal-btn" @click.prevent="cancelAppointment()" :disabled="is_saving">Change Status</button>-->
              </div>
            </div>
            <div class="col-xs-12 text-center">
              <div class="warning-block">
                <div class="text-center" style="margin-botton: 10px">
                  <b>ATTENTION!!!</b>
                  THIS ACTION СANNOT BE UNDONE THROUGH THIS SYSTEM.
                </div>

                <br />
                Upon completion of this action, appointment status will be
                changed and the comment will be added to this appointment record
                in OfficeAlly. In order to make changes to this appointment
                record after this action has been completed, please contact your
                system administrator.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <el-dialog
    title="Are you sure you want to cancel this appointment?"
    :visible.sync="showConfirmation"
    width="30%"
    class="confirmation-window"
    :modal-append-to-body="false"
  >
    <div class="d-flex flex-column text-left" style="gap: 10px;word-break: break-word;">
      <div class="d-flex" style="gap: 5px">
        <div style="width: 135px;flex-shrink: 0;">Patient name:</div>
          <div>{{ getFullPatientName() }}</div>
        </div>
      <div class="d-flex" style="gap: 5px">
        <div style="width: 135px;flex-shrink: 0;">Appointment date:</div>
          <div>{{ patient.appointment_date }} {{ patient.appointment_time }}</div>
        </div>
      <div class="d-flex" style="gap: 5px">
        <div style="width: 135px;flex-shrink: 0;">Reason:</div>
        <div>{{ reason && cancel_statuses.find(el => el.id === reason).status }}</div>
      </div>
      <div v-if="patientRequestedCancellationAt" class="d-flex" style="gap: 5px">
        <div style="width: 135px;flex-shrink: 0;">Patient Requested Cancellation At:</div>
        <div>{{ patientRequestedCancellationAt }}</div>
      </div>
      <div class="d-flex" style="gap: 5px" v-if="showLateCancellationFeeInConfirmation">
        <div style="width: 135px;flex-shrink: 0;">Late cancellation fee:</div>
        <div>{{ cancellationFeeToCharge }}$</div>
      </div>
      <div class="d-flex" style="gap: 5px">
        <div style="width: 135px;flex-shrink: 0;">Comment:</div>
        <div>{{ comment }}</div>
      </div>
    </div>
    <span slot="footer" class="dialog-footer">
      <el-button @click="showConfirmation = false">Cancel</el-button>
      <el-button type="danger" @click="cancelAppointment">Confirm</el-button>
    </span>
  </el-dialog>

  </div>
</template>

<script>
import CancellationFeeRestrictions from './CancellationFeeRestrictions';
import { CANCELLATION_COMMENT_TYPE, CANCELLED_BY_PATIENT_APPOINTMENT_STATUS_ID } from '../../settings';

export default {
  components: {
    CancellationFeeRestrictions
  },

  data() {
    return {
      reason: null,
      patientRequestedCancellationAt: null,
      comment: "",
      is_saving: false,
      reasons_for_pay: [
        "Last Minute Cancel by Patient",
        "Last Minute Reschedule",
        "Patient Did Not Come",
      ],
      errorMessage: null,
      patientLateCancellationFeeInfo: null,
      collectLateCancellationFee: null,
      showConfirmation: false,
      initSelectableRange: "00:00:00 - 23:59:00",
      selectableRange: "00:00:00 - 23:59:00",
    };
  },

  computed: {
    patient() {
      return this.$store.state.complete_appointment_data;
    },
    is_status_for_pay_selected() {
        if (!this.reason) {
            return false;
        }

        const cancelStatus = this.cancel_statuses.find((reason) => reason.id === this.reason);
        if (!cancelStatus) {
            return false;
        }

        return this.reasons_for_pay.indexOf(cancelStatus.status) !== -1;
    },
    cancel_statuses() {
      const appointmentCancelStatuses = this.$store.state.appointment_cancel_statuses

      if (this.patient) {
        const appointmentDate = moment(`${this.patient.appointment_date} ${this.patient.appointment_time}`, 'MM/DD/YYYY h:mm A')
        const currentDate = moment()

        const diffInHours = appointmentDate.diff(currentDate, 'hours')

        if (diffInHours > 24) {
          return appointmentCancelStatuses.filter(el => !this.reasons_for_pay.includes(el.status));
        }
      }

      return appointmentCancelStatuses;
    },
    canChargeLateCancellationFee() {
      let canChargeLateCancellationFee = true;

      if (this.patientLateCancellationFeeInfo) {
        const properties = Object.values(this.patientLateCancellationFeeInfo.booking_cancellation_policy);
        canChargeLateCancellationFee = properties.every((property) => property);
      }

      return canChargeLateCancellationFee;
    },
    showLateCancellationFeeInConfirmation() {
      return this.is_status_for_pay_selected && this.canChargeLateCancellationFee;
    },
    cancellationFeeToCharge() {
      if (!this.is_status_for_pay_selected || !this.canChargeLateCancellationFee) {
        return 0;
      }

      return this.collectLateCancellationFee ? this.patientLateCancellationFeeInfo.charge_for_cancellation : 0;
    },
    upcomingReauthorizationRequestsMinVisitsCount() {
      if (!this.patient || Array.isArray(this.patient) || !this.patient.insurance_plan) {
        return null;
      }

      return this.patient.insurance_plan.reauthorization_notification_visits_count;
    },
    reasonIsCancelledByPatient() {
      return this.reason === CANCELLED_BY_PATIENT_APPOINTMENT_STATUS_ID;
    },
    pickerOptions() {
      return {
        disabledDate(time) {
          return time.getTime() > Date.now();
        },
        selectableRange: this.selectableRange,
      };
    },
  },

  watch: {
    reason(value) {
      if (!value) {
        return;
      }

      if (this.reasonIsCancelledByPatient) {
        this.patientRequestedCancellationAt = moment().format('MM/DD/YYYY hh:mm A');
      } else {
        this.patientRequestedCancellationAt = null
      }

      $("#cancellation-reason").removeClass("input-error");
      $("label[for=cancellation-reason]").removeClass("label-error");
    },
    patientRequestedCancellationAt(value) {
      if (!value) {
        return;
      }

      this.handlePatientRequestedCancellationAtChange();

      $("#patient-requested-cancellation-at").removeClass("input-error");
      $("label[for=patient-requested-cancellation-at]").removeClass("label-error");
    },

    comment() {
      if (this.comment.length) {
        $("#cancel-comment").removeClass("input-error");
      }
    },

    patient(value) {
      if (!value || !value.id) {
        return;
      }

      this.$store.dispatch('checkPatientLateCancellationPayment', value.id)
        .then(res => this.patientLateCancellationFeeInfo = res.data)
    },

    collectLateCancellationFee(value) {
      if (value || value === 0) {
        $("#cancellation-fee").removeClass("label-error");
      }
    }
  },

  methods: {
    resetData() {
      this.reason = null;
      this.errorMessage = null;
      this.comment = "";
      this.collectLateCancellationFee = null;
      this.$store.commit("setVal", {
        key: "complete_appointment_data",
        val: [],
      });
      $(".input-error").removeClass("input-error");
    },

    cancelAppointment() {
      const patientId = this.patient.id;

      this.is_saving = true;
      this.showConfirmation = false;
      
      const patientRequestedCancellationAt = this.patientRequestedCancellationAt
        ? moment(this.patientRequestedCancellationAt, 'MM/DD/YYYY hh:mm A').format('YYYY-MM-DD HH:mm:ss')
        : null;

      const data = {
        appointmentId: this.patient.appointment_id,
        status: this.reason,
        comment: this.comment,
        charge_for_cancellation: this.cancellationFeeToCharge,
        patient_id: this.patient.id,
        patient_requested_cancellation_at: patientRequestedCancellationAt
      };

      const storeCommentRequestData = {
        patient_id: this.patient.id,
        comment: this.comment,
        comment_type: CANCELLATION_COMMENT_TYPE,
        appointment_id: this.patient.appointment_id,
        metadata: {
          visit_reason: this.patient.reason_for_visit
        }
      };

      this.$store.dispatch("cancelAppointment", data).then((response) => {
        if (response.status === 201) {
          $("#cancel-appointment").modal("hide");
          this.resetData();
          this.is_saving = false;
          this.$emit("canceled");
          this.$root.$emit('refresh-сalendar');
          this.$store.dispatch('getProviderTodayPatients');

          if (data.charge_for_cancellation
              && this.$route.name === 'patient-chart'
              && this.$route.params.id === String(patientId)
          ) {
            this.$store.dispatch('getPatient', {patientId: patientId});
            this.$store.dispatch('getPatientPreprocessedTransactions', patientId);
          }

          this.$store.dispatch("storeComment", storeCommentRequestData).then((response) => {
            if (response.status === 201) {
              if (this.$route.name === 'patient-chart' && this.$route.params.id === String(storeCommentRequestData.patient_id)) {
                this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {
                  id: storeCommentRequestData.patient_id,
                });
              }
              this.$store.dispatch("getProviderMessages");
            }
          });
        } else if (response.status === 422) {
          const errors = response.data.errors
            ? Object.values(response.data.errors).reduce((prev, curr) => prev.concat(curr))
            : [];
          if (errors.length) {
            this.errorMessage = errors[0];
          }
          this.is_saving = false;
        } else if (response.status === 404 || response.status === 409) {
          this.is_saving = false;
          this.errorMessage = response.data.error;
        }
      });
    },

    getFormattedDate(date) {
      return this.$moment(date).format("MM/DD/YYYY");
    },

    getFullPatientName() {
      let name = this.patient.first_name + " " + this.patient.last_name;
      if (this.patient.middle_initial) {
        name += " " + this.patient.middle_initial;
      }
      return name;
    },

    getVisitsVal(val) {
      if (val !== null && val !== undefined) {
        return val;
      }
      return 0;
    },

    getEffClass(patient) {
      let res = "";
      if (patient.is_overdue) {
        res = "overdue-eff-stop-date";
      } else if (patient.is_eff_almost_overdue) {
        res = "almost-overdue-eff-stop-date";
      }
      return res;
    },

    closeApptModal() {
      $("#cancel-appointment").modal("hide");
      this.resetData();
    },

    showConfirmationDialog() {
      this.errorMessage = null;
      let has_errors = false;
      if (!this.reason) {
        $("#cancellation-reason").addClass("input-error");
        $("label[for=cancellation-reason]").addClass("label-error");
        has_errors = true;
      }

      if (this.reasonIsCancelledByPatient && !this.patientRequestedCancellationAt) {
        $("#patient-requested-cancellation-at").addClass("input-error");
        $("label[for=patient-requested-cancellation-at]").addClass("label-error");
        has_errors = true;
      }

      this.comment = this.comment.trim();
      if (!this.comment.length) {
        $("#cancel-comment").addClass("input-error");
        has_errors = true;
      }
      if (this.is_status_for_pay_selected && this.canChargeLateCancellationFee && this.collectLateCancellationFee === null) {
        $("#cancellation-fee").addClass("label-error");
        has_errors = true;
      }

      if (!has_errors) {
        this.showConfirmation = true;
      }
    },

    handlePatientRequestedCancellationAtChange() {
      const currentDay = moment().startOf('day');
      const patientRequestedCancellationAt = moment(this.patientRequestedCancellationAt, "MM/DD/yyyy hh:mm A").startOf('day');

      if (currentDay.isSame(patientRequestedCancellationAt, "day")) {
        this.selectableRange = `00:00:00 - ${moment().format("HH:mm:ss")}`;
      } else {
        this.selectableRange = this.initSelectableRange;
      }
    }
  }
};
</script>

<style scoped lang="scss">
.confirmation-window {
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 50px;
  padding-bottom: 15vh;

}

.custom-input-width {
  width: 234px !important;
}

#cancellation-fee {
  margin-top: 10px;

  div {
    align-items: start;
    gap: 15px;

    .cancellation-fee-radio {
        font-size: 16px !important;
    }
  }
}
</style>