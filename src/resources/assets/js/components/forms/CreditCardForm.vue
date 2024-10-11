<template>
  <div class="patient-info-form credit-card-form px-0" v-if="form_data">
    <div class="patient-contact-info-container">
      <div class="section section-add-note">
        <div class="inp-group">
          <square-payment-form-optimized
            v-if="!hidden"
            ref="square_form"
            :postalCode="zip"
            :patientEmail="email"
            :withEmailField="true"
            cardContainerId="ccr-form-card-container"
            @postal-code-changed="setPostalCode"
            @patient-email-changed="setEmail"
            @card-nonce-received="storeCreditCard"
            @validation-fails="emitValidationFails"
          ></square-payment-form-optimized>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    hidden: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Object,
      required: true,
    },
    patientEmail: {
      type: String,
      required: true,
    }
  },
  data: () => ({
    form_data: null,
    email: '',
    zip: '',
  }),
  computed: {
    patient() {
      return this.$store.state.currentPatient;
    },
  },
  watch: {
    patientEmail(value) {
      this.setEmail(value);
    }
  },
  mounted() {
    this.form_data = this.data;
    this.setEmail(this.patientEmail);
  },
  methods: {
    emitValidationFails(message) {
      this.$emit('validation-fails', message);
    },

    setPostalCode(zip) {
      this.zip = zip;
    },

    setEmail(email) {
      this.email = email;
    },

    storeCreditCard(creditCardData) {
      this.form_data.card_data = {
        last_four: creditCardData.details && creditCardData.details.card ? creditCardData.details.card.last4 : '',
        exp_month: creditCardData.details && creditCardData.details.card ? creditCardData.details.card.expMonth : '',
        exp_year: creditCardData.details && creditCardData.details.card ? creditCardData.details.card.expYear : '',
        zip_code: creditCardData.details && creditCardData.details.billing ? creditCardData.details.billing.postalCode : '',
        brand: creditCardData.details && creditCardData.details.card ? creditCardData.details.card.brand : '',
      };

      this.$emit('add-loader');
      this.$store.dispatch('safeStoreCreditCard', {
        hash: this.$route.params.hash,
        patient_id: this.patient.id,
        data: {
          nonce: creditCardData.token,
          email: this.email,
          zip: this.zip,
        },
      }).then(response => {
        if(!response.data.success) {
          this.emitValidationFails(response.data.message);
        } else {
          this.$emit("validation-success");
        }
      }).catch(error => {
        if(error.response.data.errors && error.response.data.errors[0]) {
          this.emitValidationFails(error.response.data.errors[0].detail);
        }
      }).finally(() => {
        this.$emit('remove-loader');
      });
    },

    validateForm() {
      this.$validator.validateAll().then((valid) => {
        if (valid) {
          this.$refs.square_form.requestCardNonce();
        } else {
          this.emitValidationFails("Please make sure you have filled all the required fields.");
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
  .px-0 {
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
  .section {
    padding: 0;
  }
</style>