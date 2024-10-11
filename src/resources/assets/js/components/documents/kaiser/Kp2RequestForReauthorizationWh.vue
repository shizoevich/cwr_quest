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
                                <p>
                                    Woodland Hills/West Ventura Service Area
                                </p>
                            </div>
                            <div class="col-lg-12">
                                <h4 class="modal-title">
                                    REQUEST FOR REAUTHORIZATION
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    At second & all subsequent renewals, encrypt & email this form to:
                                    <a href="mailto:wh-outsidemedicalcase-management@kp.org">wh-outsidemedicalcase-management@kp.org</a>
                                </p>
                                <p>
                                    **FORM IS DUE <u>7 DAYS</u> PRIOR TO EXPIRATION OF CURRENT AUTHORIZATON**
                                </p>
                                <p>
                                    **Incomplete forms will be returned ** No retroactive authorization will be issued**
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document" novalidate>

                                <div class="row">
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
                                            label="Patient MRN"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-input>
                                </div>


                                <div class="row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Patient DOB</label>
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
                                    <div class="form-group col-md-6 input-container input-container-diagnosis">
                                        <label class="control-label diagnosis-label">Diagnosis and ICD code</label>
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


                                <div class="row">
                                    <document-textarea
                                            name="authorization_no"
                                            label="Authorization #"
                                            size="col-md-6"
                                            v-model="form_data.authorization_no"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <div class="form-group input-container col-md-6 radio-without-label radio-recommend-treatment">
                                        <div class="col-lg-12 radio">
                                            <label>
                                                <input type="radio"
                                                       id="continued_treatment_rb_1"
                                                       name="continued_treatment_rb"
                                                       v-model="form_data.continued_treatment_rb"
                                                       value="Continued"
                                                       @change="setHasValue"
                                                >
                                                Recommend Continued Treatment
                                            </label>
                                        </div>
                                        <div class="col-lg-12 radio">
                                            <label>
                                                <input type="radio"
                                                       id="continued_treatment_rb_2"
                                                       name="continued_treatment_rb"
                                                       v-model="form_data.continued_treatment_rb"
                                                       :disabled="true"
                                                       value="Discharge"
                                                       @change="setHasValue"
                                                >
                                                Recommend Discharge
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Intake Date</label>
                                        <el-date-picker
                                                v-model="form_data.intake_date"
                                                type="date"
                                                name="intake_date"
                                                @focus="pickerFocus('intake_date')"
                                                @blur="pickerBlur('intake_date')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="resetDateError('intake_date')"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <document-textarea
                                            name="no_of_sessions"
                                            label="Total # of sessions since intake"
                                            size="col-md-6"
                                            v-model="form_data.no_of_sessions"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="previous_episode"
                                            label="Was there a previous episode with you?"
                                            v-model="form_data.previous_episode"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="dates_of_service"
                                            label="Dates of service for <u>current auth # period</u>, please list"
                                            v-model="form_data.dates_of_service"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>OUTSIDE REFERRAL DEPT UPDATE</label>
                                </div>
                                <div class="row">
                                    <table class="table table-bordered document-table">
                                        <tbody>
                                        <tr>
                                            <td scope="row" class="param-td-title">
                                                <document-textarea
                                                        name="primary_coverage"
                                                        label="Primary coverage is Medi-Cal"
                                                        v-model="form_data.primary_coverage"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </td>
                                            <td class="param-td">
                                                <select class="dropdown-form-control"
                                                        @change="setHasValue"
                                                        v-model="form_data.primary_coverage_select"
                                                        :disabled="statuses.editingDisabled"
                                                >
                                                    <option value="None"></option>
                                                    <option>Yes</option>
                                                    <option>No</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td scope="row" class="param-td-title">
                                                <document-textarea
                                                        name="diagnosis_has_been_changed"
                                                        label="Diagnosis has been changed and needs to be updated in HealthConnect"
                                                        v-model="form_data.diagnosis_has_been_changed"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </td>
                                            <td class="param-td">
                                                <select class="dropdown-form-control"
                                                        @change="setHasValue"
                                                        v-model="form_data.diagnosis_has_been_changed_select"
                                                        :disabled="statuses.editingDisabled"
                                                >
                                                    <option value="None"></option>
                                                    <option>Yes</option>
                                                    <option>No</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td scope="row" class="param-td-title">
                                                <document-textarea
                                                        name="actively_integrated"
                                                        label="TPI is actively integrated into treatment"
                                                        v-model="form_data.actively_integrated"
                                                        @change="setHasValue"
                                                        :disabled="statuses.editingDisabled"
                                                ></document-textarea>
                                            </td>
                                            <td class="param-td">
                                                <select class="dropdown-form-control"
                                                        @change="setHasValue"
                                                        v-model="form_data.actively_integrated_select"
                                                        :disabled="statuses.editingDisabled"
                                                >
                                                    <option value="None"></option>
                                                    <option>Yes</option>
                                                    <option>No</option>
                                                </select>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <br/>
                                <div class="row">
                                    <label>Progress Summary</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="clinical_attention"
                                            label="Focus of Clinical Attention for the most recent authorization"
                                            v-model="form_data.clinical_attention"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="measurable_progress"
                                            label="Describe measurable clinical/behavioral progress for sessions
                                                   completed; what evidence do you and the patient observe?
                                                   (include summary of TPI results such as changes in BHI,
                                                   PHQ, GAD, and Therapeutic Alliance)"
                                            v-model="form_data.measurable_progress"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label">Patient participation in treatment</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.patient_participation.participates_actively_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Participates Actively
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.patient_participation.moderately_invested_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Moderately Invested
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.patient_participation.poor_compliance_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Poor Compliance
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.patient_participation.other_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Other
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="showExplain">
                                    <document-textarea
                                            name="explain"
                                            label="Explain"
                                            v-model="form_data.patient_participation.explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row">
                                    <label>Current Status</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="clinical_symptoms"
                                            label="Observed and reported <u><b>clinical symptoms</b></u> (reference DSM V) in last 2-4
                                                   weeks (specify acuity level of symptoms e.g., mild, moderate, severe).
                                                   List should support diagnosis with specifiers. Recent TPI scores may
                                                   support this section as well."
                                            v-model="form_data.clinical_symptoms"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="level_of_functioning"
                                            label="Describe level of <b>functioning in every day life</b> (including but not
                                                   limited to activities of daily living: grooming, hygiene, eating,
                                                   sleeping; ability to manage various roles: family,
                                                   friend, employee, community member; ability to work
                                                   or function in school; etc). Compare to the normal,
                                                   functional baseline for this patient"
                                            v-model="form_data.level_of_functioning"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="with_continued_treatment"
                                            label="With continued treatment, how likely is it that the presenting
                                                   mental health condition will improve? Why or why not?
                                                   Compare to TPI trajectory, consult as needed with
                                                   Clinical Liaison"
                                            v-model="form_data.with_continued_treatment"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="risk_factors"
                                            label="Describe and clarify <b>risk factors</b> if any (include and <u>explain</u> harm
                                                   to self, harm to others, risk for decompensation or regression)"
                                            v-model="form_data.risk_factors"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>



                                <br/>
                                <div class="row">
                                    <label>TREATMENT PLAN - CREATE <u>WITH</u> PATIENT</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="agreed_upon"
                                            label="2-3 agreed upon SMART goal(s) going forward:
                                                   Specific, Measurable, Attainable, Realistic, Time-Limited"
                                            v-model="form_data.agreed_upon"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="specific_actions"
                                            label="In the patient’s words: <u>Specific actions</u> patient agrees to take to
                                                   make progress towards goals"
                                            v-model="form_data.specific_actions"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="additional_recommended"
                                            label="Additional Kaiser services recommended (medication evaluation, group
                                                   therapy or workshop assessment, higher level of care
                                                   assessment) – please be specific"
                                            v-model="form_data.additional_recommended"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
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
    import DiagnosesMultiselect from './../../../mixins/diagnoses-multiselect';


    export default {
        mixins: [validate, save, methods, DiagnosesMultiselect],
        data(){
            return{
                document_name: 'kp1-request-for-reauthorization-wh',
                document_title: 'KP 1st Request for Reauthorization - Woodland Hills',
                emptySelect: 'None',
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
                    birth_date: this.$store.state.currentPatient.date_of_birth,
                    diagnosis_icd_code: null,
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_title: this.parseProviderTitle(this.currentProvider.provider_name),
                    provider_license_no: this.currentProvider.license_no,
                    provider_email: this.currentProvider.email,
                    provider_phone: this.currentProvider.phone,
                    authorization_no: null,
                    continued_treatment_rb: "Continued",
                    no_of_sessions: null,
                    previous_episode: null,
                    dates_of_service: null,
                    primary_coverage: null,
                    diagnosis_has_been_changed: null,
                    actively_integrated: null,
                    patient_participation: {
                        participates_actively_cb: null,
                        poor_compliance_cb: null,
                        moderately_invested_cb: null,
                        other_cb: null,
                        explain: null,
                    },
                    clinical_symptoms: null,
                    clinical_symptoms_select: this.emptySelect,
                    primary_coverage_select: this.emptySelect,
                    diagnosis_has_been_changed_select: this.emptySelect,
                    actively_integrated_select: this.emptySelect,
                    clinical_attention: null,
                    measurable_progress: null,
                    level_of_functioning: null,
                    with_continued_treatment: null,
                    risk_factors: null,
                    agreed_upon: null,
                    additional_recommended: null,
                    intake_date: null,
                    specific_actions: null,
                    mrn: null,
                   selected_diagnoses: this.$store.state.currentPatient.diagnoses || []
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp2-request-for-reauthorization-wh';
            let document_name = self.getFormName(menu_item_selector);
            self.document_name = document_name;
            self.document_title = self.getFormTitle(menu_item_selector);

            window.setTimeout(() => {
                $('#'+self.document_name).on('shown.bs.modal', function() {
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
        computed: {
           showExplain(){

               for(let item in this.form_data.patient_participation){

                   if(this.form_data.patient_participation[item] && item != "explain"){

                       return true;
                   }
               }

               this.clearExplain();
               return false;
           }
        },
        methods: {
            clearExplain(){
                this.form_data.patient_participation.explain = null;
            },
            getCustomValidation(){

                let error = false;
                if(!this.c_diagnoses_editing_disabled && !this.statuses.confirm_diagnoses) {
                  $('#ia-confirm-diagnoses label').addClass('text-red');
                  error = true;
                }

                return error;
            },
            getValidationMessage(){
                for (let child in this.$children) {

                    if(this.$children[child].name == 'provider_email'){

                        if(this.$children[child].errors.has('provider_email')){

                            if(this.$children[child].errors.items[0].rule == 'email'){

                                this.validation_message = 'Invalid email format. (Example example@example.com)';
                                break;
                            }
                        }
                    }
                    else if(this.$children[child].name == 'provider_phone'){

                        if(this.$children[child].errors.has('provider_phone')){

                            if(this.$children[child].errors.items[0].rule == 'regex'){

                                this.validation_message = 'Invalid phone format. (Example: 111-111-1111)';
                                break;
                            }
                        }
                    }
                }
            }
        },
    }
</script>
