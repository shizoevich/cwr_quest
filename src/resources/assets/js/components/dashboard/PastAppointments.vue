<template>
  <div class="wrapper">
    <div class="container">
      <div
        class="page-loader-wrapper kaiser-appointments text-center"
        v-if="loading"
      >
        <pageloader add-classes="page-loader" />
      </div>
      <template v-else>
        <div class="alert alert-danger alert-ehr" v-if="appointmentsCount > 0">
          <h4 class="alert-ehr__title">
            <strong>EHR SYSTEM ALERT</strong>: Appointment Status Change Request
          </h4>
          <p class="alert-ehr__text">
            <strong>ATTENTION</strong>! Please read the urgent message from CWR
            Administrator below and take appropriate actions to resolve some
            issues. Please note, you will not be able to access the EHR system
            until these issues are resolved.
          </p>
          <p class="alert-ehr__text">Hello {{ provider.provider_name }}</p>
          <p class="alert-ehr__text">
            You have {{ appointmentsCount }} appointments that require your
            immediate attention. Please review the list below and change the
            status of appointments scheduled in the past. If you have any
            questions please call CWR administrator at
            <a href="tel:+2139081234">(213) 908-1234</a>.
          </p>
          <p class="alert-ehr__text">
            Thank you,
          </p>
          <p class="alert-ehr__text">
            The CWR Admin Team
          </p>
        </div>
        <template v-if="appointments && appointments.length !== 0">
          <div class="panel">
            <div class="panel-body">
              <div
                v-for="(dailyAppointments, index) in appointments"
                :key="index"
              >
                <h3 class="text-center">
                  {{ formatDate(index) }}
                </h3>
                <table
                  class="table table-striped kaiser-appointments-table"
                  :id="`kaiser-appointments-table-${index}`"
                  data-datatable="true"
                >
                  <thead>
                    <tr>
                      <th>Patient Name</th>
                      <th>Time</th>
                      <th>Reason</th>
                      <th class="cell-actions">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="appointment in dailyAppointments"
                      :key="appointment.id"
                    >
                      <td>
                        <p
                          v-if="is_redirect"
                          :style="`color: #${appointment.hex_color}`"
                        >
                          {{ appointment.first_name }}
                          {{ appointment.last_name }}
                        </p>
                        <a
                          class="patient-name"
                          :href="`/chart/${appointment.patients_id}`"
                          :style="`color: #${appointment.hex_color}`"
                          target="_blank"
                          v-else
                          >{{ appointment.first_name }}
                          {{ appointment.last_name }}</a
                        >
                      </td>
                      <td>
                        {{ getFormattedTime(appointment.time * 1000) }}
                      </td>
                      <td>
                        {{ appointment.reason_for_visit }}
                      </td>
                      <td class="appt-buttons-container">
                        <span
                          class="patient-list-btn patient-list-btn-success"
                          @click.prevent="setCompleteStatus(appointment)"
                        >
                          <i class="fa fa-check"></i>
                        </span>
                        <span class="patient-list-btn patient-list-btn-warning"
                            @click.prevent="setRescheduleStatus(appointment)"
                        >
                            <i class="fa fa-refresh"></i>
                        </span>
                        <span
                          class="patient-list-btn patient-list-btn-danger"
                          @click.prevent="setCancelStatus(appointment)"
                        >
                          <i class="fa fa-times"></i>
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- <div v-if="appointments && appointments.length == 0">
              <h2 class="text-center">No results</h2>
            </div> -->
            </div>
          </div>
        </template>
      </template>
      <complete-appointment
        :is-telehealth="isTelehealth"
        @completed="getPastAppointments"
      />
      <confirm-telehealth @telehealth-confirmed="setTelehealth" />
      <reschedule-appointment @rescheduled="getPastAppointments" />
      <cancel-appointment @canceled="getPastAppointments" />
      <no-past-appointments />
    </div>
  </div>
</template>

<script>
import DatetimeFormated from "../../mixins/datetime-formated";

export default {
  mixins: [DatetimeFormated],
  data: () => ({
    appointments: null,
    is_redirect: false,
    loading: false,
    isTelehealth: false,
    provider: null,
  }),
  methods: {
    setTelehealth(status) {
      this.isTelehealth = status;
      window.setTimeout(function() {
        $("#complete-appointment").modal("show");
      }, 500);
    },
    formatDate(dateItem) {
      return moment(dateItem).format("MM/DD/YYYY");
    },
    getPastAppointments() {
      this.loading = true;
      this.$store
        .dispatch("getPastAppointments")
        .then((response) => {
          this.appointments = response.data.appointments;
          this.is_redirect = response.data.is_redirect;
          this.provider = response.data.provider;
          if (this.appointments.length === 0) {
            this.loading = false;
            $("#no-past-appointments").modal("show");
          }
        })
        .finally(() => {
          this.loading = false;
        });
    },
    setCompleteStatus(appointment) {
      if (this.sidebar_loading_today_block) {
        return false;
      }
      this.$store
        .dispatch("getCompleteAppointmentData", {
          appointment_id: appointment.id,
          patient_id: appointment.patients_id,
          action: 'complete'
        })
        .then((response) => {
          if (response.status === 200) {
            $("#confirm-telehealth").modal("show");
          }
        });
    },

    setRescheduleStatus(appointment) {
      if (this.sidebar_loading_today_block) {
        return false;
      }
      this.$store
        .dispatch("getCompleteAppointmentData", {
          appointment_id: appointment.id,
          patient_id: appointment.patients_id,
          action: 'reschedule'
        })
        .then((response) => {
          if (response.status === 200) {
            $("#reschedule-appointment").modal("show");
          }
        });
    },

    setCancelStatus(appointment) {
      if (this.sidebar_loading_today_block) {
        return false;
      }
      this.$store
        .dispatch("getCompleteAppointmentData", {
          appointment_id: appointment.id,
          patient_id: appointment.patients_id,
          action: 'cancel'
        })
        .then((response) => {
          if (response.status === 200) {
            $("#cancel-appointment").modal("show");
          }
        });
    },
  },
  computed: {
    appointmentsCount() {
      if (this.appointments) {
        let count = 0;
        for (let listKey in this.appointments) {
          count += this.appointments[listKey].length;
        }

        return count;
      }

      return 0;
    },
  },
  beforeMount() {
    this.getPastAppointments();
    this.$store.dispatch("getOtherCancelAppointmentStatuses");
  },
};
</script>

<style scoped lang="scss">
.cell-actions {
  width: 50px;
}

td .patient-name {
  &.success {
    color: #51ad58;
  }

  &.danger {
    color: #f2443d;
  }
}
</style>
