<template>
  <div class="container patient-info-form credit-card-form" v-if="form_data">
    <div class="patient-contact-info-container">
      <div class="section section-add-note">
        <div class="inp-group">
          <div class="row mb-credit">
            <p class="col-xs-12 col-sm-10 col-md-10 co-pay-label">
              Co-pay and/or co-insurance for session:
            </p>
            <div class="col-xs-12 col-sm-2 text-right">
              <p class="co-pay-value">
                ${{ form_data.co_pay ? form_data.co_pay : "0" }}
              </p>
            </div>
          </div>
          <div class="row mb-credit">
            <p class="col-xs-12 col-sm-10 col-md-10 co-pay-label">
              Payment for session not covered due to deductible:
            </p>
            <div class="col-xs-12 col-sm-2 text-right">
              <p class="co-pay-value">
                {{ deductible }}
              </p>
            </div>
          </div>
          <div class="row mb-credit">
            <p class="col-xs-12 col-sm-10 col-md-10 co-pay-label">
              Self-pay for session when paid out-of-pocket:
            </p>
            <div class="col-xs-12 col-sm-2 text-right">
              <p class="co-pay-value">
                ${{ form_data.self_pay ? form_data.self_pay : "0" }}
              </p>
            </div>
          </div>
          <div class="row mb-credit">
            <p class="col-xs-12 col-sm-10 col-md-10 co-pay-label">
              Charge for cancellation without 24 hours&#039; notice:
            </p>
            <div class="col-xs-12 col-sm-2 text-right">
              <p class="co-pay-value">
                ${{
                  form_data.charge_for_cancellation
                    ? form_data.charge_for_cancellation
                    : "0"
                }}
              </p>
            </div>
          </div>
          <div class="row mb-credit">
            <p class="col-xs-12 col-sm-10 col-md-10 co-pay-label">
              Other charges price:
            </p>
            <div class="col-xs-12 col-sm-2 text-right">
              <p class="co-pay-value">
                ${{
                  form_data.other_charges_price
                    ? form_data.other_charges_price
                    : "0"
                }}
              </p>
            </div>
          </div>
          <div class="row mb-credit">
            <p class="col-xs-12 col-sm-10 col-md-10 co-pay-label">
              Other charges specify:
            </p>
            <div class="col-xs-12 col-sm-2 text-right">
              <p class="co-pay-value">
                {{ form_data.other_charges ? form_data.other_charges : "-" }}
              </p>
            </div>
          </div>
          <div class="row mb-credit">
            <b class="col-md-12">
                **Note: When a deductible applies, our staff will provide the exact charges for each visit in advance.<br/>
                Patients are expected to pay for services at the time services are rendered. We accept cash, checks, and all major credit cards.
            </b>
          </div>
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
    patientInfo: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    form_data: null,
  }),
  computed: {
    patient() {
      return this.$store.state.currentPatient;
    },
    deductible() {
        return this.form_data.payment_for_session_not_converted && parseFloat(this.form_data.payment_for_session_not_converted) > 0 ? 'TBD**' : '$0';
    }
  },
  mounted() {
    this.form_data = this.data;
  },
  methods: {
    validateForm() {
      this.$emit("validation-success");
    },
  },
};
</script>
