<template>
  <div>
    <label>History of requests and submissions</label>

    <div class="table-responsive">
      <table class="table table-logs">
        <thead>
          <tr>
            <th>Date Sent</th>
            <th>Date Signed</th>
            <th>Sent By</th>
            <th>Email / Phone Used</th>
            <th v-if="isPaymentForServiceForm">Comment</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <template v-if="logs.length === 0">
            <tr>
              <td colspan="5" class="text-center">
                No records
              </td>
            </tr>
          </template>
          <template v-else>
            <tr :key="`request-${index}`" v-for="(log, index) in logs">
              <td class="event-failure" v-if="log.rejected_at
                || log.bounced_at
                || log.soft_bounced_at
                || log.spam_at
                || log.unsub_at
                || log.deferral_at
                || log.hard_bounced_at"
              >
                <div v-if="log.rejected_at">
                    <span class="event-failure"  data-toggle="tooltip" data-placement="top" title="Rejected">{{ getFormattedDateTime(log.rejected_at)}}</span>
                </div>
                <div v-else-if="log.bounced_at">
                    <span class="event-failure" data-toggle="tooltip" data-placement="top" title="Bounced">{{ getFormattedDateTime(log.bounced_at)}}</span>
                </div>
                <div v-else-if="log.soft_bounced_at">
                    <span class="event-failure"  data-toggle="tooltip" data-placement="top" title="Soft-Bounced">{{ getFormattedDateTime(log.soft_bounced_at)}}</span>
                </div>
                <div v-else-if="log.spam_at">
                    <span class="event-failure"  data-toggle="tooltip" data-placement="top" title="Spam">{{ getFormattedDateTime(log.spam_at)}}</span>
                </div>
                <div v-else-if="log.unsub_at">
                    <span class="event-failure"  data-toggle="tooltip" data-placement="top" title="Unsub">{{ getFormattedDateTime(log.unsub_at)}}</span>
                </div>
                <div v-else-if="log.deferral_at">
                    <span class="event-failure"  data-toggle="tooltip" data-placement="top" title="Deferral">{{ getFormattedDateTime(log.deferral_at)}}</span>
                </div>
                <div v-else>
                    <span class="event-failure"  data-toggle="tooltip" data-placement="top" title="Hard-Bounced">{{ getFormattedDateTime(log.hard_bounced_at)}}</span>
                </div>
              </td>
              <td :class="{ empty: !log.sent_at }" v-else>
                <span class="event-succeed">{{ log.sent_at ? getFormattedDateTime(log.sent_at) : "" }}</span>
                <el-tooltip v-if="isPaymentForServiceForm" effect="dark" placement="bottom">
                  <template #content>
                    <span v-html="getPaymentForServiceTooltipText(log)" />
                  </template>
                  <help />
                </el-tooltip>
              </td>
              <td :class="{ empty: !log.filled_at }">
                {{ log.filled_at ? getFormattedDateTime(log.filled_at) : "" }}
              </td>
              <td :class="{ empty: !log.send_by }">
                {{ log.send_by }}
              </td>
              <td :class="{ empty: !log.sent_to_email && !log.sent_to_phone }">
                {{ log.sent_to_email }}
                {{ log.sent_to_phone }}
              </td>
              <td v-if="isPaymentForServiceForm">
                {{ getPaymentForServiceComment(log) || '-' }}
              </td>
              <td class="td-actions">
                <div
                  class="buttons"
                  :class="{ disabled: log.filled_at || log.documents.length < 1 }"
                >
                  <button
                    class="btn btn-default btn-table-action"
                    :disabled="log.documents.length < 1"
                    @click="downloadDocuments(log.documents)"
                  >
                    <i class="fa fa-download"></i>
                  </button>
                  <button
                    class="btn btn-default btn-table-action"
                    @click="printData(log.documents.map((doc) => doc.id))"
                    :disabled="log.documents.length < 1"
                  >
                    <i class="fa fa-print"></i>
                  </button>
  <!--                <button-->
  <!--                  class="btn btn-default btn-table-action"-->
  <!--                  :disabled="!patient.email || log.documents.length < 1"-->
  <!--                  @click="openSendToEmailModal(log.documents)"-->
  <!--                >-->
  <!--                  <i class="fa fa-envelope"></i>-->
  <!--                </button>-->
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import dateTimeFormatter from "./../mixins/datetime-formated";
import printJS from "print-js";
import { PAYMENT_FOR_SERVICE_FORM_NAME, PAYMENT_FOR_SERVICE_FORM_TYPE_ID } from '../settings';

