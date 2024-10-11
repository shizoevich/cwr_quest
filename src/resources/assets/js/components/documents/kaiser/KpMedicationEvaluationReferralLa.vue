<template>
    <div>
        <div class="black-layer" v-if="statuses && statuses.saving" style="position: fixed;
top: 0;">
            <pageloader add-classes="saving-loader" image-alt="Saving..."></pageloader>
        </div>

        <div class="modal modal-vertical-center fade" :id="document_name" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="close" data-dismiss="modal"
                                        @click.prevent="closeDocument"
                                >&times;</button>
                                <h4 class="modal-title" v-html="computed_modal_title()"></h4>
                            </div>
                        </div>
                        <div class="row last-row">
                            <div class="col-lg-12 text-left">
                                Date: {{getFormattedDate()}}
                            </div>
                            <div class="col-lg-12">
                                <h4 class="modal-title">
                                    Los Angeles Medical Center Service Area
                                    <br>
                                    Medication Evaluation Referral
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    Submit via encrypted attachment to:
                                    <a href="mailto:LAMC-OSM-BH@kp.org">LAMC-OSM-BH@kp.org</a>
                                </p>
                              <p>Please attach the patient’s initial assessment.</p>
                              <p>
                                <b><u>**PATIENT WILL NOT BE SCHEDULED WITHOUT AN INTAKE**</u></b>
                              </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document" novalidate>

                                <div class="row input-row">
                                    <document-input
                                            name="first_name"
                                            label="Firstname"
                                            size="col-md-4"
                                            v-model="form_data.first_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="last_name"
                                            label="Lastname"
                                            size="col-md-4"
                                            v-model="form_data.last_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="mrn"
                                            label="MRN"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="required"
                                    ></document-input>
                                </div>
                                <div class="row">
                                  <div class="form-group input-container col-md-6 radio-without-label radio-recommend-treatment">
                                    <div class="col-lg-12">
                                      Initial Assessment Attached via
                                    </div>
                                    <div class="col-lg-12 radio">
                                      <label class="radio-inline">
                                        <input type="radio"
                                               v-model="form_data.ia_attached_via_rb"
                                               value="email"
                                               @change="setHasValue"
                                               :disabled="statuses.editingDisabled"
                                        >
                                        Email
                                      </label>

                                      <label class="radio-inline">
                                        <input type="radio"
                                               v-model="form_data.ia_attached_via_rb"
                                               value="tridiuum"
                                               @change="setHasValue"
                                               :disabled="statuses.editingDisabled"
                                        >
                                        Tridiuum
                                      </label>
                                    </div>
                                  </div>
                                  <div class="form-group col-md-6 input-container input-container-diagnosis">
                                    <label class="control-label diagnosis-label" style="z-index:92!important;">Diagnosis with Specifiers</label>
                                    <div class="fastselect-disabled" v-if="c_diagnoses_editing_disabled"></div>
                                    <diagnoses-multiselect
                                      style="z-index:91"
                                      id="diagnoseMultipleSelect"
                                      v-if="form_data.selected_diagnoses"
                                      :selectedDiagnoses="form_data.selected_diagnoses"
                                      customClass="multiselect-blue diagnoses-multiselect document-diagnoses-multiselect"
                                      @setDiagnoses="setElectronicDocumentsDiagnoses"
                                    ></diagnoses-multiselect>
                                  </div>
                                </div>
                                <div class="row input-row">
                                  <document-textarea
                                    label="Past and Current Medication"
                                    size="col-md-6"
                                    v-model="form_data.past_and_current_medication"
                                    @change="setHasValue"
                                    :disabled="statuses.editingDisabled"
                                  ></document-textarea>
                                  <document-textarea
                                    label="Current Drug/Alcohol Use"
                                    size="col-md-6"
                                    v-model="form_data.drug_or_alcohol_use"
                                    @change="setHasValue"
                                    :disabled="statuses.editingDisabled"
                                  ></document-textarea>
                                </div>

                                <div class="row input-row">
                                  <document-textarea
                                    label="Describe current symptoms, impairments in functioning, and duration"
                                    size="col-md-12"
                                    v-model="form_data.current_symptoms"
                                    @change="setHasValue"
                                    :disabled="statuses.editingDisabled"
                                  ></document-textarea>
                                </div>

                                <br />

                                <div class="row ">
                                    <label>External Provider Information</label>
                                </div>
                                <div class="row  input-row">
                                    <document-input
                                            name="provider_name"
                                            label="Provider Name"
                                            size="col-md-4"
                                            v-model="form_data.provider_name"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            label="Provider Email"
                                            size="col-md-4"
                                            v-model="form_data.provider_email"
                                    ></document-input>
                                    <document-input
                                      label="Provider Phone"
                                      size="col-md-4"
                                      v-model="form_data.provider_phone"
                                      v-mask="'###-###-####'"
                                    ></document-input>
                                </div>

                                <div class="row" style="margin-top:15px;" id="ia-confirm-diagnoses" v-if="!c_diagnoses_editing_disabled">
                                  <p>IMPORTANT! Please make sure that correct ICD Code(s) has been selected. You can change this entry at a later time, but only until the billing has been submitted for this visit. After you will only be able to change ICD codes for future visits of this patient.</p>

                                  <label class="control-label" style="font-weight:normal;">
                                    <input type="checkbox" v-model="statuses.confirm_diagnoses"> I understand and confirm the ICD code(s) are correct
                                  </label>
                                </div>

                                <div class="form-note-button-block">
                                    <div class="row">
                                        <div class="col-lg-12 text-center" style="padding-right:0;margin-bottom:15px;">
                                        <span class="text-red validation-error-msg" v-if="statuses.noErrors === false && !validation_message">
                                            Please make sure you have filled all the required fields.
                                        </span>
                                            <span class="text-red validation-error-msg" v-if="validation_message">
                                                {{validation_message}}
                                        </span>
                                        </div>

                                        <div class="col-lg-12 text-right" style="padding-right:0;">
                                            <div class="col-lg-12" style="padding-right:0;">
                                                <button type="submit" class="btn btn-primary document-button"
                                                        @click.prevent="saveDocument"
                                                        v-if="!statuses.editingDisabled"
                                                >
                                                    Save
                                                </button>

                                                <button type="button" class="btn btn-default document-button"
                                                        @click.prevent="closeDocument"
                                                >
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!--/.modal-content-->
            </div>
        </div>
    </div>
