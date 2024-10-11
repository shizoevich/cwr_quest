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
                                <h5 class="modal-title">
                                    Email this form to:
                                    <a href="mailto:External-Referral-Team-STR@KP.ORG">External-Referral-Team-STR@KP.ORG</a>
                                </h5>
                                <br>
                                <h4 class="modal-title">
                                    <u><b>REQUEST FOR REFERRAL FOR RETURN PATIENTS</b></u>
                                </h4>
                                <p>
                                    This form is to be used when a Patient has been discharged and would like to return
                                    to treatment. <b>Reminder: If a Patient has stopped care with you for a period of 3
                                    weeks with no follow up appointment scheduled, you must submit a Discharge Summary
                                    as soon as possible.</b> If Patient would look to re-engage in treatment after a
                                    discharge, this form must be completed by the provider and submitted to the
                                    External Referral Team. <b>*NOTE: THIS FORM DOES NOT GUARANTEE A REFERRAL. IT WILL
                                    BE ASSESSED BY THE EXTERNAL TEAM. DO NOT SEE PATIENTS UNTIL YOU RECEIVE
                                    AUTHORIZATION FROM KP.</b>
                                </p>
                                <br>
                                <p>
                                    The Following Patient has contacted Provider requesting to re-engage in treatment:
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
                                    <document-textarea
                                            name="mrn"
                                            label="Patient MRN"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row  input-row">
                                    <div class="form-group col-md-4 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Date Patient Was Last Seen</label>
                                        <el-date-picker
                                                v-model="form_data.last_seen"
                                                type="date"
                                                name="last_seen"
                                                @focus="pickerFocus('last_seen')"
                                                @blur="pickerBlur('last_seen')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="resetDateError('last_seen')"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <document-textarea
                                            name="number_of_sessions"
                                            label="Total Number of Sessions Patient has Seen You Over Time"
                                            size="col-md-4"
                                            v-model="form_data.number_of_sessions"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="authorization_no"
                                            label="Last Authorization #"
                                            size="col-md-4"
                                            v-model="form_data.authorization_no"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <div class="row  input-row">
                                    <document-textarea
                                            name="number_of_sessions_used"
                                            label="Number of Sessions Used on Last Auth"
                                            size="col-md-6"
                                            v-model="form_data.number_of_sessions_used"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>

                                    <div class="form-group input-container col-md-6 col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Reason Patient Previously Ended Treatment
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="reason_ended_treatment_other_rb_1"
                                                       name="reason_ended_treatment_other_rb"
                                                       v-model="form_data.reason_ended_treatment_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="improved"
                                                       @change="setHasValue"
                                                >
                                                Improved, No longer clinically needed
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="reason_ended_treatment_other_rb_2"
                                                       name="reason_ended_treatment_other_rb"
                                                       v-model="form_data.reason_ended_treatment_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="other"
                                                       @change="setHasValue"
                                                >
                                                Other
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.reason_ended_treatment_rb == 'other'">
                                    <document-textarea
                                            name="reason_ended_treatment_explain"
                                            label="Explain"
                                            v-model="form_data.reason_ended_treatment_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row text-uppercase">
                                    <label>Reason Patient is seeking therapy at this time, Presenting problems, Current Symptoms/functional impairment(s)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="therapy_reason"
                                            v-model="form_data.therapy_reason"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row input-check-div">
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Psychiatric Hospitalizations/Emergency Dept. Visits Within the Past Year
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="psychiatric_hospitalization_rb_1"
                                                       name="psychiatric_hospitalization_rb"
                                                       v-model="form_data.psychiatric_hospitalization_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="no"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="psychiatric_hospitalization_rb_2"
                                                       name="psychiatric_hospitalization_rb"
                                                       v-model="form_data.psychiatric_hospitalization_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="yes"
                                                       @change="setHasValue"
                                                >
                                                Yes, Dates & Details
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.psychiatric_hospitalization_rb == 'yes'">
                                    <document-textarea
                                            name="psychiatric_hospitalization_explain"
                                            label="Dates & Details"
                                            v-model="form_data.psychiatric_hospitalization_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Suicidal Ideation
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="suicidal_ideation_rb_1"
                                                       name="suicidal_ideation_rb"
                                                       v-model="form_data.suicidal_ideation_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="no"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="suicidal_ideation_rb_2"
                                                       name="suicidal_ideation_rb"
                                                       v-model="form_data.suicidal_ideation_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="yes"
                                                       @change="setHasValue"
                                                >
                                                Yes, Explain
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.suicidal_ideation_rb == 'yes'">
                                    <document-textarea
                                            name="suicidal_ideation_explain"
                                            label="Explain"
                                            v-model="form_data.suicidal_ideation_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            History of Suicide Attempts
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="history_of_suicide_rb_1"
                                                       name="history_of_suicide_rb"
                                                       v-model="form_data.history_of_suicide_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="no"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="history_of_suicide_rb_2"
                                                       name="history_of_suicide_rb"
                                                       v-model="form_data.history_of_suicide_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="yes"
                                                       @change="setHasValue"
                                                >
                                                Yes, Dates & Details
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.history_of_suicide_rb == 'yes'">
                                    <document-textarea
                                            name="history_of_suicide_explain"
                                            label="Dates & Details"
                                            v-model="form_data.history_of_suicide_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Access to Firearms or Other Weapon(s)
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="access_to_firearms_rb_1"
                                                       name="access_to_firearms_rb"
                                                       v-model="form_data.access_to_firearms_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="no"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="access_to_firearms_rb_2"
                                                       name="access_to_firearms_rb"
                                                       v-model="form_data.access_to_firearms_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="yes"
                                                       @change="setHasValue"
                                                >
                                                Yes, Explain
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.access_to_firearms_rb == 'yes'">
                                    <document-textarea
                                            name="access_to_firearms_explain"
                                            label="Explain"
                                            v-model="form_data.access_to_firearms_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Homicidal Ideation
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="homicidal_ideation_rb_1"
                                                       name="homicidal_ideation_rb"
                                                       v-model="form_data.homicidal_ideation_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="no"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="homicidal_ideation_rb_2"
                                                       name="homicidal_ideation_rb"
                                                       v-model="form_data.homicidal_ideation_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="yes"
                                                       @change="setHasValue"
                                                >
                                                Yes, Explain
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.homicidal_ideation_rb == 'yes'">
                                    <document-textarea
                                            name="homicidal_ideation_explain"
                                            label="Explain"
                                            v-model="form_data.homicidal_ideation_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Hallucinations/Hearing Voices
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="hallucinations_rb_1"
                                                       name="hallucinations_rb"
                                                       v-model="form_data.hallucinations_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="no"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="hallucinations_rb_2"
                                                       name="hallucinations_rb"
                                                       v-model="form_data.hallucinations_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="yes"
                                                       @change="setHasValue"
                                                >
                                                Yes, Explain
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.hallucinations_rb == 'yes'">
                                    <document-textarea
                                            name="hallucinations_explain"
                                            label="Explain"
                                            v-model="form_data.hallucinations_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Any Other Safety Concerns or Risk Factors
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="risk_factors_rb_1"
                                                       name="risk_factors_rb"
                                                       v-model="form_data.risk_factors_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="no"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="risk_factors_rb_2"
                                                       name="risk_factors_rb"
                                                       v-model="form_data.risk_factors_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="yes"
                                                       @change="setHasValue"
                                                >
                                                Yes, Explain
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" v-show="form_data.risk_factors_rb == 'yes'">
                                    <document-textarea
                                            name="risk_factors_explain"
                                            label="Explain"
                                            v-model="form_data.risk_factors_explain"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>ADDITIONAL COMMENTS (optional)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="additional_comments"
                                            v-model="form_data.additional_comments"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br>
                                <div class="row">
                                    <div class="form-group input-container col-xs-12 radio-without-label">
                                        <div class="col-lg-12 checkbox">
                                            <label class="patient-appointment">
                                                <input type="checkbox"
                                                       name="patient_to_call"
                                                       v-model="form_data.contracted_provider_1_cb"
                                                       :disabled="statuses.editingDisabled"
                                                       @change="setHasValue"
                                                >
                                                As a KP contracted External Provider, I have assessed this Patient and
                                                I feel Patient is appropriate for a referral for weekly treatment working
                                                through the solution focused, goal-oriented treatment model. I have
                                                discussed with Patient that this referral is for weekly treatment and
                                                have discussed consistency and the process of therapy.  I have discussed
                                                scheduling with Patient and agree to accept the referral and can
                                                accommodate Patient’s schedule.
                                                I reviewed standard safety measures and advised: In case of emergency-
                                                please call 911 or go to the nearest hospital Emergency Room. Patient
                                                was also advised to call the Behavioral Health Help Line at
                                                1-800-900-3277, if he/she needs clinical support when the clinic is
                                                closed. Patient was told that therapists are available 24/7 to listen
                                                and to help.
                                            </label>
                                        </div>
                                        <div class="col-lg-12 checkbox">
                                            <label class="patient-appointment">
                                                <input type="checkbox"
                                                       name="patient_to_call"
                                                       v-model="form_data.contracted_provider_2_cb"
                                                       :disabled="statuses.editingDisabled"
                                                       @change="setHasValue"
                                                >
                                                As a KP contracted External Provider, I have assessed this patient and
                                                recommend Patient be referred back to KP clinic at this time as Patient
                                                is not appropriate for weekly therapy through solution focused,
                                                goal-oriented model, and would better benefit from services within
                                                clinic. Patient has been notified that he/she will be contacted to be
                                                scheduled with therapist in clinic.
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>PROVIDER'S INFORMATION</label>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="provider_name"
                                            label="Provider Name"
                                            size="col-md-6"
                                            v-model="form_data.provider_name"
                                            :disabled="true"
                                    ></document-textarea>
                                    <document-textarea
                                            name="provider_practice_name"
                                            label="Group Practice (if applicable)"
                                            size="col-md-6"
                                            v-model="form_data.provider_practice_name"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
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

    export default {
        mixins: [validate, save, methods],
        data(){
            return{
                document_name: 'kp-behavioral-health-pc',
                document_title: 'KP Panorama City Behavioral Health',
                signature: null
            }
        },
        beforeMount(){
            if(!this.form_data && this.$store.state.currentDocument){
                this.patient_id = this.$store.state.currentPatient.id;
                this.form_data = {
                    date: this.formatDate(new Date(), this.momentDateFormat),
                    first_name: this.$store.state.currentPatient.first_name,
                    last_name: this.$store.state.currentPatient.last_name,
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_practice_name: null,
                    mrn: null,
                    last_seen: null,
                    number_of_sessions: null,
                    authorization_no: null,
                    number_of_sessions_used: null,
                    reason_ended_treatment_rb: null,
                    reason_ended_treatment_explain: null,
                    therapy_reason: null,
                    psychiatric_hospitalization_rb: null,
                    psychiatric_hospitalization_explain: null,
                    suicidal_ideation_rb: null,
                    suicidal_ideation_explain: null,
                    history_of_suicide_rb: null,
                    history_of_suicide_explain: null,
                    access_to_firearms_rb: null,
                    access_to_firearms_explain: null,
                    homicidal_ideation_rb: null,
                    homicidal_ideation_explain: null,
                    hallucinations_rb: null,
                    hallucinations_explain: null,
                    risk_factors_rb: null,
                    risk_factors_explain: null,
                    additional_comments: null,
                    contracted_provider_1_cb: null,
                    contracted_provider_2_cb: null,
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp-behavioral-health-pc';
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

                return error;
            },
            setHasValue(){
                this.statuses.hasValue = true;
            },
        },
        watch: {
            'form_data.reason_ended_treatment_rb': function(newValue, oldValue){
                if(newValue != 'other'){
                    this.form_data.reason_ended_treatment_explain = null;
                }
            },
            'form_data.psychiatric_hospitalization_rb': function(newValue, oldValue){
                if(newValue != 'yes'){
                    this.form_data.psychiatric_hospitalization_explain = null;
                }
            },
            'form_data.suicidal_ideation_rb': function(newValue, oldValue){
                if(newValue != 'yes'){
                    this.form_data.suicidal_ideation_explain = null;
                }
            },
            'form_data.history_of_suicide_rb': function(newValue, oldValue){
                if(newValue != 'yes'){
                    this.form_data.history_of_suicide_explain = null;
                }
            },
            'form_data.access_to_firearms_rb': function(newValue, oldValue){
                if(newValue != 'yes'){
                    this.form_data.access_to_firearms_explain = null;
                }
            },
            'form_data.homicidal_ideation_rb': function(newValue, oldValue){
                if(newValue != 'yes'){
                    this.form_data.homicidal_ideation_explain = null;
                }
            },
            'form_data.hallucinations_rb': function(newValue, oldValue){
                if(newValue != 'yes'){
                    this.form_data.hallucinations_explain = null;
                }
            },
            'form_data.risk_factors_rb': function(newValue, oldValue){
                if(newValue != 'yes'){
                    this.form_data.risk_factors_explain = null;
                }
            }
        }
    }
</script>
