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
                                    Email form to
                                    <a href="mailto:External-Referral-Team-STR@KP.ORG">External-Referral-Team-STR@KP.ORG</a>
                                    or Fax to 818-758-1361
                                </h5>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    Clinical Documentation
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document" novalidate>

                                <div class="row  input-row">
                                    <document-input
                                            name="first_name"
                                            label="Firstname"
                                            size="col-md-4"
                                            v-model="form_data.first_name"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="last_name"
                                            label="Lastname"
                                            size="col-md-4"
                                            v-model="form_data.last_name"
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

                                <div class="row">
                                    <!--<document-textarea-->
                                            <!--name="request_reason_rb"-->
                                            <!--label="Reason for Reauth Request"-->
                                            <!--size="col-md-6"-->
                                            <!--v-model="form_data.request_reason_rb"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-textarea>-->
                                    <div class="form-group input-container col-md-6 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Reason for Reauth Request
                                        </label>
                                        <div class="col-md-7 col-sm-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="request_reason_rb_1"
                                                       name="request_reason_rb"
                                                       v-model="form_data.request_reason_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="Time expired"
                                                       @change="setHasValue"
                                                >
                                                Time Period Expired (6 Months)
                                            </label>
                                        </div>
                                        <div class="col-md-5 col-sm-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="request_reason_rb_2"
                                                       name="request_reason_rb"
                                                       v-model="form_data.request_reason_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="Number expired"
                                                       @change="setHasValue"
                                                >
                                                # of Visits Expired
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 input-container input-container-diagnosis">
                                        <label class="control-label">Diagnosis and ICD code</label>
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



                                <div class="row input-row diagnosis-row">
                                    <!--<document-textarea-->
                                            <!--name="continued_treatment"-->
                                            <!--label="Recommend Continued Treatment"-->
                                            <!--size="col-md-6"-->
                                            <!--v-model="form_data.continued_treatment"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-textarea>-->
                                    <div class="form-group input-container col-md-6 radio-without-label">
                                        <div class="col-lg-12 checkbox">
                                            <label>
                                                <input type="checkbox"
                                                       name="continued_treatment_cb"
                                                       v-model="form_data.continued_treatment_cb"
                                                       :disabled="statuses.editingDisabled"
                                                       @change="setHasValue"
                                                >
                                                Recommend Continued Treatment
                                            </label>
                                        </div>
                                    </div>
                                    <document-textarea
                                            name="reason_for_referral"
                                            label="Total # of Sessions for this Patient Since Beginning of Treatment With You"
                                            size="col-md-6"
                                            v-model="form_data.number_of_sessions"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <!--<div class="row ">-->
                                    <!--<div class="form-group input-container stand-alone">-->
                                        <!--<label class="control-label input-label">-->
                                            <!--<b>Date of Service for Current Auth #</b>-->
                                        <!--</label>-->
                                    <!--</div>-->
                                <!--</div>-->
                                <br/>
                                <div class="row ">
                                    <label>DATE OF SERVICE FOR CURRENT AUTH #</label>
                                </div>
                                <div class="row diagnosis-row">
                                    <!--<div class="form-group col-md-6 input-container document-date"-->
                                         <!--:class="{'has-error': errors.has('date_of_service'), 'div-disabled': statuses.editingDisabled}"-->
                                    <!--&gt;-->
                                        <!--<label class="control-label input-label">Date of Service for Current Auth</label>-->
                                        <!--<el-date-picker-->
                                                <!--v-model="form_data.date_of_service"-->
                                                <!--type="date"-->
                                                <!--name="date_of_service"-->
                                                <!--@focus="pickerFocus('date_of_service')"-->
                                                <!--@blur="pickerBlur('date_of_service')"-->
                                                <!--:editable="false"-->
                                                <!--format="MM/dd/yyyy"-->
                                                <!--@change="resetDateError('date_of_service')"-->
                                                <!--:disabled="statuses.editingDisabled"-->
                                        <!--&gt;-->
                                        <!--</el-date-picker>-->
                                    <!--</div>-->
                                    <!--<date-of-service-->
                                            <!--:editingDisabled="statuses.editingDisabled"-->
                                            <!--:patient="Boolean(patient)"-->
                                            <!--label="Date of Service for Current Auth"-->
                                            <!--size="col-md-6"-->
                                    <!--&gt;</date-of-service>-->
                                    <div class="form-group col-lg-12 input-container document-date"
                                         :class="{'has-error': errors.has('date_of_service'), 'div-disabled': statuses.editingDisabled}"
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
                                </div>
                                
                                <div class="row  input-row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('date_of_service'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #1</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_1"
                                                type="date"
                                                name="session_1"
                                                @focus="pickerFocus('session_1')"
                                                @blur="pickerBlur('session_1')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_7'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #7</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_7"
                                                type="date"
                                                name="session_7"
                                                @focus="pickerFocus('session_7')"
                                                @blur="pickerBlur('session_7')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>
                                <div class="row  input-row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('date_of_service'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #2</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_2"
                                                type="date"
                                                name="session_2"
                                                @focus="pickerFocus('session_2')"
                                                @blur="pickerBlur('session_2')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_8'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #8</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_8"
                                                type="date"
                                                name="session_8"
                                                @focus="pickerFocus('session_8')"
                                                @blur="pickerBlur('session_8')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>
                                <div class="row  input-row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_3'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #3</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_3"
                                                type="date"
                                                name="session_3"
                                                @focus="pickerFocus('session_3')"
                                                @blur="pickerBlur('session_3')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_9'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #9</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_9"
                                                type="date"
                                                name="session_9"
                                                @focus="pickerFocus('session_9')"
                                                @blur="pickerBlur('session_9')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>
                                <div class="row  input-row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_4'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #4</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_4"
                                                type="date"
                                                name="session_4"
                                                @focus="pickerFocus('session_4')"
                                                @blur="pickerBlur('session_4')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_10'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #10</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_10"
                                                type="date"
                                                name="session_10"
                                                @focus="pickerFocus('session_10')"
                                                @blur="pickerBlur('session_10')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>
                                <div class="row  input-row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_5'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #5</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_5"
                                                type="date"
                                                name="session_5"
                                                @focus="pickerFocus('session_5')"
                                                @blur="pickerBlur('session_5')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_11'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #11</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_11"
                                                type="date"
                                                name="session_11"
                                                @focus="pickerFocus('session_11')"
                                                @blur="pickerBlur('session_11')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>
                                <div class="row  input-row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_6'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #6</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_6"
                                                type="date"
                                                name="session_6"
                                                @focus="pickerFocus('session_6')"
                                                @blur="pickerBlur('session_6')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('session_12'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Session #12</label>
                                        <el-date-picker
                                                v-model="form_data.sessions.session_12"
                                                type="date"
                                                name="session_12"
                                                @focus="pickerFocus('session_12')"
                                                @blur="pickerBlur('session_12')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="sessionChanged()"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="presenting_problem"
                                            label="Presenting problems, symptoms/functional impairment (s)"
                                            v-model="form_data.presenting_problem"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="risk_factors"
                                            label="Describe and clarify risk factors, if any (include and <u>explain</u> harm
                                                   to self, harm to others, risk for decompensation or regression)"
                                            v-model="form_data.risk_factors"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"

                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="treatment_goals"
                                            label="Treatment goal (s)"
                                            v-model="form_data.treatment_goals"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"

                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="measurable"
                                            label="Describe measurable clinical/behavioral progress towards treatment goals"
                                            v-model="form_data.measurable"
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

                                <div class="row">
                                    <document-textarea
                                            name="clinical_recommendations"
                                            label="Clinical recommendations for adjunct services (medication evaluation,
                                                   group therapy or workshop assessment, higher level of care assessment)
                                                   – please be specific"
                                            v-model="form_data.clinical_recommendations"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"

                                    ></document-textarea>
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
                document_name: 'kp-request-for-reauthorization-pc',
                document_title: 'KP Request for Reauthorization - Panorama City',
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
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_title: this.parseProviderTitle(this.currentProvider.provider_name),
                    provider_license_no: this.currentProvider.license_no,
                    mrn: null,
                    diagnosis_icd_code: null,
                    number_of_sessions: null,
                    continued_treatment_cb: null,
                    request_reason_rb: null,
                    presenting_problem: null,
                    risk_factors: null,
                    treatment_goals: null,
                    measurable: null,
                    patient_participation: {
                        participates_actively_cb: null,
                        poor_compliance_cb: null,
                        moderately_invested_cb: null,
                        other_cb: null,
                        explain: null,
                    },
                    clinical_recommendations: null,
