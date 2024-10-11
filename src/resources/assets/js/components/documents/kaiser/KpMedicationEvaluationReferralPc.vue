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
                                    Kaiser Permanente Medication Management Referral
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    Encrypt & email this form to:
                                    <a href="mailto:external-referral-team-str@kp.org">external-referral-team-str@kp.org</a>
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
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Date of Birth</label>
                                        <el-date-picker
                                                v-model="form_data.birth_date"
                                                type="date"
                                                name="birth_date"
                                                @focus="pickerFocus('birth_date')"
                                                @blur="pickerBlur('birth_date')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="resetDateError('birth_date')"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <document-input
                                            name="age"
                                            label="Age"
                                            size="col-md-4"
                                            v-model="form_data.age"
                                            :disabled="true"
                                    ></document-input>
                                    <div class="form-group col-md-4 input-container input-container-diagnosis">
                                        <label class="control-label">Diagnosis</label>
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
                                            name="contact_info"
                                            label="Contact Information"
                                            size="col-xs-12"
                                            v-model="form_data.contact_info"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="reason_for_referral"
                                            label="Reason for referral/Major Presenting Problems"
                                            size="col-xs-12"
                                            v-model="form_data.reason_for_referral"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                            name="other_info"
                                            label="Any other pertinent information"
                                            size="col-xs-12"
                                            v-model="form_data.other_info"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="group_practice"
                                            label="Group Practice (if applicable)"
                                            size="col-xs-12"
                                            v-model="form_data.group_practice"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>


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
        document_name: 'kp-medication-evaluation-referral-pc',
        document_title: 'Medication Evaluation Referral - Kaiser Panorama City',
        statuses: {
          confirm_diagnoses: false,
        }
      }
    },
    beforeMount(){
      if(!this.form_data && this.$store.state.currentDocument){
        this.patient_id = this.$store.state.currentPatient.id;
        let birthDate = this.$store.state.currentPatient.date_of_birth;
        this.form_data = {
          first_name: this.$store.state.currentPatient.first_name,
          last_name: this.$store.state.currentPatient.last_name,
          birth_date: birthDate,
          date: this.formatDate(new Date(), this.momentDateFormat),
          mrn: null,
          provider_name: this.parseProviderName(this.currentProvider.provider_name),
          provider_license_no: this.currentProvider.license_no,
          provider_email: this.currentProvider.email,
          provider_phone: this.currentProvider.phone,
          diagnosis_icd_code: null,
          age: birthDate ? ('' + moment().diff(birthDate, 'years')) : null,
          reason_for_referral: null,
          other_info: null,
          group_practice: null,
          selected_diagnoses: this.$store.state.currentPatient.diagnoses || []
        }
      }
    },
    mounted() {
      let self = this;
      let menu_item_selector = 'kp-medication-evaluation-referral-pc';
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
      setConcernsCbIfChildEnabled() {
        this.setHasValue();
        this.form_data.concerns_include_cb = this.form_data.needs_adhd_cb || this.form_data.presents_with_depression_cb || this.form_data.has_failed_pcp_cb;
      },
      setConcernsChildsIfDisabled() {
        this.setHasValue();
        if(this.form_data.concerns_include_cb === false) {
          this.form_data.needs_adhd_cb = false;
          this.form_data.presents_with_depression_cb = false;
          this.form_data.has_failed_pcp_cb = false;
        }
      }
    },
  }
</script>