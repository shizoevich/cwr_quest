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
                                    Woodland Hills/West Ventura Service Area
                                    <br>
                                    Medication Evaluation Referrals
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    Encrypt & email this form to:
                                    <a href="mailto:wh-outsidemedicalcase-management@kp.org">wh-outsidemedicalcase-management@kp.org</a>
                                </p>
                                <p>
                                    <b>** Please advise your patient</b>, depending on severity and history, patient may be referred to Primary Care, Depression Care, Developmental Clinic (peds ADHD/Autism), OR Psychiatry.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <table class="table-score">
                                <tr>
                                    <td class="green">
                                        <p class="score-diagnose"><b>Mild Depression</b> (PHQ9 <10)</p>
                                        <p class="score-diagnose"><b>Mild Anxiety</b> (GAD7 <5)</p>
                                        <p class="score-diagnose">“circumstantial stress”</p>
                                    </td>
                                    <td class="orange">
                                        <p class="score-diagnose"><b>Moderate Depression</b> (PHQ9 10-19)</p>
                                        <p class="score-diagnose"><b>Moderate Anxiety</b> (GAD7 5-14)</p>
                                    </td>
                                    <td class="yellow">
                                        <p class="score-diagnose"><b>Severe Depression</b> (PHQ9 >20)</p>
                                        <p class="score-diagnose"><b>Severe Anxiety</b> (GAD7 >15)</p>
                                        <p class="score-diagnose">ADHD evaluations</p>
                                        <p class="score-diagnose">Psychosis</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="green">
                                        <ul>
                                            <li>Referral to Primary care physician</li>
                                            <li>Wellness Coaches</li>
                                            <li>CALM app</li>
                                            <li>Exercise</li>
                                            <li>KP.org (sleep, mood&amp;stress management tools)</li>
                                        </ul>
                                    </td>
                                    <td class="orange">
                                        <ul>
                                            <li>Referral to Primary care physician or depression care program if patient hasn't had medication trials.</li>
                                            <li>Medication management</li>
                                        </ul>
                                    </td>
                                    <td class="yellow">
                                        <ul>
                                            <li>Patient will be scheduled to see a psychiatrist.</li>
                                            <li>
                                                Please send a referral and intake if not previously submitted.
                                                <b><u>Patient will not be scheduled without an intake.</u></b>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <br />

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

                                <br/>

                                <div class="row ">
                                    <label>TPI Scores</label>
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
                                    <div class="description-container col-xs-8">
                                    </div>
                                </div>
                                <div class="row row--flex">
                                    <document-input
                                            name="phq9_score"
                                            label="PHQ-9 Score"
                                            size="col-xs-4"
                                            v-model="form_data.phq9_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <div class="description-container col-xs-8">
                                        Patients with mild symptoms and/or TPI scores of 9-19 with no previous med trials will be referred to Depression Care or Primary Care.
                                    </div>
                                </div>
                                <div class="row row--flex">
                                    <document-input
                                            name="gad_score"
                                            label="GAD Score"
                                            size="col-xs-4"
                                            v-model="form_data.gad_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <div class="description-container col-xs-8">
                                        Patients with mild symptoms and/or TPI scores of less than 14 with no previous med trials will be referred to Depression Care or Primary Care.
                                    </div>
                                </div>
                                <div class="row row--flex">
                                    <document-input
                                            key="cssrs-score"
                                            name="cssrs_score"
                                            label="CSSRS Score"
                                            size="col-xs-4"
                                            v-model="form_data.cssrs_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <div class="description-container last col-xs-8">
                                        If patients score is higher than 3 please consider a BHIOS referral.
                                    </div>
                                </div>

                                <br />

                                <div class="row ">
                                    <label>Please select all that apply</label>
                                </div>

                                <div class="row row--flex">
                                    <div class="form-group input-container col-xs-12 radio-without-label">
                                        <div class="col-lg-12 checkbox">
                                            <label>
                                                <input type="checkbox"
                                                       name="needs_adhd_cb"
                                                       v-model="form_data.needs_adhd_cb"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Patient needs ADHD Medication/Assessment
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row--flex">
                                    <div class="form-group input-container col-xs-12 radio-without-label">
                                        <div class="col-lg-12 checkbox">
                                            <label>
                                                <input type="checkbox"
                                                       name="has_failed_pcp_cb"
                                                       v-model="form_data.severe_diagnoses_cb"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Patient presents with complicated depression, anxiety, or other severe diagnoses that require
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row--flex">
                                    <div class="form-group input-container col-xs-12 radio-without-label">
                                        <div class="col-lg-12 checkbox">
                                            <label>
                                                <input type="checkbox"
                                                       name="has_failed_pcp_cb"
                                                       v-model="form_data.has_failed_pcp_cb"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Patient has had failed PCP medication trials
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row--flex">
                                    <div class="form-group input-container col-xs-12 radio-without-label">
                                        <div class="col-lg-12 checkbox">
                                            <label>
                                                <input type="checkbox"
                                                       name="exhibits_disorder_cb"
                                                       v-model="form_data.exhibits_disorder_cb"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Patient exhibits thought disorder signs/symptoms
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <br />

                                <div class="row ">
                                    <label>**Please provide additional <b><u>REQUIRED</u></b> information to support your referral</label>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="past_and_current_medication"
                                            label="Past and current medication"
                                            v-model="form_data.past_and_current_medication"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="required"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="symptoms_duration"
                                            label="Current severity/duration of symptoms"
                                            v-model="form_data.symptoms_duration"
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

                                <div class="row  input-row">
                                    <document-input
                                            name="provider_email"
                                            label="Provider Email"
                                            size="col-md-6"
                                            v-model="form_data.provider_email"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="email"
                                    ></document-input>
                                    <document-input
                                            name="provider_phone"
                                            label="Provider Phone"
                                            size="col-md-6"
                                            v-model="form_data.provider_phone"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-mask="'###-###-####'"
                                            validateRules="regex:^([\d]{3}-){2}[\d]{4}$"
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
  import dateOfService from './../../../mixins/date-of-service';
  import DiagnosesMultiselect from './../../../mixins/diagnoses-multiselect';

  export default {
    mixins: [validate, save, methods, dateOfService, DiagnosesMultiselect],
    data(){
      return{
        document_name: 'kp-medication-evaluation-referral',
        document_title: 'Medication Evaluation Referral - Kaiser Woodland Hills',
        diagnosis_icd_code_fastselect: null,
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
          past_and_current_medication: null,
          symptoms_duration: null,
          provider_name: this.parseProviderName(this.currentProvider.provider_name),
          provider_license_no: this.currentProvider.license_no,
          provider_email: this.currentProvider.email,
          provider_phone: this.currentProvider.phone,
          bhi_score: null,
          phq9_score: null,
          gad_score: null,
          cssrs_score: null,
          diagnosis_icd_code: null,
          exhibits_disorder_cb: null,
          needs_adhd_cb: null,
          has_failed_pcp_cb: null,
          severe_diagnoses_cb: null,
          selected_diagnoses: this.$store.state.currentPatient.diagnoses || []
        }
      }
    },
    mounted() {
      let self = this;
      let menu_item_selector = 'kp-medication-evaluation-referral';
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