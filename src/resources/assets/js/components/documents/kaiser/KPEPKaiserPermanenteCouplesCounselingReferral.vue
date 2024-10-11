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
                                <p class="text-justify attention-text  pt-3">
                                   <span class="attention-text-bold">Please note: </span>If the patient is in individual treatment and 
                                   the need for couples or family therapy arises, 
                                   <span class="attention-text-bold">the same therapist</span> may provide the couples or family 
                                   therapy sessions unless there is a <span  class="attention-text-bold">clinical
                                   rationale </span>
                                   for how this is contraindicated. Referral to a second provider for the purposes of couples or family therapy 
                                   should only be made if remaining with current therapist is clinically contraindicated.
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
                                    <document-input
                                            name="reason_for_referral_for_couples"
                                            label="Reason for referral for couples counseling/Clinical rationale for referring to a different provider"
                                            size="col-md-12"
                                            v-model="form_data.reason_for_referral_for_couples"
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

                                <br/>
                                <div class="row ">
                                    <label class="font-weight-bold">Clinical Considerations before referring or initiating Couples Counseling (check mark those that are “yes”):</label>
                                </div>
                                <div class="row ">
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.initial_assessment_tridiuum_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Initial Assessment has been completed on Tridiuum
                                      </label>
                                  </div>
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.clinically_indicated_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Is it clinically indicated for spouse/family 
                                                   member to be included in session to achieve patient’s
                                                   goals?
                                      </label>
                                  </div>
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.individual_treatment_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Is individual treatment likely to continue? 
                                      </label>
                                  </div>
                                  <document-input
                                            name="explain_clinical_rationale"
                                            label="If so, explain clinical rationale for providing both individual and couples therapies:"
                                            size="col-md-12"
                                            v-model="form_data.explain_clinical_rationale"
                                    ></document-input>
                                  <div class="col-md-12 pt-3">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.collateral_session_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Has a collateral/family session been included in patient’s current episode of care? 
                                      </label>
                                  </div>
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.patient_and_spouse_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Do both Patient and Spouse agree to a Couples Counseling referral?
                                      </label>
                                  </div>
                                   <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.limited_workshop_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Would couple be interested in a time limited group workshop rather than counseling?
                                      </label>
                                  </div>
                                </div>

                                <br/>
                                <div class="row">
                                    <label class="font-weight-bold">If the answer is “Yes” to the following, then couples therapy is likely not indicated:</label>
                                </div>
                                <div class="row">
                                   <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.intimate_violence_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Is there Intimate Partner Violence -physical, verbal, emotional?
                                      </label>
                                  </div>
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.partners_unwilling_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Is there ongoing affair(s) that one or both partners are unwilling to end?
                                      </label>
                                  </div>
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.mental_health_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Is there Mental health disorder better addressed in individual therapy (i.e., bipolar disorder)?
                                      </label>
                                  </div>
                                  <div class="col-md-12">
                                      <label class="font-weight-normal">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.impairing_patients_ability_cb"
                                                   @change="setHasValue" 
                                                   :disabled="statuses.editingDisabled">
                                                   Current drug and alcohol use, impairing patients/spouse ability to function well
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
        document_name: 'kpep-couples-counseling-referral',
        document_title: 'KPEP Kaiser Permanente Couples Counseling Referral',
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
          reason_for_referral_for_couples: null,
          diagnosis_icd_code: null,
          selected_diagnoses: this.$store.state.currentPatient.diagnoses || [],
          bhi_score: null,  
          gad_score: null,  
          phq9_score: null, 
          cssrs_score: null,
          initial_assessment_tridiuum_cb:false,
          clinically_indicated_cb:false,
          individual_treatment_cb:false,
          explain_clinical_rationale:null,
          collateral_session_cb:false,
          patient_and_spouse_cb:false,
          limited_workshop_cb:false,
          intimate_violence_cb:false,
          partners_unwilling_cb:false,
          mental_health_cb:false,
          impairing_patients_ability_cb:false,
          provider_name: this.parseProviderName(this.currentProvider.provider_name),
          group_practice: null,
          provider_email: this.currentProvider.email,
          provider_phone: this.currentProvider.phone,
        }
      }
    },
    mounted() {
      let self = this;
      let menu_item_selector = 'kpep-couples-counseling-referral';
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