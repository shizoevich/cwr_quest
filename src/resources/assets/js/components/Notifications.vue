<template>
  <div class="appointments-table-container">
    <div class="appointments-wrapper">
      <div class="alert-form-block">
        <button
          v-if="isUserAdmin"
          type="button"
          class="btn btn-warning alert-btn"
          @click="showModal"
        >
          Add New Alert
        </button>
        <div
          class="modal fade"
          id="alertMessageModal"
          tabindex="-1"
          role="dialog"
          aria-labelledby="alertMessageTitle"
          aria-hidden="true"
          data-backdrop="static"
          data-keyboard="false"
        >
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Add Alert</h5>
                <button
                  type="button"
                  class="close"
                  data-dismiss="modal"
                  aria-label="Close"
                  @click.prevent="closeModal"
                >
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <form @submit.prevent="addPatientAlertData">
                <div class="modal-body">
                  <div class="form-group">
                    <label for="alertMessage">
                      Co-pay
                    </label>
                    <input
                      type="number"
                      v-model="alert_data.co_pay"
                      min="0"
                      step="0.01"
                      class="form-control"
                      @blur="handleBlur('co_pay')"
                      @wheel="handleWheel"
                    />
                  </div>
                  <div class="form-group">
                    <label for="alertMessage">
                      Deductible
                    </label>
                    <input
                      type="number"
                      v-model="alert_data.deductible"
                      min="0"
                      step="0.01"
                      class="form-control"
                      @blur="handleBlur('deductible')"
                      @wheel="handleWheel"
                    />
                  </div>
                  <div class="d-flex gap-3">
                    <div class="form-group flex-grow-1">
                      <label for="alertMessage">
                        Deductible Met
                      </label>
                      <input
                        type="number"
                        v-model="alert_data.deductible_met"
                        min="0"
                        step="0.01"
                        class="form-control"
                        @blur="handleBlur('deductible_met')"
                        @wheel="handleWheel"
                      />
                    </div>
                    <div class="form-group flex-grow-1">
                      <label for="alertMessage">
                        Remaining Deductible
                      </label>
                      <input
                        type="number"
                        v-model="alert_data.deductible_remaining"
                        min="0"
                        step="0.01"
                        class="form-control"
                        @blur="handleBlur('deductible_remaining')"
                        @wheel="handleWheel"
                      />
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="alertMessage">
                      Insurance Pay
                    </label>
                    <input
                      type="number"
                      v-model="alert_data.insurance_pay"
                      min="0"
                      step="0.01"
                      class="form-control"
                      @blur="handleBlur('insurance_pay')"
                      @wheel="handleWheel"
                    />
                  </div>
                  <div class="form-group">
                    <label for="referenceNumber">Reference number</label>
                    <input
                      type="text"
                      v-model="alert_data.reference_number"
                      class="form-control"
                      id="referenceNumber"
                      placeholder="Enter reference number"
                      maxlength="128"
                    />
                  </div>
                  <div class="form-group">
                    <label for="alertMessage">
                      Alert Message
                      <span class="text-red">*</span>
                    </label>
                    <input
                      type="text"
                      v-model="alert_data.message"
                      class="form-control"
                      id="alertMessage"
                      placeholder="Enter alert message"
                      maxlength="128"
                    />
                  </div>
                  <div class="form-group">
                    <label for="uploadFile">Upload file</label>
                    <div class="file-input">
                      <input
                        id="uploadFile"
                        ref="uploadFile"
                        type="file"
                        class="form-control"
                        @change="handleFileUpload"
                      />
                      <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-primary" ref="uploadButton" type="button" @click.stop="triggerFileInput">Select file</button>
                        <span v-if="alert_data.file">{{ alert_data.file.name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <template v-if="!isLoading">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button
                      type="button"
                      class="btn btn-default"
                      data-dismiss="modal"
                      aria-label="Close"
                      @click.prevent="closeModal"
                    >
                      Close
                    </button>
                  </template>

                  <pageloader add-classes="save-loader" v-show="isLoading"></pageloader>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div
        class="table-responsive"
        v-if="notifications && notifications.length"
      >
        <table class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th>Date Created</th>
              <th>Co-pay</th>
              <th>Deductible</th>
              <th>Deductible Met</th>
              <th>Remaining Deductible</th>
              <th>Insurance Pay</th>
              <th>Reference Number</th>
              <th>Message</th>
              <th>Recorded By</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(notification, index) in notifications"
              :class="{ odd: index % 2 === 0 }"
              :key="index"
            >
              <td class="text-center">
                {{ notification.formatted_date_created }}
              </td>
              <td>{{ notification.co_pay }}</td>
              <td>{{ notification.deductible }}</td>
              <td>{{ notification.deductible_met }}</td>
              <td>{{ notification.deductible_remaining }}</td>
              <td>{{ notification.insurance_pay }}</td>
              <td>{{ notification.reference_number || '-' }}</td>
              <td>{{ notification.message }}</td>
              <td>{{ notification.recorded_by_name }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="no-appointments" v-else>
        No notification records for this patient.
      </div>
    </div>
  </div>
</template>

<script>
import InsuranceInput from "../mixins/insurance-input.js";
import {parseMoney} from "../helpers/parseMoney";

const INITIAL_ALERT_DATA = {
  message: "",
  co_pay: 0,
  deductible: 0,
  deductible_met: 0,
  deductible_remaining: 0,
  insurance_pay: 0,
  reference_number: "",
  file: null,
}

export default {
  mixins: [InsuranceInput],

  data() {
    return {
      alert_data: {},
      componentKey: 0,
      tableData: [],
      isLoading: false,
    };
  },

  mounted() {
    this.tableData = this.$store.state.patient_notifications;
  },

  computed: {
    notifications() {
      return this.$store.state.patient_notifications;
    },
    isUserAdmin() {
      return this.$store.state.isUserAdmin;
    },
  },

  methods: {
    getNotificationStatus(status) {
      status = parseInt(status);
      switch (status) {
        case 0:
          return "OFF";
        case 1:
          return "ON";
        case 2:
          return "RESOLVED";
        default:
          return status;
      }
    },

    validateAlertForm() {
      if (!this.alert_data.message) {
        $("#alertMessage").addClass("input-error");
        return false;
      }

      return true;
    },

    addPatientAlertData() {
      if (!this.validateAlertForm()) {
        return;
      }

      this.isLoading = true;

      const alertData = this.alert_data;

      const formData = new FormData();

      formData.append('patient_id', this.$store.state.currentPatient.id);

      Object.keys(alertData).forEach(key => {
        const value = alertData[key];

        if (value === null) {
          return;
        }

        formData.append(key, value);
      });

      this.$store.dispatch("addPatientAlert", formData)
        .then((response) => {
          if (!response || response.status !== 200) {
            this.$message({
              type: "error",
              message: "Oops, something went wrong!"
            });
            return
          }
          
          const {
            patient_id,
            message,
            date_created,
            status,
            co_pay,
            deductible,
            deductible_met,
            deductible_remaining,
            insurance_pay,
            reference_number,
            recorded_by_name
          } = response.data;

          const newPatientAlert = {
            patient_id,
            message,
            status,
            co_pay,
            deductible,
            deductible_met,
            deductible_remaining,
            insurance_pay,
            reference_number,
            formatted_date_created: moment(date_created).format('MM/DD/YYYY'),
            recorded_by_name,
          };
          this.tableData.unshift(newPatientAlert);
          this.$store.state.patient_notifications_count += 1;

          this.$store.dispatch('getPatient', {patientId: patient_id});

          this.closeModal();
        })
        .catch(() => {
          this.$message({
            type: "error",
            message: "Oops, something went wrong!"
          });
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    showModal() {
        this.initAlertData();
        $("#alertMessageModal").modal("show");
    },

    initAlertData() {
      let alertData = Object.assign({}, INITIAL_ALERT_DATA);
      if (this.tableData && this.tableData.length) {
        const latestNotification = this.tableData[0];
        alertData = {
          co_pay: parseMoney(latestNotification.co_pay),
          deductible: parseMoney(latestNotification.deductible),
          deductible_met: parseMoney(latestNotification.deductible_met),
          deductible_remaining: parseMoney(latestNotification.deductible_remaining),
          insurance_pay: parseMoney(latestNotification.insurance_pay),
          reference_number: latestNotification.reference_number,
        };
      }

      this.alert_data = alertData;
    },

    closeModal() {
      this.resetAlertData();
      this.resetValidationErrors();
      $("#alertMessageModal").modal("hide");
    },

    resetAlertData() {
      this.alert_data = {};
    },

    resetValidationErrors() {
      $("#alertMessage").removeClass("input-error");
    },

    handleBlur(fieldName) {
      this.handleInputNumberBlur("alert_data", fieldName);
    },

    handleFileUpload(e) {
      const file = e.target.files[0];

      if (file) {
        this.alert_data.file = file;
      }

      this.$refs.uploadButton.blur();
    },

    triggerFileInput() {
      this.$refs.uploadFile.click();
    }
  },

  watch: {
    "alert_data.message"(value) {
      if (value) {
        $("#alertMessage").removeClass("input-error");
      }
    },
  }
};
</script>

<style scoped lang="scss">
.alert-form-block {
  display: flex;
  justify-content: flex-end;
  width: 100%;
  padding: 0;
}
.alert-btn {
  margin-bottom: 15px;
}

.modal-dialog {
  position: absolute !important;
  top: 50% !important;
  left: 50% !important;
  transform: translate(-50%, -50%) !important;

  .modal-header {
    display: flex;
    justify-content: space-between;
    width: 100%;

    h5 {
      flex-grow: 1;
    }
  }
}

.file-input {
  border: 1px solid #a3aebc;
  border-radius: 4px;
  padding: 10px;

  input {
    display: none;
  }
}

.save-loader {
  max-width: 36px;
  max-height: 36px;
}
</style>
