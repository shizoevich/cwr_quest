export default {
  props: {
    isStep: Boolean,
    noCoPay: Boolean,
  },
  computed: {
    patient() {
      return this.$store.state.currentPatient;
    },
  },

  methods: {
    initFormData() {
      let first_name = this.patient.first_name;
      let last_name = this.patient.last_name;
      let diff = new Date(
        Date.now() - new Date(this.patient.date_of_birth).getTime()
      );
      let years_old = Math.abs(diff.getUTCFullYear() - 1970);

      this.form_data.new_patient.name = first_name + " " + last_name;
      this.form_data.new_patient.home_address = this.patient.address || "";
      this.form_data.new_patient.city = this.patient.city || "";
      this.form_data.new_patient.state = this.patient.state || "";
      this.form_data.new_patient.zip = this.patient.zip || "";
      this.form_data.agreement_for_service_and_hipaa_privacy_notice_and_patient_rights.name =
        first_name + " " + last_name;
      this.form_data.new_patient.date_of_birth = this.$moment(
        this.patient.date_of_birth
      ).format("MM/DD/YYYY");
      this.form_data.new_patient.email = this.patient.email || this.patient.secondary_email || "";
      this.form_data.new_patient.patient_id = this.patient.id;
      this.form_data.new_patient.home_phone = this.patient.home_phone
        ? this.patient.home_phone
        : "";
      this.form_data.new_patient.mobile_phone = this.patient.cell_phone
        ? this.patient.cell_phone
        : "";
      this.form_data.new_patient.work_phone = this.patient.work_phone
        ? this.patient.work_phone
        : "";
      this.form_data.new_patient.years_old = years_old;

      this.form_data.confidential_information.name =
        first_name + " " + last_name;
      this.form_data.confidential_information.date_of_birth = this.$moment(
        this.patient.date_of_birth
      ).format("MM/DD/YYYY");
      this.form_data.confidential_information.years_old = years_old;

      this.form_data.telehealth.name = first_name + " " + last_name;

      this.form_data.payment_for_service.name = first_name + " " + last_name;
      this.form_data.payment_for_service.is_payment_forbidden = !!this.patient.is_payment_forbidden;
    },

    initPaymentForm() {
      if (this.patientForms) {
        let paymentForm = this.patientForms.find(
          (form) => form.type.name === "payment_for_service"
        );
        if (paymentForm) {
          for (let paymentType in paymentForm.metadata) {
            this.form_data.payment_for_service[paymentType] = paymentForm.metadata[paymentType];
          }
        }
      }
    },

    initSimpleFormData() {
      let first_name = this.patient.first_name;
      let last_name = this.patient.last_name;
      this.form_data.name = first_name + " " + last_name;
      this.form_data.date_of_birth = this.$moment(
        this.patient.date_of_birth
      ).format("MM/DD/YYYY");
      this.form_data.patient_id = this.patient.id;
      this.form_data.home_phone = this.patient.home_phone
        ? this.patient.home_phone
        : "";
      this.form_data.cell_phone = this.patient.cell_phone
        ? this.patient.cell_phone
        : "";
      this.form_data.work_phone = this.patient.work_phone
        ? this.patient.work_phone
        : "";
      if (!this.noCoPay) {
        let co_pay = this.patient.visit_copay;
        if (co_pay !== null && co_pay !== undefined) {
          this.form_data.co_pay = co_pay + "";
        }
      }
      let diff = new Date(
        Date.now() - new Date(this.form_data.date_of_birth).getTime()
      );
      this.form_data.years_old = Math.abs(diff.getUTCFullYear() - 1970);
    },
  },
};
