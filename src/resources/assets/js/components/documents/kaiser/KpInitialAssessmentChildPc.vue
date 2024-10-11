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
                                    CHILD/ADOLESCENT INITIAL EVALUATION
                                </h4>
                            </div>
                            <div class="col-lg-12">
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
                                        <label class="control-label input-label">Birthdate</label>
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
                                            name="age"
                                            label="Age"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.age"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row  input-row">
                                    <document-textarea
                                            name="mrn"
                                            label="Medical Record Number"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="ethnicity"
                                            label="Ethnicity"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.ethnicity"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="emergency_contact"
                                            label="Emergency Contact"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.emergency_contact"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <!--<document-textarea-->
                                            <!--name="length_of_session"-->
                                            <!--label="Length of Session"-->
                                            <!--size="col-md-4"-->
                                            <!--v-model="form_data.length_of_session"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-textarea>-->

                                </div>

                                <div class="row  input-row">
                                    <document-textarea
                                            name="attending_session"
                                            label="Those Attending Session"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.attending_session"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="occupation"
                                            label="Occupation"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.occupation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>PRESENTING PROBLEM (Reason for seeking MH treatment, recent losses/changes/stressors)</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="presenting_problem"
                                            v-model="form_data.presenting_problem"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row">
                                    <label>SYMPTOMS/BEHAVIORS (frequency, duration)</label>
                                </div>
                                <div class="row">
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
                                <div class="row">
                                    <document-textarea
                                            name="onset_symptoms_behaviors"
                                            v-model="form_data.onset_symptoms_behaviors"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>SLEEP ISSUES (Frequent nightmares/sound /restless sleeper/frequent nightly
                                        awakenings/Difficult falling asleep or difficult awakening/shares a room/sleeps
                                        in own bed, avg. hrs/night)</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="sleep_issues"
                                            v-model="form_data.sleep_issues"
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
                                <div class="row">
                                    <document-textarea
                                            name="history_of_traumatic"
                                            v-model="form_data.history_of_traumatic"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>FAMILY COMPOSITION, DYNAMICS, DEVELOPMENTAL HISTORY</label>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="father_name"
                                            label="Biological Fathers Name"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.father_name"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="father_age"
                                            label="Age"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.father_age"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="father_occupation"
                                            label="Occupation"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.father_occupation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="mother_name"
                                            label="Biological Mothers Name"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.mother_name"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="mother_age"
                                            label="Age"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.mother_age"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="mother_occupation"
                                            label="Occupation"
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.mother_occupation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="living_situation"
                                            label="Living Situation (who patient lives with, include siblings/step siblings)"
                                            labelClass="z-index-30"
                                            v-model="form_data.living_situation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="family_communication_style"
                                            label="Family Communication Style"
                                            labelClass="z-index-30"
                                            v-model="form_data.family_communication_style"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row"> 
                                    <document-textarea
                                            name="discipline_style"
                                            label="Discipline Style/Strategies"
                                            labelClass="z-index-30"
                                            v-model="form_data.discipline_style"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row"> 
                                    <document-textarea
                                            name="family_problems"
                                            label="Family/Blended Family Problems"
                                            labelClass="z-index-30"
                                            v-model="form_data.family_problems"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="quality_time"
                                            label="Parental Quality Time W/Child"
                                            labelClass="z-index-30"
                                            v-model="form_data.quality_time"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>BEHAVIORAL HEALTH HISTORY (previous tx, medication hx, psych hospitalizations, addiction treatment)</label>
                                </div>
                                <div class="row">
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
                                <div class="row">
                                    <document-textarea
                                            name="behavioral_family_health_history"
                                            v-model="form_data.behavioral_family_health_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>DEVELOPMENTAL HISTORY</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="developmental_history"
                                            label="Developmental Problems"
                                            labelClass="z-index-30"
                                            v-model="form_data.developmental_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
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
                                            name="children"
                                            label="Children"
                                            labelClass="z-index-30"
                                            v-model="form_data.children"
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
                                            name="learning_disability"
                                            label="IEP'S/SST'S/504 Plan/Tested at School for Learning Disability"
                                            labelClass="z-index-30"
                                            v-model="form_data.learning_disability"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>SOCIALIZATION</label>
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
                                            name="patient_victim"
                                            label="Is Patient a Victim of Bullying"
                                            labelClass="z-index-30"
                                            v-model="form_data.patient_victim"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="bully_others"
                                            label="Does Patient Tease or Bully Others"
                                            labelClass="z-index-30"
                                            v-model="form_data.bully_others"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="sport_activities"
                                            label="Sports/Extra-Curricular Activities"
                                            labelClass="z-index-30"
                                            v-model="form_data.sport_activities"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="hobbies"
                                            label="Hobbies/Interests"
                                            labelClass="z-index-30"
                                            v-model="form_data.hobbies"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>PATIENT MEDICAL HISTORY (Medical Problems/Chronic Conditions/ Allergies)</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="patient_medical_history"
                                            v-model="form_data.patient_medical_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
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
                                <div class="row ">
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
                                <div class="row ">
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
                                <div class="row ">
                                    <label>MENTAL STATUS EXAM</label>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="apparent_age"
                                            label="Apparent Age (over/underweight, etc.)"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.apparent_age"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="attire"
                                            label="Attire (glasses, braces, etc.)"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.attire"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="grooming"
                                            label="Grooming"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.grooming"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="hygiene"
                                            label="Hygiene (birthmarks, marks, bruises, etc.)"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.hygiene"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="eye_contact"
                                            label="Eye Contact"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.eye_contact"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="gait_and_posture"
                                            label="Gait and Posture"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.gait_and_posture"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="behavior_manner"
                                            label="Behavior / Manner"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.behavior_manner"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="motor_activity"
                                            label="Motor Activity"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.motor_activity"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="mood"
                                            label="Mood"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.mood"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="affect"
                                            label="Affect"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.affect"
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
                                            name="thought_process"
                                            label="Thought Process"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.thought_process"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="thought_content"
                                            label="Thought Content / Perceptual Disturbances"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.thought_content"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="sensory_and_cognitive"
                                            label="Sensory and Cognitive"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.sensory_and_cognitive"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="insight"
                                            label="Insight"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.insight"
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
                                <div class="row  input-row">
                                    <document-textarea
                                            name="reliability"
                                            label="Reliability"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.reliability"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="impulse"
                                            label="Impulse"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.impulse"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="other_information"
                                            label="Other Observations/Additional Information"
                                            labelClass="z-index-30"
                                            v-model="form_data.other_information"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>DSM V DIAGNOSIS</label>
                                </div>

                                <div class="row">
                                    <div class="form-group col-12 input-container input-container-diagnosis">
                                        <label class="control-label diagnosis-label">Axis I</label>
                                        <div class="fastselect-disabled" v-if="!form_data.diagnoses_editable || statuses.editingDisabled || (!!this.$store.state.currentDocumentData && form_data.selected_diagnoses && form_data.selected_diagnoses.length > 0)"></div>
