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
                                    WOODLAND HILLS SERVICE AREA
                                    <br>
                                    Referral to Behavioral Health Intensive Outpatient Services
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    Submit via encrypted attachment to:
                                    <a href="mailto:wh-outsidemedicalcase-management@kp.org">wh-outsidemedicalcase-management@kp.org</a>
                                </p>
                                <p>
                                    **Please note, depending on severity and history, patient may be referred to Primary Care, Depression Care, Developmental Clinic (peds ADHD/Autism), OR Psychiatry. <b>Please advise your patient.</b>
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
                                    <document-textarea
                                            name="mrn"
                                            label="MRN"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="required"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <div class="form-group col-xs-12 input-container input-container-diagnosis">
                                        <label class="control-label">Diagnosis with specifiers</label>
                                        <div class="fastselect-disabled" v-if="statuses.editingDisabled"></div>
                                        <input type="text"
                                               multiple
                                               id="diagnoseMultipleSelect"
                                               class="tagsInput"
                                               data-user-option-allowed="true"
                                        />
                                    </div>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>Most recent TPI scores (if available)</label>
                                </div>

                                <div class="row row--flex">
                                    <document-input
                                            name="bhi_score"
                                            label="BHI Score"
                                            size="col-xs-4"
                                            v-model="form_data.bhi_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <document-input
                                            name="gad7_score"
                                            label="GAD 7 Score"
                                            size="col-xs-4"
                                            v-model="form_data.gad7_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <document-input
                                            name="phq9_score"
                                            label="PHQ9 Score"
                                            size="col-xs-4"
                                            v-model="form_data.phq9_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <br />

                                <div class="row row--flex">
                                    <document-input
                                            name="current_sympthoms"
                                            label="Current Symptoms/Behaviors warranting BHIOS evaluation"
                                            size="col-xs-6"
                                            v-model="form_data.current_sympthoms"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <document-input
                                            name="internal_and_external_resources"
                                            label="Internal and External Resources currently utilizied"
                                            size="col-xs-6"
                                            v-model="form_data.internal_and_external_resources"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <div class="row row--flex">
                                    <document-input
                                            name="brief_summary"
                                            label="Brief Summary of previous levels of care"
                                            size="col-xs-6"
                                            v-model="form_data.brief_summary"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <document-input
                                            name="level_of_care"
                                            label="Level of Care requested"
                                            size="col-xs-6"
                                            v-model="form_data.level_of_care"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="additional_information"
                                            label="Additional information/assessment/recommendation"
                                            v-model="form_data.additional_comments"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="required"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>PROVIDER'S INFORMATION</label>
                                </div>
                                <div class="row  input-row">
                                    <document-input
                                            name="provider_name"
                                            label="Provider Name"
                                            size="col-md-6"
                                            v-model="form_data.provider_name"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="provider_license_no"
                                            label="Provider License No."
                                            size="col-md-6"
                                            v-model="form_data.provider_license_no"
                                            :disabled="true"
                                    ></document-input>
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
  import dateOfService from './../../../mixins/date-of-service';
  import diagnosisMethods from './../../../mixins/diagnosis-and-icd-code-methods';

  export default {
    mixins: [validate, save, methods, dateOfService, diagnosisMethods],
    data(){
      return{
        document_name: 'kp-bhios-wh',
        document_title: 'BHIOS',
        diagnosis_icd_code_fastselect: null,
      }
    },
    beforeMount(){
      if(!this.form_data && this.$store.state.currentDocument){
        this.patient_id = this.$store.state.currentPatient.id;
        this.form_data = {
          first_name: this.$store.state.currentPatient.first_name,
          last_name: this.$store.state.currentPatient.last_name,
          date: this.formatDate(new Date(), this.momentDateFormat),
          mrn: null,
          additional_comments: null,
          past_and_current_medication: null,
          symptoms_duration: null,
          provider_name: this.parseProviderName(this.currentProvider.provider_name),
          provider_license_no: this.currentProvider.license_no,
          provider_email: this.currentProvider.email,
          provider_phone: this.currentProvider.phone,
          bhi_score: null,
          phq9_score: null,
          gad7_score: null,
          diagnosis_icd_code: null,
          exhibits_disorder_cb: null,
          needs_adhd_cb: null,
          has_failed_pcp_cb: null,
          current_sympthoms: null,
          internal_and_external_resources: null,
          brief_summary: null,
          level_of_care: null,
          additional_information: null,
        }
      }
    },
    mounted() {
      let self = this;
      let menu_item_selector = 'kp-bhios-wh';
      let document_name = self.getFormName(menu_item_selector);
      self.document_name = document_name;
      self.document_title = self.getFormTitle(menu_item_selector);

      window.setTimeout(() => {
        $('#'+this.document_name).on('shown.bs.modal', function() {
          $('body').addClass('custom-modal');
          self.initDiagnosisAndIcdCode('diagnosis_icd_code_fastselect', 'diagnoseMultipleSelect', 'diagnosis_icd_code');

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

        this.setDiagnosisIcdCode('diagnosis_icd_code_fastselect', 'diagnosis_icd_code');

        return error;
      },
    },
  }
</script>