export default {
  name: "TableLogs",
  mixins: [dateTimeFormatter],
  props: {
    logs: {
      type: Array,
    },
    patient: {
      validator: (prop) => typeof prop === "object" || prop === null,
      required: true,
    },
    formName: {
      type: String,
      required: true,
    },
  },
  computed: {
    isPaymentForServiceForm() {
      return this.formName === PAYMENT_FOR_SERVICE_FORM_NAME;
    },
    provider() {
        return this.$store.state.currentProvider;
    },
    isUserAdmin() {
        return this.$store.state.isUserAdmin;
    },
    showDeductible() {
        return this.isUserAdmin || (this.provider && this.provider.is_collect_payment_available);
    },
  },
  data: () => ({
    isSending: false,
    documentsForSend: null,
  }),
  methods: {
    printItem(item) {
      return new Promise((resolve) => {
        printJS({
          printable: item,
          type: "pdf",
          base64: true,
          onPrintDialogClose: () => {
            resolve(item);
          },
        });
      });
    },
    printData(documents) {
      let documentsId = documents;
      let docId = documentsId.pop();
      if (docId) {
        this.getBase64Document(docId).then((response) => {
          let documentBase64 = response.data.document;
          this.printItem(documentBase64).then(() => {
            this.printData(documentsId);
          });
        });
      }
    },
    openSendToEmailModal(documents) {
      this.documentsForSend = documents.map((doc) => doc.id);
      let data = {
        email: this.patient.email,
        documents: _.clone(this.documentsForSend),
      };
      let formData = {
        patientId: this.patient.id,
        data,
      };
      this.$emit("open-send-modal", formData);
      this.documentsForSend = null;
      $("#sendToEmail").modal("show");
    },
    downloadDocuments(documents) {
      let data = documents.map((doc) => doc.id);
      this.$store
        .dispatch("downloadPatientDocuments", {
          patientId: this.patient.id,
          data,
        })
        .then((response) => {
          let file = new Blob([response.data]);
          let filename = "";
          let url = window.URL.createObjectURL(file);
          let link = document.createElement("a");

          if (data.length === 1) {
            filename = documents.find((doc) => data[0] === doc.id)
              .original_document_name;
          } else {
            filename = "documents.zip";
          }

          link.href = url;
          link.setAttribute("download", filename);
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
        });
    },
    getBase64Document(id) {
      return this.$store.dispatch("getBase64PatientDocument", {
        patientId: this.patient.id,
        documentId: id,
      });
    },
    getTooltipValue(value) {
      return value !== undefined ? value : "-";
    },
    getPaymentForServiceTooltipText(log) {
      const paymentForServiceRequestItem = log.items.find(item => item.form_type_id === PAYMENT_FOR_SERVICE_FORM_TYPE_ID);

      if (!paymentForServiceRequestItem) {
        return null;
      }

      const {co_pay, payment_for_session_not_converted, self_pay, charge_for_cancellation, other_charges_price, other_charges} = paymentForServiceRequestItem.metadata;

      return `<span>Co-pay and/or co-insurance for session: ${ this.getTooltipValue(co_pay) }</span><br/>
              ${ this.showDeductible ? ('<span>Payment for session not covered due to deductible: ' + this.getTooltipValue(payment_for_session_not_converted) + '</span><br/>') : '' }
              <span>Self-pay for session when paid out-of-pocket: ${ this.getTooltipValue(self_pay) }</span><br/>
              <span>Charge for cancellation without 24 hours’ notice: ${ this.getTooltipValue(charge_for_cancellation) }</span><br/>
              <span>Other charges (price): ${ this.getTooltipValue(other_charges_price) }</span><br/>
              <span>Other charges (comment): ${ this.getTooltipValue(other_charges) }</span>`
    },
    getPaymentForServiceComment(log) {
      const paymentForServiceRequestItem = log.items.find(item => item.form_type_id === PAYMENT_FOR_SERVICE_FORM_TYPE_ID);

      if (!paymentForServiceRequestItem) {
        return null;
      }

      return paymentForServiceRequestItem.comment;
    }
  },
  mounted() {
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
  },
  
};
</script>

<style scoped>
.event-succeed{
 color: #12cb43;
}
.event-failure{
  color: #ff0000;
}
</style>