<!--                                        <input type="text"-->
<!--                                               multiple-->
<!--                                               id="axis1MultipleSelect"-->
<!--                                               class="tagsInput"-->
<!--                                               data-user-option-allowed="true"-->
<!--                                        />-->
                                        <diagnoses-multiselect
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
                                <div class="row" v-if="signature">
                                    <label>SIGNATURE</label>
                                </div>
                                <div class="row" v-if="signature">
                                    <img class="img-responsive provider-signature" :src="signature">
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
                document_name: 'kp-initial-assessment-child-pc',
                document_title: 'KP Initial Assessment (Child) - Panorama City',
                signature: null,
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
                    age: null,
                    occupation: null,
                    ethnicity: null,
                    attending_session: null,
                    emergency_contact: null,
                    presenting_problem: null,
                    history_of_traumatic: null,
                    symptoms_behaviors: null,
                    onset_symptoms_behaviors: null,
                    behavioral_health_history: null,
                    behavioral_family_health_history: null,
                    patient_medical_history: null,
                    sleep_issues: null,
                    apparent_age: null,
                    attire: null,
                    grooming: null,
                    hygiene: null,
                    eye_contact: null,
                    gait_and_posture: null,
                    motor_activity: null,
                    speech: null,
                    mood: null,
                    affect: null,
                    thought_process: null,
                    thought_content: null,
                    sensory_and_cognitive: null,
                    insight: null,
                    judgment: null,
                    reliability: null,
                    impulse: null,
                    other_information: null,
                    alcohol_amount: null,
                    alcohol_frequency: null,
                    alcohol_history: null,
                    drug_amount: null,
                    drug_frequency: null,
                    drug_history: null,
                    self_harming: null,
                    current_suicidal: null,
                    histore_self_harming: null,
                    treatment_plan: null,
                    treatment_goals: null,
                    date_of_service: null,
                    father_name: null,
                    father_age: null,
                    father_occupation: null,
                    mother_name: null,
                    mother_age: null,
                    mother_occupation: null,
                    living_situation: null,
                    family_communication_style: null,
                    discipline_style: null,
                    family_problems: null,
                    quality_time: null,
                    developmental_history: null,
                    family_of_origin: null,
                    relationship_history: null,
                    children: null,
                    social_support: null,
                    learning_disability: null,
                    initiates_sustains: null,
                    patient_victim: null,
                    bully_others: null,
                    sport_activities: null,
                    hobbies: null,
                    behavior_manner: null,
                    selected_diagnoses: this.$store.state.currentPatient.diagnoses || []
                }
            }

//            this.getSignature();
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp-initial-assessment-child-pc';
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
