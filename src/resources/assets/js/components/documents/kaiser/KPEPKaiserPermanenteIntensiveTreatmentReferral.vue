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
                        <div class="row">
                            <div class="col-lg-12">
                                <p class="text-left pt-3">
                                    E-mail Completed form to:
                                    <a href="mailto:scal-bh-panel@kp.org"><span class="primary"> scal-bh-panel@kp.org</span></a>
                                </p>
                            </div>
                            <div class="col-lg-12">
                                 <p class="pt-3"> <span class="font-weight-bold">Date of Referral:</span> {{getFormattedDate()}} </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document" novalidate>

                                <div class="row input-row">
                                    <document-input
                                            name="patient_name"
                                            label="Patient Name"
                                            size="col-md-4"
                                            v-model="form_data.patient_name"
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
                                    <document-input
                                            name="dob"
                                            label="DOB"
                                            size="col-md-4"
                                            v-model="form_data.dob"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                </div>

                                <br/>
                                <div class="row input-row">
                                    <document-input
                                            name="patient_home_phone"
                                            label="Best number to reach patient"
                                            size="col-md-4"
                                            v-model="form_data.patient_home_phone"
                                            @change="setHasValue"
                                           :disabled="statuses.editingDisabled"
                                            v-mask="'###-###-####'"
                                            validateRules="regex:^([\d]{3}-){2}[\d]{4}$"
                                    ></document-input>
                                    <document-input
                                            name="medical_center_area_of_patient"
                                            label="Medical center area of Patient"
                                            size="col-md-4"
                                            v-model="form_data.medical_center_area_of_patient"
                                    ></document-input>
                                </div>

                                <br/>
                                <div class="row input-row">
                                    <div class="form-group col-md-12 input-container input-container-diagnosis">
                                    <label class="control-label diagnosis-label" style="z-index:92!important;">Diagnosis</label>
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
                               <div class="row input-row">
                                    <document-input
                                            name="reason_problems"
                                            label="Reason for referral/Major Presenting Problems:"
                                            size="col-md-12"
                                            v-model="form_data.reason_problems"
                                    ></document-input>
                                </div>

                                <br/>
                                <div class="row input-row">
                                    <document-input
                                            name="current_smart_goals"
                                            label="Current SMART goals and progress toward goals and or barriers to progress:"
                                            size="col-md-12"
                                            v-model="form_data.current_smart_goals"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <br/>
                                <div class="row input-row">
                                    <document-input
                                            name="episode_of_care"
                                            label="Internal and external resources currently utilized in this
                                            episode of care (e.g., medication, groups, AA, therapy, DMC, 
                                            IOP, community resource):"
                                            size="col-md-12"
                                            v-model="form_data.episode_of_care"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <br/>
                                <div class="row input-row">
                                    <document-input
                                            name="currently_utilized"
                                            label="Internal and external resources previously utilized by patient and outcome:"
                                            size="col-md-12"
                                            v-model="form_data.currently_utilized"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <br/>
                                <div class="row input-row">
                                    <document-input
                                            name="level_of_care_recommended"
                                            label="Level of Care recommended:"
                                            size="col-md-12"
                                            v-model="form_data.level_of_care_recommended"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label class="font-weight-bold">Most Recent TPI scores:</label>
                                </div>
                                <div class="row  input-row">
                                    <document-input
                                            name="bhi_score"
                                            label="BHI"
                                            size="col-md-3"
                                            v-model="form_data.bhi_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <document-input
                                            name="gad_score"
                                            label="GAD"
                                            size="col-md-3"
                                            v-model="form_data.gad_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <document-input
                                            name="phq9_score"
                                            label="PHQ9"
                                            size="col-md-3"
                                            v-model="form_data.phq9_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                    <document-input
                                            name="cssrs_score"
                                            label="CSSRS"
                                            size="col-md-3"
                                            v-model="form_data.cssrs_score"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>
                                <div class="row ">
                                    <label class="font-weight-bold">(CSSRS 3+ Safety Plan Required)</label>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label class="font-weight-bold">High Risk Behaviors (check all that apply):</label>
                                </div>
                                 <div class="row">
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.suicidality_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Suicidality (passive ideations/ active but no plan)- If 
                                                   patient is in imminent danger call 911/Go to ER
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.homicidal_ideations_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Homicidal ideations/ Aggressive behaviors toward others
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.major_impairment_occupation_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Major impairment with occupation/school (lost or about to lose employment)
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.major_impairments_relationally_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Major impairments relationally (chronic relational 
                                                   issues, and or lack of social support)
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.major_impairment_independently_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Major impairment with ability to care for oneself independently
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.recent_involvement_law_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Recent involvement with the law (legal troubles) and or DCFS
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.issues_with_meeting_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Issues with meeting basic needs such as housing
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.current_mania_issues_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Current mania and or impulse control issues
                                      </label>
                                    </div>
                                    <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.current_drug_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Current drug and or alcohol use (To access Addiction services,
                                                    patients call the Behavioral health line)
                                      </label>
                                    </div>
                                  </div>

                                <br/>
                                <div class="row input-row">
                                    <document-input
                                            name="patient_other_pertinent_information"
                                            label="Any other pertinent information:"
                                            size="col-md-12"
                                            v-model="form_data.patient_other_pertinent_information"
                                    ></document-input>
                                </div>

                                <br />
                                <div class="row">
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.initial_assessment_tridiuum_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Initial Assessment has been completed on Tridiuum
                                      </label>
                                  </div>

                                  <br />
                                   <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.patient_agree_higher_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Patient agrees to a higher care referral
                                      </label>
                                  </div>
                                </div>

                                <br />
                                <div class="row ">
                                    <label>External Provider Information</label>
                                </div>
                                <div class="row  input-row">
                                    <document-input
                                            name="provider_name"
                                            label="Provider Name"
                                            size="col-md-3"
                                            v-model="form_data.provider_name"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="group_practice"
                                            label=" Group Practice (if applicable)"
                                            size="col-md-3"
                                            v-model="form_data.group_practice"
                                    ></document-input>
                                    <document-input
                                            label="Provider Email"
                                            size="col-md-3"
                                            v-model="form_data.provider_email"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="email"
                                    ></document-input>
                                    <document-input
                                            label="Provider Phone"
                                            size="col-md-3"
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
  import DiagnosesMultiselect from './../../../mixins/diagnoses-multiselect';

  export default {
    mixins: [validate, save, methods, DiagnosesMultiselect],
    data(){
      return{
        document_name: 'kpep-intensive-treatment-referral',
        document_title: 'KPEP Kaiser Permanente Intensive Treatment Referral',
        statuses: {
          confirm_diagnoses: false,
        }
      }
    },
    beforeMount(){
      if(!this.form_data && this.$store.state.currentDocument){
        this.patient_id = this.$store.state.currentPatient.id;
        let patient_full_name = this.$store.state.currentPatient.first_name + ' ' + this.$store.state.currentPatient.last_name;
        this.form_data = {
          patient_name: patient_full_name,
          date_of_referral: this.formatDate(new Date(), this.momentDateFormat),
          mrn: this.$store.state.currentPatient.subscriber_id,
          dob: this.$store.state.currentPatient.date_of_birth,
          patient_home_phone: this.$store.state.currentPatient.home_phone || null,
          medical_center_area_of_patient: null,
          diagnosis_icd_code: null,
          selected_diagnoses: this.$store.state.currentPatient.diagnoses || [],
          reason_problems: null,
          current_smart_goals: null,
          episode_of_care: null,
          currently_utilized: null,
          level_of_care_recommended: null,
          bhi_score: null,  
          gad_score: null,  
          phq9_score: null, 
          cssrs_score: null,
          suicidality_cb: false,
          homicidal_ideations_cb: false,
          major_impairment_occupation_cb: false,
          major_impairments_relationally_cb: false,
          major_impairment_independently_cb: false,
          recent_involvement_law_cb: false,
          issues_with_meeting_cb: false,
          current_mania_issues_cb: false,
          current_drug_cb: false,
          patient_other_pertinent_information: null,
          patient_initial_assessment_tridiuum_cb: false,
          patient_agree_higher_cb: false,
          provider_name: this.parseProviderName(this.currentProvider.provider_name),
          group_practice: null,
          provider_email: this.currentProvider.email,
          provider_phone: this.currentProvider.phone,
        }
      }
    },
    mounted() {
      let self = this;
      let menu_item_selector = 'kpep-intensive-treatment-referral';
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

<style scoped>

.attention-text{
  color:#b71c1c;
}

.attention-text-bold{
  font-weight:bold !important;
  text-decoration: underline;
}

.refferal-date{
 border:1px solid 1px solid #c9c9c9 !important;
}

.font-weight-normal{
  font-weight:400 !important;
}
</style>
