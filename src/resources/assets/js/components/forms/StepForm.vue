<template>
  <patient-form-layout @loaded="handleLoadedData">
    <template v-slot:content>
      <div class="patiens-multistep-form multistep-form">
        <template v-if="steps.length > 0">
          <div class="multistep-form-progress progress" v-if="steps.length > 1">
            <div
              class="multistep-form-progress-bar progress-bar"
              role="progressbar"
              aria-valuenow="100"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
          <div
            class="multistep-form-steps"
            :class="{ 'is-single': steps.length === 1 }"
          >
            <template v-for="(step, index) in steps">
              <div
                class="step-container"
                :class="{ active: currentStep === index }"
              >
                <div
                  class="step"
                  :class="{
                    active: currentStep === index, 
                    completed: step.completed && currentStep >= index
                  }"
                >
                  <div class="step-number">
                    <span>{{ index + 1 }}</span>
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      xmlns:xlink="http://www.w3.org/1999/xlink"
                      version="1.1"
                      class="icon-complete"
                      id="Capa_1"
                      x="0px"
                      y="0px"
                      width="512px"
                      height="512px"
                      viewBox="0 0 45.701 45.7"
                      style="enable-background: new 0 0 45.701 45.7;"
                      xml:space="preserve"
                    >
                      <g>
                        <path
                          d="M20.687,38.332c-2.072,2.072-5.434,2.072-7.505,0L1.554,26.704c-2.072-2.071-2.072-5.433,0-7.504    c2.071-2.072,5.433-2.072,7.505,0l6.928,6.927c0.523,0.522,1.372,0.522,1.896,0L36.642,7.368c2.071-2.072,5.433-2.072,7.505,0    c0.995,0.995,1.554,2.345,1.554,3.752c0,1.407-0.559,2.757-1.554,3.752L20.687,38.332z"
                          data-original="#000000"
                          class="active-path"
                          data-old_color="#000000"
                          fill="#FFFFFF"
                        />
                      </g>
                    </svg>
                  </div>
                </div>
                <div class="step-label">
                  Step <span class="index">{{ index + 1 }}</span>
                </div>
              </div>
            </template>
          </div>
        </template>
        <template v-if="!formDataInited">
          <pageloader class="loader" />
        </template>
        <div class="multistep-form-content" v-else>
          <h2 class="step-heading">
            <span class="index">{{ `${currentStep + 1}.` }}</span>
            {{ `${steps[currentStep].title}` }}
          </h2>
          <div class="step-content">
            <new-patient-form
              v-if="steps.length > 0 && formNames.includes('new_patient')"
              v-show="steps[currentStep].name === 'new_patient'"
              :data="form_data.new_patient"
              ref="new_patient"
              @validation-success="nextStep"
              @validation-fails="showError"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <patient-info-form
              v-if="
                steps.length > 0 &&
                formNames.includes(
                  'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights'
                )
              "
              v-show="
                steps[currentStep].name ===
                'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights'
              "
              :data="
                form_data.agreement_for_service_and_hipaa_privacy_notice_and_patient_rights
              "
              ref="agreement_for_service_and_hipaa_privacy_notice_and_patient_rights"
              @validation-fails="showError"
              @validation-success="nextStep"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <payment-for-service-form
              v-if="
                steps.length > 0 && formNames.includes('payment_for_service')
              "
              v-show="steps[currentStep].name === 'payment_for_service'"
              :hidden="steps[currentStep].name !== 'payment_for_service'"
              :data="form_data.payment_for_service"
              :patient-info="form_data.new_patient"
              ref="payment_for_service"
              @validation-fails="showError"
              @validation-success="nextStep"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <appointment-policy-form
              v-if="
                steps.length > 0 && formNames.includes('attendance_policy')
              "
              v-show="steps[currentStep].name === 'attendance_policy'"
              :hidden="steps[currentStep].name !== 'attendance_policy'"
              :data="form_data.payment_for_service"
              :patient-info="form_data.new_patient"
              ref="attendance_policy"
              @validation-fails="showError"
              @validation-success="nextStep"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <credit-card-form
              v-if="
                steps.length > 0 && formNames.includes('credit_card_on_file')
              "
              v-show="steps[currentStep].name === 'credit_card_on_file'"
              :hidden="steps[currentStep].name !== 'credit_card_on_file'"
              :data="form_data.payment_for_service"
              :patientEmail="patient_email"
              ref="credit_card_on_file"
              @validation-fails="showError"
              @validation-success="nextStep"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <confidential-form
              v-if="
                steps.length > 0 &&
                formNames.includes('confidential_information')
              "
              v-show="steps[currentStep].name === 'confidential_information'"
              is-step
              no-co-pay
              :data="form_data.confidential_information"
              :exchange-with="getConfidentialInformationExchangeWith()"
              ref="confidential_information"
              @validation-fails="showError"
              @validation-success="nextStep"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <telehealth-form
              v-if="steps.length > 0 && formNames.includes('telehealth')"
              v-show="steps[currentStep].name === 'telehealth'"
              :data="form_data.telehealth"
              ref="telehealth"
              @validation-fails="showError"
              @validation-success="nextStep"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <document-form
              v-if="
                steps.length > 0 && formNames.includes('supporting_documents')
              "
              v-show="steps[currentStep].name === 'supporting_documents'"
              :data="form_data.supporting_documents.documents"
              :doc-types="
                patientForms.find(
                  (item) => item.type.name === 'supporting_documents'
                ).metadata.documents
              "
              ref="supporting_documents"
              @documents-change="
                (value) => (form_data.supporting_documents.documents = value)
              "
              @validation-fails="showError"
              @validation-success="nextStep"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
            <signature-form
              v-if="signatureFormInited"
              v-show="steps[currentStep].name === 'electronic_signature'"
              ref="electronic_signature"
              :data="form_data.signature_data"
              :forms="formsData"
              @saving="setSavingStatus"
              @validation-fails="showError"
              @validation-success="saveForm"
              @add-loader="addLoader"
              @remove-loader="removeLoader"
            />
          </div>
        </div>

        <div class="form-note-button-block text-left" v-if="validation_message">
          <div class="row form-note-row">
            <span class="text-red validation-error-msg">{{
              validation_message
            }}</span>
          </div>
        </div>

        <div
          class="multistep-form-controls forms"
          :class="{
            'justify-end': currentStep === 0,
            'justify-between': currentStep > 0,
          }"
        >
          <button
            @click="prevStep"
            v-if="currentStep > 0"
            class="btn btn-lg btn-success btn-start btn-back ml-0"
          >
            Back
          </button>
          <button
            @click="nextStep"
            v-if="steps.length > 2 && steps[currentStep].patient_can_skip_form"
            class="btn btn-lg btn-success btn-start btn-back ml-auto"
          >
            Skip
          </button>
          <button
            @click="validateForm"
            :disabled="saving"
            class="btn btn-lg btn-success btn-start"
          >
            <template v-if="currentStep < steps.length - 1">
              Next
            </template>
            <template v-else>
              Finish
            </template>
          </button>
        </div>
      </div>
    </template>
  </patient-form-layout>
