<template>
  <div class="modal appt-modal-vertical-center fade appt-modal appt-modal-warning" id="reschedule-appointment"
    data-backdrop="static" data-keyboard="false" v-if="patient">

    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" @click.prevent="closeApptModal()" :disabled="is_saving">
            &times;
          </button>
          <h4 class="modal-title">Appointment Status Change</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-xs-6" style="padding-right: 10%; margin-bottom: 40px">
              <h4 class="title">Appointment Info</h4>
              <div class="row">
                <div class="col-xs-12">
                  {{ getFullPatientName() }},
                  <small class="years-old" v-if="patient.years_old > 0">{{ patient.years_old }} years old</small>
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
                      :class="{'almost-overdue-eff-stop-date': patient.visits_auth_left > 0 && patient.visits_auth_left <= upcomingReauthorizationRequestsMinVisitsCount, 'overdue-eff-stop-date': patient.visits_auth_left <= 0}">
                      {{ getVisitsVal(patient.visits_auth_left) }}
                    </span>
                    out of {{ getVisitsVal(patient.visits_auth) }} visits left
                  </div>
                  <div class="col-xs-12" v-if="patient.eff_start_date && patient.eff_stop_date">
                    {{ getFormattedDate(patient.eff_start_date) }} -
                    <span :class="getEffClass(patient)">
                      {{ getFormattedDate(patient.eff_stop_date) }}
                    </span>
                  </div>
                </template>
              </div>
            </div>

            <div class="col-xs-12">
              <h4 class="title" style="margin-bottom: 5px">Appointment Date & Time</h4>

              <el-form :model="ruleForm" :rules="rules" ref="ruleForm" class="custom-form">
                <div class="row">
                  <el-form-item class="col-12 col-md-6" label="Date" prop="date">
                    <el-date-picker type="date" format="MM/dd/yyyy" value-format="MM/dd/yyyy"
                      :picker-options="datePickerOptions" v-model="ruleForm.date" :clearable="false">
                    </el-date-picker>
                  </el-form-item>
                  <el-form-item class="col-12 col-md-6" label="Time" prop="time">
                    <el-select v-model="ruleForm.time" name="time" placeholder="">
                      <el-option v-for="(hour, index) in workedHours" :key="index + hour" :value="hour">
                      </el-option>
                    </el-select>
                  </el-form-item>
                </div>
                <div class="row">
                  <el-form-item class="col-12 col-md-6" label="Visit Type" prop="visit_type">
                    <el-radio-group v-model="ruleForm.visit_type" :disabled="isVirtualTypeDisabled"
                      @change="changeVisitType">
                      <el-radio :label="inPersonal">
                        <i class="fa fa-user"></i>
                        In person
                      </el-radio>
                      <el-radio :label="virtual">
                        <i class="fa fa-video-camera"></i>
                        Virtual
                      </el-radio>
                    </el-radio-group>
                  </el-form-item>
                </div>
                <div class="row" v-if="ruleForm.visit_type">
                  <el-form-item
                    id="reason_for_visit"
                    class="col-12 col-md-6"
                    label="Visit Reason"
                    prop="reason_for_visit"
                  >
                    <el-select
                        v-if="!filteredVisitReasonList || !filteredVisitReasonList.length"
                        value=""
                        name="reason_for_visit"
                        placeholder=""
                        :disabled="true"
                    ></el-select>
                    <el-select
                        v-else
                        v-model="ruleForm.reason_for_visit"
                        name="reason_for_visit"
                        placeholder=""
                    >
                        <el-option v-for="reason in filteredVisitReasonList" :key="reason.name" :value="reason.id" :label="reason.name">
                        </el-option>
                    </el-select>
                  </el-form-item>
                </div>

                <!-- <div v-if="feePerVisit" class="alert alert-info form-group">
                  You will be paid: {{ feePerVisit }}$
                </div> -->
                
                <div class="row-virtual-data col-12" v-if="ruleForm.visit_type === virtual">
                  <div class="row-virtual-data__checked">
                      <el-checkbox v-model="isInvite">Invite to join a Telehealth session</el-checkbox>
                  </div>
                  <template v-if="isInvite">
                      <div class="row-virtual-data__description">
                          Please provide an email address or phone number to invite
                          patient to join in a Telehealth session
                      </div>
                      <div class="row-virtual-data-wrapper">
                          <el-form-item
                              v-if="currentProvider && currentProvider.upheal_user_id"
                              style="margin-bottom: 10px"
                              class="col-12 col-md-6 col-md-offset-right-6 form-item-virtual-data"
                              label="Telehealth provider"
                              prop="telehealth_provider"
                              id="telehealth_provider"
                          >
                              <el-select
                                  v-model="ruleForm.telehealth_provider"
                                  name="telehealth_provider"
                                  placeholder=""
                                  @change="changeTelehealthProvider"
                              >
                                  <el-option
                                      v-for="telehealthProvider in telehealthProviderList"
                                      :key="telehealthProvider.id"
                                      :value="telehealthProvider.id"
                                      :label="telehealthProvider.name"
                                  >
                                  </el-option>
                              </el-select>
                          </el-form-item>

                          <el-form-item style="margin-bottom: 10px" class="col-12 col-md-6 form-item-virtual-data"
                              label="Send Invitation via Email" prop="email">
                              <el-checkbox v-model="ruleForm.send_telehealth_link_via_email" :disabled="isUphealProviderSelected" @change="
                                  changeCheckVirtualData(
                                      ruleForm.send_telehealth_link_via_email,
                                      'email'
                                  )
                              " />
                              <el-input type="email" :disabled="!ruleForm.send_telehealth_link_via_email"
                                  v-model="ruleForm.email" name="email" id="email" placeholder="Email">
                              </el-input>
                          </el-form-item>
                          <el-form-item style="margin-bottom: 10px" class="col-12 col-md-6 form-item-virtual-data"
                              label="Send Invitation via Add Email" prop="secondary_email">
                              <el-checkbox v-model="ruleForm.send_telehealth_link_via_secondary_email" @change="
                                  changeCheckVirtualData(
                                      ruleForm.send_telehealth_link_via_secondary_email,
                                      'secondary_email'
                                  )
                              " />
                              <el-input type="email" :disabled="
                                  !ruleForm.send_telehealth_link_via_secondary_email
                              " v-model="ruleForm.secondary_email" name="secondary_email" id="secondary_email"
                                  placeholder=" Add Email">
                              </el-input>
                          </el-form-item>
                          <el-form-item style="margin-bottom: 10px"
                              class="col-12 col-md-6 form-item-virtual-data form-item-virtual-data--phone"
                              label="Send Invitation via SMS" prop="phone">
                              <el-checkbox v-model="ruleForm.send_telehealth_link_via_sms" @change="
                                  changeCheckVirtualData(
                                      ruleForm.send_telehealth_link_via_sms,
                                      'phone'
                                  )
                              " />
                              <el-input type="tel" :disabled="!ruleForm.send_telehealth_link_via_sms"
                                  v-model="ruleForm.phone" name="phone" v-mask="'(###)-###-####'" :masked="true"
                                  id="phone" placeholder="Phone">
                              </el-input>
                          </el-form-item>
                          <el-form-item style="margin-bottom: 10px"
                              class="col-12 col-md-6 form-item-virtual-data form-item-virtual-data--date"
                              label="Schedule Invitation" prop="telehealth_notification_date">
                              <el-date-picker 
                                ref="notificationPicker" 
                                v-model="ruleForm.telehealth_notification_date"
                                popper-class="appointment-date-picker" 
                                format="MM/dd/yyyy hh:mm A"
                                value-format="MM/dd/yyyy hh:mm A" 
                                :disabled="!ruleForm.time"
                                :picker-options="notificationPickerOptions" 
                                type="datetime"
                                placeholder="Select date and time" 
                              />
                          </el-form-item>
                          <div class="col-md-12" v-if="isShowJoinByPhone">
                              <el-checkbox style="margin-top: 8px" v-model="ruleForm.allow_to_join_by_phone">Allow to
                                  Join by Phone
                              </el-checkbox>
                              <p class="help-block" style="margin-top: 0; font-weight: normal; font-size: 11px">
                                  Checking the "Allow to Join by Phone" box above will allow
                                  patients to join this Telehealth session by dialing a phone
                                  number provided and entering a PIN code included in this
                                  invitation.
                              </p>
                          </div>
                      </div>
                  </template>
                </div>
              </el-form>
            </div>

            <div class="col-xs-12">
              <h4 class="title" style="margin-bottom: 5px">Reschedule Reason</h4>


              <div class="reason-block text-center">
                <label for="reschedule-reason">Select Reason</label>
                <el-select
                    id="reschedule-reason"
                    v-model="ruleForm.reschedule_sub_status_id"
                    placeholder=""
                    style="width: 234px"
                  >
                    <el-option 
                      v-for="status in rescheduledSubStatuses" 
                      :value="status.id" 
                      :key="status.id"    
                      :label="status.status"
                    />
                  </el-select>
              </div>
            </div>

            <div class="col-xs-12" style="margin-top: 20px">
              <h4 class="title">Comment</h4>
              <div class="comment-block">
                <div class="form-group" style="margin-bottom: 5px">
                  <textarea
                    v-model="comment"
                    id="reschedule-comment"
                    class="form-control no-resize"
                    placeholder="Comment..."
                    rows="4"
                    :disabled="is_saving"
                    maxlength="255"
                  ></textarea>
                </div>
                <div v-if="errorMessage" class="text-red validation-error-msg" style="font-size: 16px">{{ errorMessage }}</div>
                <el-button @click.prevent="rescheduleAppointment" :loading="is_saving"
                  type="warning">Reschedule</el-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
  
