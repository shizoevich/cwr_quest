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
                            <div class="col-xs-6 text-left">
                                Date: {{getFormattedDate()}}
                            </div>
                            <div class="col-xs-6 text-right">
                                CPT: 90791
                            </div>
                            <div class="col-lg-12">
                                <h4 class="modal-title">
                                    INITIAL DIAGNOSTIC INTERVIEW
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <h5 class="modal-title">
                                    Send to
                                    <a href="mailto:External-Referral-Team-STR@kp.org">External-Referral-Team-STR@kp.org</a>
                                    or (818) 758-1361
                                </h5>
                                <p>
                                    Patient information reviewed and signed which includes confidentiality/exceptions
                                    to confidentiality. Informed consent given. Release of information for KP signed.
                                    Emergency protocols discussed. KP Behavioral Healthcare Line given 800-900-3277.
                                    KP Clinic number given 800-700-8705.
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
                                    <!--<div class="form-group col-md-4 input-container document-date"-->
                                         <!--:class="{'has-error': errors.has('date_of_service'), 'div-disabled': statuses.editingDisabled}"-->
                                    <!--&gt;-->
                                        <!--<label class="control-label input-label">Date of Service</label>-->
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
                                    <date-of-service
                                            :editingDisabled="statuses.editingDisabled || !!this.$store.state.currentDocumentData"
                                            :patient="Boolean(patient)"
                                    ></date-of-service>
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
                                    <div class="form-group col-md-4 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label z-index-30">Date of Birth</label>
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
                                    <document-textarea
                                            name="sex"
                                            label="Sex (at birth)"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.sex"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="mrn"
                                            label="Kaiser MRN"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row  input-row">
                                    <document-textarea
                                            name="length_of_session"
                                            label="Length of Session"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.length_of_session"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="individuals_present"
                                            label="Individuals Present"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.individuals_present"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="occupation"
                                            label="Occupation"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.occupation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="other_sources"
                                            label="Other Sources of Information"
                                            labelClass="z-index-30"
                                            v-model="form_data.other_sources"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>PRESENTING PROBLEM (reason for seeking MH treatment, recent losses/changes/stressors)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="presenting_problem"
                                            v-model="form_data.presenting_problem"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>SYMPTOMS/BEHAVIORS (frequency, duration)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="symptoms_behaviors"
                                            v-model="form_data.symptoms_behaviors"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>ONSET OF SYMPTOMS/BEHAVIORS</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="onset_symptoms_behaviors"
                                            v-model="form_data.onset_symptoms_behaviors"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>RISK ASSESSMENT</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="self_harming"
                                            label="Self-Harming Behaviors"
                                            labelClass="z-index-30"
                                            v-model="form_data.self_harming"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="current_suicidal"
                                            label="Current Suicidal/Homicidal Ideation, Plan or Intent"
                                            labelClass="z-index-30"
                                            v-model="form_data.current_suicidal"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="histore_self_harming"
                                            label="History of Self-Harming Behaviors or SI/SA HI/HA"
                                            labelClass="z-index-30"
                                            v-model="form_data.histore_self_harming"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>CURRENT OR HISTORY OF TRAUMATIC EVENTS</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="history_of_traumatic"
                                            v-model="form_data.history_of_traumatic"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>BEHAVIORAL HEALTH HISTORY (previous tx, medication hx, psych hospitalizations, addiction treatment)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="behavioral_health_history"
                                            v-model="form_data.behavioral_health_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>FAMILY BEHAVIORAL HEALTH HISTORY (mental health disorder, substance use history)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="behavioral_family_health_history"
                                            v-model="form_data.behavioral_family_health_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row">
                                    <label>SUBSTANCE USE - Alcohol</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="alcohol_amount"
                                            label="Amount"
                                            labelClass="z-index-30"
                                            v-model="form_data.alcohol_amount"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="alcohol_frequency"
                                            label="Frequency"
                                            labelClass="z-index-30"
                                            v-model="form_data.alcohol_frequency"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="alcohol_history"
                                            label="History"
                                            labelClass="z-index-30"
                                            v-model="form_data.alcohol_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>SUBSTANCE USE - Drugs</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="drug_amount"
                                            label="Amount"
                                            labelClass="z-index-30"
                                            v-model="form_data.drug_amount"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="drug_frequency"
                                            label="Frequency"
                                            labelClass="z-index-30"
                                            v-model="form_data.drug_frequency"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="drug_history"
                                            label="History"
                                            labelClass="z-index-30"
                                            v-model="form_data.drug_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row">
                                    <label>SOCIAL HISTORY/SUPPORT SYSTEM</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="family_of_origin"
                                            label="Family of Origin"
                                            labelClass="z-index-30"
                                            v-model="form_data.family_of_origin"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="relationship_history"
                                            label="Relationship History"
                                            labelClass="z-index-30"
                                            v-model="form_data.relationship_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="initiates_sustains"
                                            label="Initiates & Sustains Friendships Easily"
                                            labelClass="z-index-30"
                                            v-model="form_data.initiates_sustains"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="social_support"
                                            label="Social Support"
                                            labelClass="z-index-30"
                                            v-model="form_data.social_support"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="living_arrangements"
                                            label="Living Arrangements (who does patient live with, partner, roommate(s), etc.)"
                                            labelClass="z-index-30"
                                            v-model="form_data.living_arrangements"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>PATIENT MEDICAL HISTORY (medical problems/chronic conditions/allergies)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="patient_medical_history"
                                            v-model="form_data.patient_medical_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>BARRIERS TO CARE (cultural or religious, transportation)</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="barries_to_care"
                                            v-model="form_data.barries_to_care"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>MENTAL STATUS EXAM</label>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="appearance"
                                            label="Appearance"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.appearance"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="behavior"
                                            label="Behavior"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.behavior"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="impairment_memory"
                                            label="Impairment in Cognition or Memory"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.impairment_memory"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="eye_contact"
                                            label="Eye Contact"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.eye_contact"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="speech"
                                            label="Speech"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.speech"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="mood"
                                            label="Mood"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.mood"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="affect"
                                            label="Affect"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.affect"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="stream_of_thought"
                                            label="Stream of Thought"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.stream_of_thought"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="impulse_control"
                                            label="Impulse Control"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.impulse_control"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="judgment"
                                            label="Judgment"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.judgment"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="insight"
                                            label="Insight"
                                            labelClass="z-index-30"
                                            v-model="form_data.insight"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row">
                                    <div class="col-xs-12 input-container radio-without-label form-group">
                                        <div class="col-xs-4" v-for="option in this.functional_status_options">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.functional_status_rb"
                                                       :value="option.value"
                                                       name="visit_purpose_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                {{ option.label }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>FUNCTIONAL STATUS</label>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="significant_impairment"
                                            label="Does the patient have a significant impairment in an important area
                                                    of life functioning?"
                                            labelClass="z-index-30"
                                            v-model="form_data.significant_impairment"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row ">
                                    <div class="form-group input-container stand-alone">
                                        <label class="control-label input-label z-index-30">
                                            In the past 3 months, how impaired has the patient been in the following areas of:
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="appropriate_self_care"
                                            label="1. Age appropriate self-care"
                                            labelClass="z-index-30"
                                            v-model="form_data.appropriate_self_care"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="interpersonal_relationships"
                                            label="2. Interpersonal relationships (i.e. friends, peers, etc)"
                                            labelClass="z-index-30"
                                            v-model="form_data.interpersonal_relationships"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="work_tasks"
                                            label="3. Work/school tasks?"
                                            labelClass="z-index-30"
                                            v-model="form_data.work_tasks"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="social_activities"
                                            label="4. Participation in usual social/community activities?"
                                            labelClass="z-index-30"
                                            v-model="form_data.social_activities"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="significant_deterioration"
                                            label="Significant deterioration: please note if there reasonable
                                                    probability of significant deterioration in an important area
                                                    of life functioning"
                                            labelClass="z-index-30"
                                            v-model="form_data.significant_deterioration"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="cut_down_amount"
                                            label="During the past 2 weeks, how much has the patient had to cut
                                                    down the amount of time spent on work or other activities as
                                                    a result of any emotional problems (such as feeling depressed
                                                    or anxious)?"
                                            labelClass="z-index-30"
                                            v-model="form_data.cut_down_amount"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <!-- Table under commit is Functional status with select -->
                                <!--<div class="row">-->
                                    <!--<table class="table table-bordered document-table">-->
                                        <!--<tbody>-->
                                        <!--<tr>-->
                                            <!--<td scope="row" class="param-td-title">-->
                                                <!--<document-textarea-->
                                                        <!--name="appropriate_self_care"-->
                                                        <!--label="1. Age appropriate self-care"-->
                                                        <!--v-model="form_data.appropriate_self_care"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;</document-textarea>-->
                                            <!--</td>-->
                                            <!--<td class="param-td">-->
                                                <!--<select class="dropdown-form-control"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--v-model="form_data.appropriate_self_care_select"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;-->
                                                    <!--<option value="None"></option>-->
                                                    <!--<option>Mild</option>-->
                                                    <!--<option>Moderate</option>-->
                                                    <!--<option>Severe</option>-->
                                                <!--</select>-->
                                            <!--</td>-->
                                        <!--</tr>-->
                                        <!--<tr>-->
                                            <!--<td scope="row" class="param-td-title">-->
                                                <!--<document-textarea-->
                                                        <!--name="interpersonal_relationships"-->
                                                        <!--label="2. Interpersonal relationships (i.e. friends, peers, etc)"-->
                                                        <!--v-model="form_data.interpersonal_relationships"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;</document-textarea>-->
                                            <!--</td>-->
                                            <!--<td class="param-td">-->
                                                <!--<select class="dropdown-form-control"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--v-model="form_data.interpersonal_relationships_select"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;-->
                                                    <!--<option value="None"></option>-->
                                                    <!--<option>Mild</option>-->
                                                    <!--<option>Moderate</option>-->
                                                    <!--<option>Severe</option>-->
                                                <!--</select>-->
                                            <!--</td>-->
                                        <!--</tr>-->
                                        <!--<tr>-->
                                            <!--<td scope="row" class="param-td-title">-->
                                                <!--<document-textarea-->
                                                        <!--name="work_tasks"-->
                                                        <!--label="3. Work/school tasks?"-->
                                                        <!--v-model="form_data.work_tasks"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;</document-textarea>-->
                                            <!--</td>-->
                                            <!--<td class="param-td">-->
                                                <!--<select class="dropdown-form-control"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--v-model="form_data.work_tasks_select"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;-->
                                                    <!--<option value="None"></option>-->
                                                    <!--<option>Mild</option>-->
                                                    <!--<option>Moderate</option>-->
                                                    <!--<option>Severe</option>-->
                                                <!--</select>-->
                                            <!--</td>-->
                                        <!--</tr>-->
                                        <!--<tr>-->
                                            <!--<td scope="row" class="param-td-title">-->
                                                <!--<document-textarea-->
                                                        <!--name="social_activities"-->
                                                        <!--label="4. Participation in usual social/community activities?"-->
                                                        <!--v-model="form_data.social_activities"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;</document-textarea>-->
                                            <!--</td>-->
                                            <!--<td class="param-td">-->
                                                <!--<select class="dropdown-form-control"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--v-model="form_data.social_activities_select"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;-->
                                                    <!--<option value="None"></option>-->
                                                    <!--<option>Mild</option>-->
                                                    <!--<option>Moderate</option>-->
                                                    <!--<option>Severe</option>-->
                                                <!--</select>-->
                                            <!--</td>-->
                                        <!--</tr>-->
                                        <!--<tr>-->
                                            <!--<td scope="row" class="param-td-title" colspan="2">-->
                                                <!--<document-textarea-->
                                                        <!--name="significant_deterioration"-->
                                                        <!--label="Significant deterioration: please note if there reasonable-->
                                                    <!--probability of significant deterioration in an important area-->
                                                    <!--of life functioning"-->
                                                        <!--v-model="form_data.significant_deterioration"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;</document-textarea>-->
                                            <!--</td>-->
                                        <!--</tr>-->
                                        <!--<tr>-->
                                            <!--<td scope="row" class="param-td-title">-->
                                                <!--<document-textarea-->
                                                        <!--name="cut_down_amount"-->
                                                        <!--label="During the past 2 weeks, how much has the patient had to cut-->
                                                    <!--down the amount of time spent on work or other activities as-->
                                                    <!--a result of any emotional problems (such as feeling depressed-->
                                                    <!--or anxious)?"-->
                                                        <!--v-model="form_data.cut_down_amount"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;</document-textarea>-->
                                            <!--</td>-->
                                            <!--<td class="param-td">-->
                                                <!--<select class="dropdown-form-control"-->
                                                        <!--@change="setHasValue"-->
                                                        <!--v-model="form_data.cut_down_amount_select"-->
                                                        <!--:disabled="statuses.editingDisabled"-->
                                                <!--&gt;-->
                                                    <!--<option value="None"></option>-->
                                                    <!--<option>Mild</option>-->
                                                    <!--<option>Moderate</option>-->
                                                    <!--<option>Severe</option>-->
                                                <!--</select>-->
                                            <!--</td>-->
                                        <!--</tr>-->
                                        <!--</tbody>-->
                                    <!--</table>-->
                                <!--</div>-->

                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label z-index-30">Intervention provided today (select what applies)</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="one">
                                                <table class="table borderless">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.history_gathering_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                History Gathering
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.rapport_building_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Rapport Building
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.emotional_support_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Emotional Support
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.formulate_solutions_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Initial Education on Solution Focused Therapy to begin
                                                                clarifying goals and formulate solutions
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.balance_in_life_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Initial Education on Improving  Stress Management Skills
                                                                to include developing relaxation  skills, improved
                                                                self-care, developing balance in life
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.solving_skills_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Initial Education on cognitive therapy, emphasizing
                                                                cognitive restricting to increase adaptive thinking,
                                                                resiliency and problem solving skills
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.crisis_intervention_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Crisis Intervention
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.intervention.other_cb"
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
                                <div class="row ">
                                    <document-textarea
                                            name="general_summary"
                                            label="General Summary and Clinical Impression"
                                            labelClass="z-index-30"
                                            v-model="form_data.general_summary"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>DSM V DIAGNOSIS</label>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 input-container input-container-diagnosis z-index-30">
                                        <label class="control-label diagnosis-label z-index-30">Axis I</label>
                                        <div class="fastselect-disabled" v-if="!form_data.diagnoses_editable || statuses.editingDisabled || (!!this.$store.state.currentDocumentData && form_data.selected_diagnoses_1 && form_data.selected_diagnoses_1.length > 0)"></div>