//                    date_of_service: null,
                    intake_date: null,
                    sessions: {
                        session_1: null,
                        session_2: null,
                        session_3: null,
                        session_4: null,
                        session_5: null,
                        session_6: null,
                        session_7: null,
                        session_8: null,
                        session_9: null,
                        session_10: null,
                        session_11: null,
                        session_12: null,
                    },
                  selected_diagnoses: this.$store.state.currentPatient.diagnoses || []
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp-request-for-reauthorization-pc';
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

                    self.initDateOfService();

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
            resetSessionsValidation(){

                for(let session in this.form_data.sessions){

                    this.removeDateError(session);
                }
            },
            getSessionValidation(){

                this.resetSessionsValidation();

                let sessions = this.form_data.sessions;
                let sessionsSize = Object.keys(sessions).length;
                let error = false;

                while(sessionsSize > 1){

                    if(sessions['session_'+sessionsSize] && !sessions['session_'+(sessionsSize-1)]){

                        this.validation_message = 'Please fill "Session" fields in series';

                        while(sessionsSize > 0){

                            if(!sessions['session_'+sessionsSize]){

                                this.setDateError('session_'+sessionsSize);
                            }

                            sessionsSize --;
                        }

                        error = true;
                        break;
                    }

                    sessionsSize --;
                }

                if(!error){

                    this.validation_message = null;
                }

                return error;
            },
            sessionChanged(){

              this.setHasValue();
//              this.getSessionValidation();
            },
            clearExplain(){
                this.form_data.patient_participation.explain = null;
            }
        },
        computed:{
            showExplain(){

                for(let item in this.form_data.patient_participation){

                    if(this.form_data.patient_participation[item] && item != "explain"){

                        return true;
                    }
                }

                this.clearExplain();
                return false;
            },
        }
    }
</script>
