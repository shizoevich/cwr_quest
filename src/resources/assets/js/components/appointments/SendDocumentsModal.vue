<template>
  <div
    id="sendDocuments"
    class="modal modal-vertical-center fade"
    data-backdrop="static"
    data-keyboard="false"
    tabindex="-1"
    role="dialog"
  >
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" @click="close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Request for documents from {{ patient.first_name }} {{  patient.last_name }}</h4>
        </div>
        <form method="post" @submit.prevent="validate">
          <div class="modal-body">
            <p>Are you sure you have selected all required forms and supporting documents to be included in this request for signature?</p>
            <p>Forms and documents selected to be sent:</p>
            <requested-documents compact :patient-forms="selectedForms" />
            <div
              v-if="method === 'email'"
              class="form-group align-center form-control-wrapper"
              :class="{ 'has-error': errors.has('email') }"
            >
              <label for="sendDocumentEmail">
                Enter the email address:
              </label>
              <input
                v-model.trim="email"
                v-validate.disable="'required|email'"
                data-vv-validate-on=""
                id="sendDocumentEmail"
                name="email"
                class="form-control"
                :disabled="isSending"
              />
              <span class="help-block">{{ errors.first("email") }}</span>
            </div>
            <div
              v-if="method === 'phone'"
              class="form-group align-center form-control-wrapper"
              :class="{ 'has-error': errors.has('phone') }"
            >
              <label for="sendDocumentPhone">
                Enter the phone number:
              </label>
              <the-mask
                :disabled="isSending"
                autocomplete="new-password"
                id="sendDocumentPhone"
                name="phone"
                :class="{ 'input-error': errors.has('phone') }"
                class="form-control empty-input"
                mask="(###)-###-####"
                :masked="true"
                v-validate="'required|min:14'"
                v-model="phoneTo"
                @keydown.enter.prevent
              ></the-mask>
              <span class="help-block">{{ errors.first("phone") }}</span>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary" :disabled="isSending">
              Send
            </button>
            <button
              class="btn btn-default"
              data-dismiss="modal"
              @click.prevent="close"
              :disabled="isSending"
            >
              Close
            </button>
          </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.modal -->
</template>

<script>
import RequestedDocuments from "../documents/partials/RequestedDocuments";
import PhoneFormatted from "../../mixins/phone-format";

export default {
  name: "SendDocumentsModal",
  components: {
    RequestedDocuments,
  },
  mixins: [PhoneFormatted],
  props: {
    isSending: {
      type: Boolean,
    },
    sendTo: {
      type: String,
    },
    selectedForms: {
      type: Array,
      required: true,
    },
    patient: {
      type: Object,
      required: true,
    },
    method: {
      type: String,
      default: "email",
    },
    phone: {
      type: String,
    },
    errorMessages: {
      type: Object,
    },
  },
  data: () => ({
    email: "",
    phoneTo: "",
    isValid: true,
  }),
  mounted() {
    this.email = this.sendTo;
    this.phoneTo = this.phone;
    this.errors.clear();
  },
  computed: {
    phoneToFormatted() {
      return this.getUsFormat(this.phoneTo);
    },
  },
  methods: {
    submit() {
      this.$emit("submit", {
        method: this.method,
        email: this.email,
        phone: this.phoneToFormatted,
      });
    },
    close() {
      this.$emit("close");
    },
    validate() {
      this.errors.clear();
      this.$validator.validateAll().then((valid) => {
        console.log(this.errors);
        if (valid) {
          this.submit();
        }
      });
    },
  },
  watch: {
    sendTo() {
      this.email = this.sendTo;
    },
    phone() {
      this.phoneTo = this.phone;
    },
    errorMessages: {
      handler: function (val) {
        let errors = [];
        for (let key in val) {
          errors.push({ field: key, msg: val[key] });
        }
        for (let error of errors) {
          this.errors.add(error);
        }
      },
      deep: true,
    },
  },
};
</script>

<style lang="scss" scoped>
.form-group {
  margin-bottom: 15px;
}

.form-control-wrapper {
  position: relative;

  .help-block {
    font-size: 11px;
    // position: absolute;
  }
}
</style>