</template>

<script>
import * as NewPatientForm from "./NewPatientForm.vue";
import * as PatientInfoForm from "./PatientInformationForm.vue";
import * as PaymentForServiceForm from "./PaymentForServiceForm.vue";
import * as ConfidentialForm from "./patients/ConfidentialInformation.vue";
import * as DocumentForm from "./patients/DocumentForm.vue";
import * as AppointmentPolicyForm from "./AppointmentPolicyForm.vue";
import onbeforeunload from "../../helpers/onbeforeunload";
import VueScrollTo from "vue-scrollto";
import patientFormsMixin from "../../mixins/patient-forms";
import CreditCardForm from "./CreditCardForm";

export default {
  name: "StepForm",
  components: {
    CreditCardForm,
    NewPatientForm,
    PatientInfoForm,
    PaymentForServiceForm,
    AppointmentPolicyForm,
    ConfidentialForm,
    DocumentForm,
  },
  mixins: [patientFormsMixin],
  data: () => ({
    patient_email: '',
    progressWidth: 0,
    progressOffsetLeft: 0,
    currentStep: 0,
    progress: 0,
    maxProgress: 100,
    steps: [],
    form_data: {
      new_patient: {
        name: "",
        date_of_birth: "",
        home_address: "",
        city: "",
        state: "",
        zip: "",
        email: "",
        allow_mailing: "",
        home_phone: "",
        mobile_phone: "",
        work_phone: "",
        allow_home_phone_call: "",
        allow_mobile_phone_call: "",
        allow_mobile_send_messages: "",
        allow_work_phone_call: "",
        emergency_contact: "",
        emergency_contact_phone: "",
        emergency_contact_relationship: "",
        //how did you hear about us?
        yelp: false,
        google: false,
        yellow_pages: false,
        event_i_attended: false,
        hear_about_us_other: false,
        hear_about_us_other_specify: "",
        //I was referred by:
        friend_or_relative: false,
        another_professional: false,
        kaiser: false,
        referred_by_other_insurance: false,
        referred_by_other_insurance_specify: "",
      },
      agreement_for_service_and_hipaa_privacy_notice_and_patient_rights: {
        name: "",
        understand_agreements: null,
      },
      confidential_information: {
        name: "",
        date_of_birth: "",
        hereby_information_with: "",
        relationship: "",
        guardian_name: "",
        years_old: "",
      },
      payment_for_service: {
        name: "",
        card_data: {
          last_four: "",
          exp_month: "",
          exp_year: "",
          zip_code: "",
          brand: "",
        },
        is_payment_forbidden: false,
        understand_agreements: null,
      },
      telehealth: {
        name: "",
        home_address: "",
        emergency_contact: "",
        understand_agreements: null,
      },
      supporting_documents: {
        documents: [],
      },
      signature_data: {
        signature: "",
        signature18: "",
        relationship: "",
        guardian_name: "",
      },
    },
    formDataInited: false,
    validation_message: "",
    saving: false,
    patientForms: [],
    signatureFormInited: false,
  }),
  watch: {
    currentStep() {
      this.scrollTop();
      if (this.currentStep === this.steps.length - 1) {
        this.signatureFormInited = true;
      }
    },
    patientForms() {
      if (this.patientForms.length > 0) {
        this.initSteps();
      }
    },
    patient() {
      if (!this.formDataInited) {
        this.initFormData();
      }
    },
    "form_data.new_patient": {
      handler: function () {
        for (let key in this.form_data.telehealth) {
          if (key in this.form_data.new_patient) {
            this.form_data.telehealth[key] = this.form_data.new_patient[key];
          }
        }
      },
      deep: true,
    },
  },
  computed: {
    isFirstStep() {
      return this.currentStep === 0;
    },
    isLastStep() {
      return this.currentStep === this.steps.length - 1;
    },
    patient() {
      return this.$store.state.currentPatient;
    },
    formsData() {
      let formTypes = this.patientForms.map((form) => form.type.name);
      let formData = {};
      for (let formType of formTypes) {
        formData[formType] = this.form_data[formType];
      }

      return formData;
    },
    formNames() {
      return this.steps.map((step) => step.name);
    },
  },
  methods: {
    addLoader() {
      let loader = document.createElement("div");
      let loaderImg = document.createElement("img");
      let scrollTop = document.querySelector("html").scrollTop;

      loader.classList.add("loader-page");
      loaderImg.src = "/images/pageloader.gif";
      loader.appendChild(loaderImg);
      loader.style.top = `${scrollTop}px`;
      document.querySelector("html").classList.add("document-loader");
      document.body.appendChild(loader);
    },
    removeLoader() {
      let loader = document.querySelector(".loader-page");
      document.querySelector("html").classList.remove("document-loader");
      loader.parentNode.removeChild(loader);
    },
    getConfidentialInformationExchangeWith() {
        let exchangeWith = this.patientForms.find(
            (item) => item.type.name === 'confidential_information'
        );
        if(exchangeWith && exchangeWith.metadata && exchangeWith.metadata.exchange_with) {
            return exchangeWith.metadata.exchange_with;
        }

        return null;
    },

    formExists(formName) {
      return this.steps > 0 && this.formNames.includes(formName);
    },
    setSavingStatus(status) {
      this.saving = status;
    },
    initSteps() {
      for (let form of this.patientForms) {
        let step = Object.assign({}, form.type);
        step.completed = false;
        this.steps.push(step);
      }
      this.steps.push({
        name: "electronic_signature",
        title: "Electronic Signature",
        completed: false,
      });
      this.initPaymentForm();
    },
    validateForm() {
      this.validation_message = "";
      if (this.$refs[this.steps[this.currentStep].name]) {
        this.$refs[this.steps[this.currentStep].name].validateForm();
      } else {
        this.nextStep();
      }
    },
    saveForm() {
      this.$refs.electronic_signature.saveForm();
    },
    showError(errorMessage) {
      this.validation_message = errorMessage;
    },
    nextStep() {
      this.validation_message = "";
      this.steps[this.currentStep].completed = true;
      this.currentStep++;
    },
    prevStep() {
      this.currentStep--;
    },
    scrollTop() {
      this.$nextTick(() => {
        VueScrollTo.scrollTo(document.querySelector("#app-forms"), 1000, {
          container: "body",
          duration: "1000",
          easing: "ease",
          offset: 0,
          force: true,
        });
      });
    },
    handleLoadedData(data) {
      this.$nextTick(() => {
        this.formDataInited = true;
      });
      this.patient_email = data.email || '';
      this.patientForms = data.forms.filter((form) => !form.filled_at);
      if (this.patientForms.length === 0) {
        this.$router.push(`/f/${this.$route.params.hash}/download`);
      }
    },
  },
  mounted() {
    window.onbeforeunload = onbeforeunload;
    if (this.patient) {
      this.initFormData();
    }
  },
  beforeDestroy() {
    window.onbeforeunload = null;
  },
};
</script>

<style lang="scss" scoped>
  .ml-auto {
    margin-left: auto;
  }
  .multistep-form-controls {
    @media (max-width: 768px) {
      display: flex;
      flex-direction: column-reverse;
      align-items: flex-end;

      .btn {
        margin-bottom: 20px;
      }
    }

    .btn {
      min-width: 175px;
    }
  }
</style>