<script>
import { WEEKLY_PERMITTED_NUMBER_OF_DAYS, BIWEEKLY_PERMITTED_NUMBER_OF_DAYS, BIWEEKLY_VISIT_FREQUENCY_ID, MONTHLY_PERMITTED_NUMBER_OF_DAYS, MONTHLY_VISIT_FREQUENCY_ID, RESCHEDULE_COMMENT_TYPE, VIRTUAL_VISIT_TYPE, COFFEE_BEANS_OFFICE_ROOM_ID, IN_PERSON_VISIT_TYPE, RESCHEDULED_BY_OFFICE_SUB_STATUS_ID } from '../../settings';
import NotificationPicker from "../../mixins/notification-picker";

export default {
  mixins: [NotificationPicker],

  mounted() {
    this.initOfficeList();
    this.$store.dispatch('getRescheduleAppointmentSubStatuses');
  },

  data() {
    return {
      datePickerOptions: {
        disabledDate(time) {
          return time.getTime() < Date.now() - 8.64e7;
        },
      },
      ruleForm: {
        id: null,
        date: "",
        time: "",
        office_id: null,
        office_room: null,
        reason_for_visit: "",
        telehealth_provider: "google_meet",
        send_telehealth_link_via_email: false,
        send_telehealth_link_via_secondary_email: false,
        send_telehealth_link_via_sms: false,
        email: "",
        secondary_email: "",
        phone: "",
        telehealth_notification_date: null,
        allow_to_join_by_phone: false,
        reschedule_sub_status_id: null,
      },
      rules: {
        date: [
          {
            required: true,
            message: "The date field is required",
            trigger: "change",
          },
        ],
        time: [
          {
            required: true,
            message: "The time field is required",
            trigger: "change",
          },
        ],
        visit_type: [
          {
            required: true,
            message: "The visit type field is required",
            trigger: "change",
          },
        ],
        office_id: [
          {
            required: true,
            message: "The office field is required",
            trigger: "change",
          },
        ],
        office_room: [
          {
            required: true,
            message: "The room field is required",
            trigger: "change",
          },
        ],
        reason_for_visit: [
          {
            required: true,
            message: "The visit reason field is required",
            trigger: "blur",
          },
        ],
        telehealth_notification_date: [
          {
            required: true,
            message: "The schedule invitation field is required",
            trigger: "change",
          },
        ],
        email: [
          {
            required: false,
          }
        ],
      },
      isDisable: false,
      isVirtualTypeDisabled: false,
      inPersonal: IN_PERSON_VISIT_TYPE,
      virtual: VIRTUAL_VISIT_TYPE,
      officeList: [],
      is_saving: false,
      comment: "",
      isInvite: false,
      telehealthProviderList: [
        {
          id: "google_meet",
          name: "Google meet",
        },
        {
          id: "upheal",
          name: "Upheal",
        },
      ],
    //   feePerVisit: null,
      errorMessage: null,
    };
  },

  computed: {
    patient() {
      return this.$store.state.complete_appointment_data;
    },
    completeAction() {
      return this.$store.state.complete_appointment_action;
    },
    currentProvider() {
      return this.$store.state.currentProvider;
    },
    totalVisitMinutes() {
      let minutes = [];
      for (let i = 1; i <= 2; i++) {
        let step = 30;
        step *= i;
        minutes.push(step);
      }
      return minutes;
    },
    visitReasonList() {
      return this.$store.state.treatmentModalities;
    },
    filteredVisitReasonList() {
      const isTelehealth = this.ruleForm.visit_type === this.virtual ? 1 : 0;

      return this.visitReasonList.filter(treatmentModality => Number(treatmentModality.is_telehealth) === isTelehealth);
    },
    workedHours() {
      const items = [];
      new Array(24).fill("", 7, 22).forEach((acc, index) => {
        items.push(moment({ hour: index }).format("h:mm A"));
        items.push(moment({ hour: index, minute: 15 }).format("h:mm A"));
        items.push(moment({ hour: index, minute: 30 }).format("h:mm A"));
        items.push(moment({ hour: index, minute: 45 }).format("h:mm A"));
      });
      return items;
    },
    upcomingReauthorizationRequestsMinVisitsCount() {
      if (!this.patient || Array.isArray(this.patient) || !this.patient.insurance_plan) {
        return null;
      }

      return this.patient.insurance_plan.reauthorization_notification_visits_count;
    },
    isUphealProviderSelected() {
      return this.ruleForm.telehealth_provider === 'upheal';
    },
    isShowJoinByPhone() {
      if (this.isUphealProviderSelected) {
        return false;
      }

      let condition = moment().isSame(this.ruleForm.date, "day");
      if (!condition) {
        this.$set(this.ruleForm, "allow_to_join_by_phone", false);
      }
      return condition;
    },
    isUserAdmin() {
      return Boolean(this.$store.state.isUserAdmin);
    },
    rescheduledSubStatuses() {
      const rescheduledSubStatuses = this.$store.state.appointment_reschedule_sub_statuses;

      if (!this.isUserAdmin) {
        return rescheduledSubStatuses.filter(status => status.id !== RESCHEDULED_BY_OFFICE_SUB_STATUS_ID);
      }

      return rescheduledSubStatuses;
    },
  },

  watch: {
    patient(value) {
      if (!value || !value.id) {
        return;
      }

      if (this.completeAction === 'reschedule') {
        this.fetchTreatmentModalities();
      }
      
      this.initAppointmentData();
    },
    comment() {
      if (this.comment.length) {
        $("#reschedule-comment").removeClass("input-error");
      }
    },
    "ruleForm.visit_type"(newValue, oldValue) {
      if (!oldValue) {
        return;
      }
      this.ruleForm.reason_for_visit = "";
    },
    "ruleForm.reason_for_visit"(value) {
    //   this.getFeePerVisit(this.ruleForm.patient_id, value);

      setTimeout(() => {
        this.$refs.ruleForm.clearValidate('reason_for_visit');
      }, 10);
    },
    "ruleForm.reschedule_sub_status_id"(value) {
      if (!value) {
        return;
      }

      this.removeRescheduleReasonInputErrorClass();
    },
  },

  methods: {
    getFormattedDate(date) {
      return this.$moment(date).format("MM/DD/YYYY");
    },

    fetchTreatmentModalities() {
      const params = {
        patient_id: this.patient.id,
        appointment_id: this.patient.appointment_id,
      };
      this.$store.dispatch('getTreatmentModalities', params)
        .then(() => {
            // reset value if current reason is not in the list
            const currentVisitReason = this.visitReasonList.find(el => el.id === this.ruleForm.reason_for_visit);
            if (!currentVisitReason) {
                this.ruleForm.reason_for_visit = null;
            }
        });
    },
    
    initAppointmentData() {
      this.ruleForm = {
        id: this.patient.appointment_id,
        date: this.patient.appointment_date,
        time: this.patient.appointment_time,
        reason_for_visit: this.patient.treatment_modality_id,
        visit_type: this.patient.office_room_id
          ? this.inPersonal
          : this.virtual,
        office_id: this.patient.offices_id || null,
        office_room: this.patient.office_room_id || null,
        patient_id: this.patient.id,
        provider_id: this.patient.appointment_provider_id,
        email: this.patient.email,
        secondary_email: this.patient.secondary_email,
        phone: this.patient.cell_phone,
        telehealth_provider: 'google_meet',
        reschedule_sub_status_id: null
      };

      let dateDay = moment(this.patient.appointment_date, 'MM/DD/YYYY').day();
      if (dateDay === 0) {
        dateDay = 7;
      }
      const startOfWeek = moment(this.patient.appointment_date, 'MM/DD/YYYY').subtract(dateDay - 1, 'days').format('MM/DD/YYYY');
      
      const permittedNumberOfDays = this.getPermittedNumberOfDays(this.patient.visit_frequency_id);
      const endOfWeek = moment(startOfWeek, 'MM/DD/YYYY').add(permittedNumberOfDays, 'days').format('MM/DD/YYYY');

      this.datePickerOptions = {
        disabledDate(time) {
          const date = moment(time).startOf('day');
          return !date.isBetween(startOfWeek, endOfWeek, 'day', '[]') || time.getTime() < Date.now() - 8.64e7;
        },
      };
    },

    getPermittedNumberOfDays(visit_frequency_id) {
        if (visit_frequency_id === BIWEEKLY_VISIT_FREQUENCY_ID) {
          return BIWEEKLY_PERMITTED_NUMBER_OF_DAYS;
        }
        if (visit_frequency_id === MONTHLY_VISIT_FREQUENCY_ID) {
          return MONTHLY_PERMITTED_NUMBER_OF_DAYS;
        }
        return WEEKLY_PERMITTED_NUMBER_OF_DAYS;
    },

    rescheduleAppointment() {
      this.errorMessage = null;

      this.$refs['ruleForm'].validate((valid) => {
        let has_errors = false;
        this.comment = this.comment.trim();

        if (!this.ruleForm.reschedule_sub_status_id) {
          $("#reschedule-reason").addClass("input-error");
          $("label[for=reschedule-reason]").addClass("label-error");
          has_errors = true;
        }

        if (!this.comment.length) {
          $("#reschedule-comment").addClass("input-error");
          has_errors = true;
        }

        if (!valid || has_errors) {
          console.log("error submit!!");
          return false;
        }

        this.is_saving = true;
        let data = this.ruleForm;

        if (!this.isInvite) {
          delete data.email;
        }

        data.comment = this.comment;

        const currentVisitReason = this.visitReasonList.find(el => el.id === this.ruleForm.reason_for_visit);
        let requestData = {
          patient_id: this.patient.id,
          comment: this.comment,
          comment_type: RESCHEDULE_COMMENT_TYPE,
          appointment_id: this.patient.appointment_id,
          metadata: {
            old_time: moment(`${this.patient.appointment_date} ${this.patient.appointment_time}`, 'MM/DD/YYYY h:mm A').unix(),
            new_time: moment(`${this.ruleForm.date} ${this.ruleForm.time}`, 'MM/DD/YYYY h:mm A').unix(),
            visit_reason: currentVisitReason ? currentVisitReason.name : null,
          }
        };

        this.$store.dispatch("rescheduleAppointment", data).then((response) => {
          if (response.status === 201) {
            this.closeApptModal();

            this.$store.dispatch("storeComment", requestData).then((response) => {
              if (response.status === 201) {
                if (this.$route.name === 'patient-chart' && this.$route.params.id === String(requestData.patient_id)) {
                  this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {
                    id: requestData.patient_id,
                  });
                }
              }
            })

            this.$emit("rescheduled");
            this.$root.$emit('refresh-сalendar');
            this.$store.dispatch('getProviderTodayPatients');
            this.$store.dispatch("getProviderMessages");

            if (this.$route.name === 'patient-chart') {
              this.$store.dispatch('getPatientAppointments', requestData.patient_id);
            }
          } else if (response.status === 422) {
            const errors = response.data.errors
              ? Object.values(response.data.errors).reduce((prev, curr) => prev.concat(curr))
              : [];
            if (errors.length) {
              this.errorMessage = errors[0];
            }
          } else {
            this.errorMessage = response.data.message;
          }
          this.is_saving = false;
        })
      })
    },

    removeRescheduleReasonInputErrorClass() {
      $("#reschedule-reason").removeClass("input-error");
      $("label[for=reschedule-reason]").removeClass("label-error");
    },

    resetData() {
      this.isInvite = false;
      this.errorMessage = null;
      this.comment = "";
      $("#reschedule-comment").removeClass("input-error");
      this.removeRescheduleReasonInputErrorClass();
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
      $("#reschedule-appointment").modal("hide");
      this.resetData();
    },

    changeVisitType(value) {
      let officeRoomValue = null

      if (value === IN_PERSON_VISIT_TYPE) {
        officeRoomValue = COFFEE_BEANS_OFFICE_ROOM_ID;
      }

      this.$set(this.ruleForm, "office_room", officeRoomValue);
    },

    initOfficeList() {
      this.$store.dispatch("getOfficeListForAppointments").then(({ data }) => {
        this.officeList = data.offices;
      });
    },

    changeTelehealthProvider() {
      this.$set(this.ruleForm, "allow_to_join_by_phone", false);

      if (this.isUphealProviderSelected) {
        this.$set(this.ruleForm, "send_telehealth_link_via_email", true);
        this.changeCheckVirtualData(true, 'email');
      }
    },

    changeCheckVirtualData(check, field) {
      if (check) {
        this.rules[field] = [
          {
            required: true,
            message: `The ${field} field is required`,
            trigger: "blur",
          },
        ];
        if (field === "email") {
          this.rules[field].push({
            type: "email",
            message: "Invalid email address",
            trigger: "blur",
          });
        }
      } else {
        this.$refs.ruleForm.clearValidate(field);
        this.rules[field] = [{ required: false }];
      }
    },

    // getFeePerVisit(patientId, treatmentModalityId) {
    //   if (!patientId || !treatmentModalityId) {
    //     this.feePerVisit = null;
    //     return;
    //   }

    //   const payload = { patientId, treatmentModalityId };

    //   this.$store.dispatch("getFeePerVisitForProvider", payload)
    //     .then(({ data }) => {
    //       this.feePerVisit = data.fee_per_visit;
    //     })
    //     .catch(() => {
    //       this.feePerVisit = null;
    //     });
    // }
  }
};
</script>