<!--                                        <input type="text"-->
<!--                                               multiple-->
<!--                                               id="axis1MultipleSelect"-->
<!--                                               class="tagsInput"-->
<!--                                               data-user-option-allowed="true"-->
<!--                                        />-->
                                        <diagnoses-multiselect
                                            id="diagnoseMultipleSelect1"
                                            v-if="form_data.selected_diagnoses_1"
                                            :selectedDiagnoses="form_data.selected_diagnoses_1"
                                            customClass="multiselect-blue diagnoses-multiselect document-diagnoses-multiselect"
                                            @setDiagnoses="setElectronicDocumentsDiagnoses1"
                                        ></diagnoses-multiselect>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-12 input-container input-container-diagnosis z-index-30">
                                        <label class="control-label diagnosis-label z-index-30">Axis II</label>
                                        <div class="fastselect-disabled" v-if="!form_data.diagnoses_editable || statuses.editingDisabled || (!!this.$store.state.currentDocumentData && form_data.selected_diagnoses_2 && form_data.selected_diagnoses_2.length > 0)"></div>
<!--                                        <input type="text"-->
<!--                                               multiple-->
<!--                                               id="axis2MultipleSelect"-->
<!--                                               class="tagsInput"-->
<!--                                               data-user-option-allowed="true"-->
<!--                                        />-->
                                        <diagnoses-multiselect
                                            id="diagnoseMultipleSelect2"
                                            v-if="form_data.selected_diagnoses_2"
                                            :selectedDiagnoses="form_data.selected_diagnoses_2"
                                            customClass="multiselect-blue diagnoses-multiselect document-diagnoses-multiselect"
                                            @setDiagnoses="setElectronicDocumentsDiagnoses2"
                                        ></diagnoses-multiselect>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-12 input-container input-container-diagnosis z-index-30">
                                        <label class="control-label diagnosis-label z-index-30">Axis III: Notable medical condition impacting mental well-being</label>
                                        <div class="fastselect-disabled" v-if="!form_data.diagnoses_editable || statuses.editingDisabled || (!!this.$store.state.currentDocumentData && form_data.selected_diagnoses_3 && form_data.selected_diagnoses_3.length > 0)"></div>
