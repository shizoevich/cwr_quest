<template>
  <div class="section-detail">
    <div class="form-group text-right">
      <form :action="downloadUrl" method="post">
        <input type="hidden" name="_token" :value="getCSRFToken()" />
        <input
          type="hidden"
          name="selected_filter_type"
          :value="formData.selectedFilterType"
        />
        <input type="hidden" name="date_from" :value="formData.dateFrom" />
        <input type="hidden" name="date_to" :value="formData.dateTo" />
        <input type="hidden" name="month" :value="formData.month" />
        <input type="hidden" name="billing_period_id" :value="formData.billingPeriodId" />
        <button type="submit" class="btn btn-primary">Download</button>
        <button
          type="button"
          class="btn btn-primary"
          data-toggle="collapse"
          :data-target="`#detail-${providerId}`"
          style="width: 92px;"
          @click="getDetails"
        >
          Detail
        </button>
      </form>
    </div>

    <br />
    <br />
    <div :id="`detail-${providerId}`" class="collapse">
      <template v-if="isLoading">
        <pageloader add-classes="details-loader" />
      </template>
      <template v-else>
        <template v-if="sortedDetails.length > 0">
          <br />
          <h4 class="text-center">Compensation for work during Current Pay Period</h4>
          <div class="table-responsive">
            <table
              class="statistic-table table table-condenced table-striped table-bordered dataTable"
            >
              <thead>
                <tr>
                  <td></td>
                  <td>OfficeAlly</td>
                  <td
                    class="is-sortable sorting-asc date-col"
                    @click="sortBy('sortedDetails', ['date'], $event)"
                    data-order="desc"
                  >
                    Visit Date
                  </td>
                  <td>POS</td>
                  <td>CPT</td>
                  <td>Insurance</td>
                  <td
                    class="is-sortable sorting-none patient-col"
                    @click="
                      sortBy(
                        'sortedDetails',
                        ['first_name', 'last_name'],
                        $event
                      )
                    "
                    data-order="asc"
                  >
                    Patient
                  </td>
                  <td>Fee</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(detail, index) in sortedDetails">
                  <td>{{ index + 1 }}</td>
                  <td>
                    <a v-if="!detail.is_created_from_timesheet"
                      :href="`https://pm.officeally.com/pm/PatientVisits/EditVisit.aspx?Tab=V&PageAction=edit&ID=${detail.visit_id}&rnum=20`"
                      target="_blank"
                      >Open</a
                    >
                    <span v-else>Added by Therapist</span>
                  </td>
                  <td>{{ getFormattedDateSimple(detail.date) }}</td>
                  <td>{{ detail.pos }}</td>
                  <td>
                    {{ detail.procedure_code }}
                    <span v-if="detail.is_telehealth" style="color: blue;"
                      >(Telehealth)</span
                    >
                  <span style="color:#E6A23C;" v-if="detail.is_overtime">(Overtime)</span>
                  </td>
                  <td>{{ detail.insurance }}</td>
                  <td>
                    <a :href="`/chart/${detail.patient_id}`" target="_blank">{{
                      detail.patient_name
                    }}</a>
                    (<a
                      :href="`https://pm.officeally.com/pm/ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID=${detail.pid}&From=ViewAppointments`"
                      target="_blank"
                      >OA</a
                    >)
                  </td>
                  <td>{{ formatFee(detail.paid_fee) }}</td>
                  <td class="text-center">
                    <form :action="syncUrl" method="post">
                      <input
                        type="hidden"
                        name="_token"
                        :value="getCSRFToken()"
                      />
                      <input type="hidden" name="sync_by" value="visit" />
                      <input type="hidden" name="sync_date" />
                      <input type="hidden" name="sync_month" />
                      <input type="hidden" name="sync_provider" />
                      <input
                        type="hidden"
                        name="sync_visit"
                        :value="detail.visit_id"
                      />
                      <button
                        type="submit"
                        class="btn btn-success"
                        :disabled="disableButtons"
                      >
                        Sync
                      </button>
                    </form>
                  </td>
                </tr>
                <tr>
                  <td colspan="9">
                    <b>Total:</b>
                  </td>
                </tr>
                <tr>
                  <td>
                    <b>{{ details ? details.length : 0 }}</b>
                  </td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>
                    <b>{{ patientsCount }}</b>
                  </td>
                  <td>
                    <b>{{ formatFee(totalFee) }}</b>
                  </td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>

        <template v-if="sortedRefundsDetails.length > 0">
          <br />
          <h4 class="text-center">Balance Payout for Visits in Previous Pay Periods</h4>
          <div class="table-responsive" :id="`missing-notes-${providerId}`">
            <table
              class="statistic-table table table-condenced table-striped table-bordered dataTable table-missing-note"
            >
              <thead>
                <tr>
                  <td class="cell-id"></td>
                  <td>OfficeAlly</td>
                  <td
                    class="is-sortable sorting-none date-col"
                    data-order="asc"
                    @click="sortBy('sortedRefundsDetails', ['visit_date'], $event)"
                  >
                    Visit Date
                  </td>
                  <td
                    class="is-sortable sorting-asc date-col"
                    data-order="desc"
                    style="padding-right:25px"
                    @click="sortBy('sortedRefundsDetails', ['date'], $event)"
                  >
                    PN / IA Created At
                  </td>
                  <td>POS</td>
                  <td>CPT</td>
                  <td>Insurance</td>
                  <td
                    class="is-sortable sorting-none patient-col"
                    data-order="asc"
                    @click="
                      sortBy(
                        'sortedRefundsDetails',
                        ['first_name', 'last_name'],
                        $event
                      )
                    "
                  >
                    Patient
                  </td>
                  <td class="full-amount-col">Fee</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(detail, index) in sortedRefundsDetails">
                  <td class="cell-id">{{ index + 1 }}</td>
                  <td>
                    <a
                        v-if="!detail.is_created_from_timesheet"
                        :href="`https://pm.officeally.com/pm/PatientVisits/EditVisit.aspx?Tab=V&PageAction=edit&ID=${detail.visit_id}&rnum=20`"
                    >
                        Open
                    </a>
                    <span v-else>Added by Therapist</span>
                  </td>
                  <td>{{ getFormattedDateSimple(detail.visit_date) }}</td>
                  <td>{{ getFormattedDateSimple(detail.date) }}</td>
                  <td>{{ detail.pos }}</td>
                  <td>
                    {{ detail.procedure_code }}
                    <span v-if="detail.is_telehealth" style="color: blue;">(Telehealth)</span>
                    <span style="color:#E6A23C;" v-if="detail.is_overtime">(Overtime)</span>
                  </td>
                  <td>{{ detail.insurance }}</td>
                  <td>
                    <a :href="`/chart/${detail.patient_id}`" target="_blank">
                      {{ detail.patient_name }}
                    </a>
                    (<a
                      :href="`https://pm.officeally.com/pm/ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID=${detail.pid}&From=ViewAppointments`"
                    >OA</a>)
                  </td>
                  <td>{{ formatFee(detail.fee) }}</td>
                  <td class="text-center">
                    <form :action="syncUrl" method="post">
                      <input
                        type="hidden"
                        name="_token"
                        :value="getCSRFToken()"
                      />
                      <input type="hidden" name="sync_by" value="visit" />
                      <input type="hidden" name="sync_date" />
                      <input type="hidden" name="sync_month" />
                      <input type="hidden" name="sync_provider" />
                      <input
                        type="hidden"
                        name="sync_visit"
                        :value="detail.visit_id"
                      />
                      <button
                        type="submit"
                        class="btn btn-success"
                        :disabled="disableButtons"
                      >
                        Sync
                      </button>
                    </form>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="10">
                    <b>Total:</b>
                  </td>
                </tr>
                <tr>
                  <td>
                    <b>{{ refundsDetails ? refundsDetails.length : 0 }}</b>
                  </td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>
                    <b>{{ refundPatientsCount }}</b>
                  </td>
                  <td>
                    <b>{{ formatFee(refundTotal) }}</b>
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </template>

        <template v-if="sortedPnDetails.length > 0">
          <br />
          <h4 class="text-center">Compensation for Visits with Missing Progress Notes / Initial Assessments (Current Pay Period)</h4>
          <div class="table-responsive" :id="`missing-notes-${providerId}`">
            <table
              class="statistic-table table table-condenced table-striped table-bordered dataTable table-missing-note"
            >
              <thead>
                <tr>
                  <td class="cell-id"></td>
                  <td>OfficeAlly</td>
                  <td
                    class="is-sortable sorting-asc date-col"
                    @click="sortBy('sortedPnDetails', ['date'], $event)"
                    data-order="desc"
                  >
                    Visit Date
                  </td>
                  <td>POS</td>
                  <td>CPT</td>
                  <td>Insurance</td>
                  <td
                    class="is-sortable sorting-none patient-col"
                    @click="
                      sortBy(
                        'sortedPnDetails',
                        ['first_name', 'last_name'],
                        $event
                      )
                    "
                    data-order="asc"
                  >
                    Patient
                  </td>
                  <td class="full-amount-col">Full Amount</td>
                  <td class="partial-col">Partial (w/o PN)</td>
                  <td>Balance</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(pnDetail, index) in sortedPnDetails">
                  <td class="cell-id">{{ index + 1 }}</td>
                  <td>
                    <a v-if="!pnDetail.is_created_from_timesheet"
                      :href="`https://pm.officeally.com/pm/PatientVisits/EditVisit.aspx?Tab=V&PageAction=edit&ID=${pnDetail.visit_id}&rnum=20`"
                      >Open</a
                    >
                      <span v-else>Added by Therapist</span>

                  </td>
                  <td>{{ getFormattedDateSimple(pnDetail.date) }}</td>
                  <td>{{ pnDetail.pos }}</td>
                  <td>
                    {{ pnDetail.procedure_code }}
                    <span v-if="pnDetail.is_telehealth" style="color: blue;"
                      >(Telehealth)</span>
                      <span style="color:#E6A23C;" v-if="pnDetail.is_overtime">(Overtime)</span>
                  </td>
                  <td>{{ pnDetail.insurance }}</td>
                  <td>
                    <a :href="`/chart/${pnDetail.patient_id}`" target="_blank">
                      {{ pnDetail.patient_name }}
                    </a>
                    (<a
                      :href="`https://pm.officeally.com/pm/ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID=${pnDetail.external_patient_id}&From=ViewAppointments`"
                      >OA</a
                    >)
                  </td>
                  <td>{{ formatFee(pnDetail.fee) }}</td>
                  <td>{{ formatFee(pnDetail.paid_fee) }}</td>
                  <td>{{ formatFee(pnDetail.fee - pnDetail.paid_fee) }}</td>
                  <td class="text-center">
                    <form :action="syncUrl" method="post">
                      <input
                        type="hidden"
                        name="_token"
                        :value="getCSRFToken()"
                      />
                      <input type="hidden" name="sync_by" value="visit" />
                      <input type="hidden" name="sync_date" />
                      <input type="hidden" name="sync_month" />
                      <input type="hidden" name="sync_provider" />
                      <input
                        type="hidden"
                        name="sync_visit"
                        :value="pnDetail.visit_id"
                      />
                      <button
                        type="submit"
                        class="btn btn-success"
                        :disabled="disableButtons"
                      >
                        Sync
                      </button>
                    </form>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="11">
                    <b>Total:</b>
                  </td>
                </tr>
                <tr>
                  <td>
                    <b>{{
                      progressNoteDetails ? progressNoteDetails.length : 0
                    }}</b>
                  </td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td>
                    <b>{{ pnPatientsCount }}</b>
                  </td>
                  <td>
                    <b>{{ formatFee(pnTotalFee) }}</b>
                  </td>
                  <td>
                    <b>{{ formatFee(pnTotalFeeMissing) }}</b>
                  </td>
                  <td>
                    <b>{{ formatFee(pnTotalBalance) }}</b>
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </template>

        <template v-if="sortedLateCancellationDetails.length > 0">
          <br />
          <h4 class="text-center">Late Appt. Cancellations</h4>
          <div class="table-responsive" :id="`late-cancellations-${providerId}`">
            <table
              class="statistic-table table table-condenced table-striped table-bordered dataTable table-missing-note"
            >
              <thead>
              <tr>
                <td class="cell-id"></td>
                <td
                  class="is-sortable sorting-asc date-col"
                  @click="sortBy('sortedLateCancellationDetails', ['visit_date'], $event)"
                  data-order="desc"
                >
                  Visit Date
                </td>
                <td
                  class="is-sortable date-col sorting-none"
                  @click="sortBy('sortedLateCancellationDetails', ['date'], $event)"
                  data-order="desc"
                >
                  Fee Collected At
                </td>
                <td>Patient</td>
                <td class="full-amount-col">Collected Fee</td>
                <td class="partial-col">Paid Fee</td>
              </tr>
              </thead>
              <tbody>
              <tr v-for="(lcDetail, index) in sortedLateCancellationDetails">
                <td class="cell-id">{{ index + 1 }}</td>

                <td>{{ getFormattedDateSimple(lcDetail.visit_date) }}</td>
                <td>
                  {{ getFormattedDateSimple(lcDetail.date) }}
                  <span style="color:#E6A23C;" v-if="lcDetail.is_custom_created">(Added by Therapist)</span>
                </td>
                <td>
                  <a :href="`/chart/${lcDetail.patient_id}`" target="_blank">
                    {{ lcDetail.patient_name }}
                  </a>
                  (<a :href="`https://pm.officeally.com/pm/ManagePatients/EditPatient.aspx?Tab=P&PageAction=edit&PID=${lcDetail.external_patient_id}&From=ViewAppointments`">OA</a>)
                </td>
                <td>{{ formatFee(lcDetail.collected_fee) }}</td>
                <td>{{ formatFee(lcDetail.paid_fee) }}</td>
              </tr>
              </tbody>
              <tfoot>
              <tr>
                <td colspan="6">
                  <b>Total:</b>
                </td>
              </tr>
              <tr>
                <td>
                  <b>{{
                      lateCancellationDetails ? lateCancellationDetails.length : 0
                    }}</b>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                  <b>{{ formatFee(lcTotalPaidFee) }}</b>
                </td>
              </tr>
              </tfoot>
            </table>
          </div>
        </template>

        <template v-if="additionalCompensation.length > 0">
          <br />
          <h4 class="text-center">Additional Compensation</h4>
          <div class="table-responsive" :id="`additional-compensation-${providerId}`">
            <table
              class="statistic-table table table-condenced table-striped table-bordered dataTable table-missing-note"
            >
              <thead>
                <tr>
                  <td></td>
                  <td># of Visits</td>
                  <td>Amount</td>
                  <td>Additional Comments</td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in additionalCompensation">
                  <td>{{ item.title }}</td>
                  <td>
                    <template v-if="item.additional_data">
                      {{ item.additional_data.visit_count }}
                    </template>
                  </td>
                  <td>{{ formatFee(item.paid_fee) }}</td>
                  <td>{{ item.notes }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4">
                    <b>Total:</b>
                  </td>
                </tr>
                <tr>
                  <td></td>
                  <td></td>
                  <td><b>{{ formatFee(additionalCompensationTotalFee) }}</b></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </template>
      </template>
      <br />
      <br />
    </div>
  </div>
</template>

<script>
import datetimeFormatted from "../../../mixins/datetime-formated";
import patientBalance from "../../../mixins/patient-balance";

export default {
  name: "Detail",
  mixins: [datetimeFormatted, patientBalance],
  props: {
    downloadUrl: {
      type: String,
    },
    syncUrl: {
      type: String,
    },
    formData: {
      type: Object,
      required: true,
    },
    providerId: {
      type: [String, Number],
      required: true,
    },
    isParserRunning: {
      type: [Boolean, Number],
    },
  },
  data: () => ({
    details: null,
    isLoading: true,
    progressNoteDetails: null,
    refundsDetails: null,
    lateCancellationDetails: null,
    sortedDetails: null,
    sortedPnDetails: null,
    sortedRefundsDetails: null,
    sortedLateCancellationDetails: null,
    additionalCompensation: null
  }),
  computed: {
    disableButtons() {
      if (typeof this.isParserRunning === "number") {
        return this.isParserRunning === 1;
      } else {
        return this.isParserRunning;
      }
    },
    patientsCount() {
      if (!this.details || (this.details && this.details.length < 1)) {
        return 0;
      }

      let patientIds = this.details.map((item) => item.patient_id);
      let uniqueIds = _.uniq(patientIds);
      return uniqueIds.length;
    },
    totalFee() {
      let fee = 0;
      if (!this.details || (this.details && this.details.length < 1)) {
        return fee;
      }

      let feeList = this.details.map((item) => item.paid_fee);
      return feeList.reduce((a, b) => a + b, 0);
    },
    additionalCompensationTotalFee() {
      let fee = 0;
      if (!this.additionalCompensation || (this.additionalCompensation && this.additionalCompensation.length < 1)) {
        return fee;
      }

      let feeList = this.additionalCompensation.map((item) => item.paid_fee);
      return feeList.reduce((a, b) => a + b, 0);
    },
    pnPatientsCount() {
      if (
        !this.progressNoteDetails ||
        (this.progressNoteDetails && this.progressNoteDetails.length < 1)
      ) {
        return 0;
      }

      let patientIds = this.progressNoteDetails.map((item) => item.patient_id);
      let uniqueIds = _.uniq(patientIds);
      return uniqueIds.length;
    },
    pnTotalFee() {
      let fee = 0;
      if (
        !this.progressNoteDetails ||
        (this.progressNoteDetails && this.progressNoteDetails.length < 1)
      ) {
        return fee;
      }

      let feeList = this.progressNoteDetails.map((item) => item.fee);
      return feeList.reduce((a, b) => a + b, 0);
    },
    lcTotalPaidFee() {
      let fee = 0;
      if (
        !this.lateCancellationDetails ||
        (this.lateCancellationDetails && this.lateCancellationDetails.length < 1)
      ) {
        return fee;
      }

      let feeList = this.lateCancellationDetails.map((item) => item.paid_fee);
      return feeList.reduce((a, b) => a + b, 0);
    },
    pnTotalFeeMissing() {
      let fee = 0;
      if (
        !this.progressNoteDetails ||
        (this.progressNoteDetails && this.progressNoteDetails.length < 1)
      ) {
        return fee;
      }

      let feeList = this.progressNoteDetails.map((item) => item.paid_fee);
      return feeList.reduce((a, b) => a + b, 0);
    },
    pnTotalBalance() {
      return this.pnTotalFee - this.pnTotalFeeMissing;
    },
    refundPatientsCount() {
      if (
        !this.refundsDetails ||
        (this.refundsDetails && this.refundsDetails.length < 1)
      ) {
        return 0;
      }

      let patientIds = this.refundsDetails.map((item) => item.patient_id);
      let uniqueIds = _.uniq(patientIds);
      return uniqueIds.length;
    },
    refundTotal() {
      let fee = 0;
      if (
        !this.refundsDetails ||
        (this.refundsDetails && this.refundsDetails.length < 1)
      ) {
        return fee;
      }
      let feeList = this.refundsDetails.map((item) => item.fee);
      return feeList.reduce((a, b) => a + b, 0);
    },
  },
  methods: {
    getCSRFToken() {
      return document.head.querySelector('meta[name="csrf-token"]').content;
    },

    getDetails() {
      if (!this.details) {
        this.isLoading = true;
        this.$store
          .dispatch("getProviderSalaryDetail", {
            id: this.providerId,
            query: window.location.search,
          })
          .then((response) => {
            this.details = response.data.details;
            this.progressNoteDetails = response.data.progress_note_details;
            this.refundsDetails =
              response.data.refunds_for_progress_note_details;
            this.lateCancellationDetails = response.data.late_cancellations;
            this.additionalCompensation = response.data.additional_compensation || [];
            this.sortedDetails = _.orderBy(this.details, ["date"], ["asc"]);
            this.sortedPnDetails = _.orderBy(
              this.progressNoteDetails,
              ["date"],
              ["asc"]
            );
            this.sortedRefundsDetails = _.orderBy(
              this.refundsDetails,
              ["date"],
              ["asc"]
            );
            this.sortedLateCancellationDetails = _.orderBy(
              this.lateCancellationDetails,
              ["visit_date"],
              ["asc"]
            );
          })
          .finally(() => {
            this.isLoading = false;
          });
      }
    },

    formatFee(fee) {
      return this.getLocaleFormattedBalance({
        amount: fee,
        options: { minimumFractionDigits: 2 },
      });
    },

    sortBy(dataKey, fields, event) {
      this.clearSort(event.target);
      let order = event.target.dataset.order;
      let orderList = [];
      for (let field in fields) {
        orderList.push(order);
      }
      let defaultDataKey = "";
      switch (dataKey) {
        case "sortedDetails":
          defaultDataKey = "details";
          break;
        case "sortedPnDetails":
          defaultDataKey = "progressNoteDetails";
          break;
        case "sortedRefundsDetails":
          defaultDataKey = "refundsDetails";
          break;
      case "sortedLateCancellationDetails":
          defaultDataKey = "lateCancellationDetails";
          break;
      }
      if (order === "asc" || order === "desc") {
        this[dataKey] = _.orderBy(this[defaultDataKey], fields, orderList);
        event.target.dataset.order = order === "asc" ? "desc" : "asc";
        event.target.classList.remove("sorting-none");
        event.target.classList.add(
          order === "asc" ? "sorting-asc" : "sorting-desc"
        );
      }
    },

    clearSort(target) {
      let row = target.parentElement;
      let cols = row.querySelectorAll(".is-sortable");
      for (let col of cols) {
        col.classList.remove("sorting-asc");
        col.classList.remove("sorting-desc");
        col.classList.add("sorting-none");
      }
    },
  },
  mounted() {
    // if (document.querySelector(`#missing-notes-${this.providerId}`)) {
    //   $(`#missing-notes-${this.providerId}`).dataTable();
    // }
  },
};
</script>

<style scoped></style>