</template>

<script>
  import validate from './../../../mixins/validate';
  import save from './../../../mixins/save-document-if-valid';
  import methods from './../../../mixins/document-methods';
  import DiagnosesMultiselect from './../../../mixins/diagnoses-multiselect';

  export default {
    mixins: [validate, save, methods, DiagnosesMultiselect],
    data(){
      return{
        document_name: 'kp-medication-evaluation-referral-la',
        document_title: 'Medication Evaluation Referral - Kaiser Los Angeles',
        statuses: {
          confirm_diagnoses: false,
        }
      }
    },
    beforeMount(){
      if(!this.form_data && this.$store.state.currentDocument){
        this.patient_id = this.$store.state.currentPatient.id;
        this.form_data = {
          first_name: this.$store.state.currentPatient.first_name,
          last_name: this.$store.state.currentPatient.last_name,
          date: this.formatDate(new Date(), this.momentDateFormat),
          mrn: this.$store.state.currentPatient.subscriber_id,
          ia_attached_via_rb: null,
          selected_diagnoses: this.$store.state.currentPatient.diagnoses || [],
          past_and_current_medication: null,
          drug_or_alcohol_use: null,
          current_symptoms: null,

          provider_name: this.parseProviderName(this.currentProvider.provider_name),
          provider_email: this.currentProvider.email,
          provider_phone: this.currentProvider.phone,
        }
      }
    },
    mounted() {
      let self = this;
      let menu_item_selector = 'kp-medication-evaluation-referral-la';
      let document_name = self.getFormName(menu_item_selector);
      self.document_name = document_name;
      self.document_title = self.getFormTitle(menu_item_selector);

      window.setTimeout(() => {
        $('#'+this.document_name).on('shown.bs.modal', function() {
          $('body').addClass('custom-modal');

          autosize($('#'+document_name).find('textarea'));

          $('.input-container').on('click', function(){

            $(this).find('.input-element').focus();
          });

          $('#'+document_name).find('input.el-input__inner').addClass('input-element');
        }).on('hidden.bs.modal', function() {
          $('body').removeClass('custom-modal');
        });
      },500);
    },
    methods: {
      getCustomValidation(){

        let error = false;
        if(!this.c_diagnoses_editing_disabled && !this.statuses.confirm_diagnoses) {
          $('#ia-confirm-diagnoses label').addClass('text-red');
          error = true;
        }

        return error;
      },
    },
  }
</script>