<!--                                        <input type="text"-->
<!--                                               multiple-->
<!--                                               id="axis3MultipleSelect"-->
<!--                                               class="tagsInput"-->
<!--                                               data-user-option-allowed="true"-->
<!--                                        />-->
                                        <diagnoses-multiselect
                                            id="diagnoseMultipleSelect3"
                                            v-if="form_data.selected_diagnoses_3"
                                            :selectedDiagnoses="form_data.selected_diagnoses_3"
                                            customClass="multiselect-blue diagnoses-multiselect document-diagnoses-multiselect"
                                            @setDiagnoses="setElectronicDocumentsDiagnoses3"
                                        ></diagnoses-multiselect>
                                    </div>
                                </div>



                                <br/>
                                <div class="row ">
                                    <label>TREATMENT PLAN</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="treatment_plan"
                                            v-model="form_data.treatment_plan"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>TREATMENT GOALS</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="treatment_goals"
                                            v-model="form_data.treatment_goals"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row" style="margin-top:15px;" id="ia-confirm-diagnoses">
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
    import diagnosisMethods from './../../../mixins/diagnosis-and-icd-code-methods';
    import DiagnosesMultiselect from './../../../mixins/diagnoses-multiselect';

    export default {
        mixins: [validate, save, methods, dateOfService, diagnosisMethods, DiagnosesMultiselect],
        data(){
            return{
                document_name: 'kp-initial-assessment-adult-pc',
                document_title: 'KP Initial Assessment (Adult) - Panorama City',
                functional_status_options: [
                    {value:'Mild', label: 'Mild'},
                    {value:'Moderate', label: 'Moderate'},
                    {value:'Severe', label: 'Severe'},
                ],
                statuses: {
                    confirm_diagnoses: false,
                }
            }
        },
        beforeMount(){
            if(!this.form_data && this.$store.state.currentDocument){
                this.patient_id = this.$store.state.currentPatient.id;
                this.form_data = {
                    diagnoses_editable: true,
                    first_name: this.$store.state.currentPatient.first_name,
                    last_name: this.$store.state.currentPatient.last_name,
                    date: this.formatDate(new Date(), this.momentDateFormat),
                    birth_date: this.$store.state.currentPatient.date_of_birth,
                    sex: this.$store.state.currentPatient.sex,
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_title: this.parseProviderTitle(this.currentProvider.provider_name),
                    provider_license_no: this.currentProvider.license_no,
                    mrn: null,
                    length_of_session: null,
                    individuals_present: null,
                    occupation: null,
                    family_of_origin: null,
                    relationship_history: null,
                    living_arrangements: null,
                    social_support: null,
                    initiates_sustains: null,
                    other_sources: null,
                    presenting_problem: null,
                    history_of_traumatic: null,
                    symptoms_behaviors: null,
                    onset_symptoms_behaviors: null,
                    behavioral_health_history: null,
                    behavioral_family_health_history: null,
                    patient_medical_history: null,
                    barries_to_care: null,
                    appearance: null,
                    behavior: null,
                    impairment_memory: null,
                    eye_contact: null,
                    speech: null,
                    mood: null,
                    affect: null,
                    stream_of_thought: null,
                    impulse_control: null,
                    judgment: null,
                    insight: null,
                    alcohol_amount: null,
                    alcohol_frequency: null,
                    alcohol_history: null,
                    drug_amount: null,
                    drug_frequency: null,
                    drug_history: null,
                    significant_impairment: null,
                    appropriate_self_care: null,
                    // appropriate_self_care_select: 'None',
                    interpersonal_relationships: null,
                    // interpersonal_relationships_select: 'None',
                    work_tasks: null,
                    // work_tasks_select: 'None',
                    social_activities: null,
                    // social_activities_select: 'None',
                    significant_deterioration: null,
                    cut_down_amount: null,
                    // cut_down_amount_select: 'None',
                    general_summary: null,
                    self_harming: null,
                    current_suicidal: null,
                    histore_self_harming: null,
                    treatment_plan: null,
                    treatment_goals: null,
                    intervention: {
                        history_gathering_cb: null,
                        rapport_building_cb: null,
                        emotional_support_cb: null,
                        formulate_solutions_cb: null,
                        balance_in_life_cb: null,
                        solving_skills_cb: null,
                        crisis_intervention_cb: null,
                        other_cb: null,
                    },
                    date_of_service: null,
                    functional_status_rb: null,
                    selected_diagnoses_1: this.$store.state.currentPatient.diagnoses || [],
                    selected_diagnoses_2: [],
                    selected_diagnoses_3: [],
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp-initial-assessment-adult-pc';
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

                    self.initDateOfService();

                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                });
            },500);
        },
        methods: {
            getCustomValidation(){

                let error = false;

                if(this.getCustomValidateionDateOfService()){
                    error = true;
                }

                if(this.getCustomValidationDiagnoses()){
                    error = true;
                }

                if(!this.statuses.confirm_diagnoses) {
                    $('#ia-confirm-diagnoses label').addClass('text-red');
                    error = true;
                }

                return error;
            },
        },
    }
</script>

<style scoped>
.form-note .form-group .control-label {
    z-index:30!important;
}
</style>
