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
                                    Woodland Hills Service Area
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <h4 class="modal-title">
                                    INITIAL DIAGNOSTIC INTERVIEW
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>**FORM IS DUE within ONE business day of session**</p>
                                <h5 class="modal-title">
                                    Submit via encrypted email to:
                                    <a href="mailto:WH-OutsideMedicalCase-Management@kp.org">WH-OutsideMedicalCase-Management@kp.org</a>
                                </h5>
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
                                            labelClass="z-index-30"
                                            size="col-md-4"
                                            v-model="form_data.first_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="last_name"
                                            label="Lastname"
                                            labelClass="z-index-30"
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
                                    <document-textarea
                                            name="length_of_session"
                                            label="Length of Session"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.length_of_session"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="mrn"
                                            label="Kaiser MRN"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row  input-row">
                                    <document-textarea
                                            name="individuals_present"
                                            label="Individuals Present"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.individuals_present"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="other_sources"
                                            label="Other Sources of Information"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.other_sources"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>CHIEF COMPLAINT(S)</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="chief_complaint"
                                            v-model="form_data.chief_complaint"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>ONSET AND DURATION OF SYMPTOMS</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="duration_of_symptoms"
                                            v-model="form_data.duration_of_symptoms"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>HISTORY & PRESENTATION</label>
                                </div>
                                <div class="row">
                                    <label>PSYCHIATRIC HISTORY AND MEDICATIONS (diagnoses, psychotherapy, IP/IOP/PHP, etc.)</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="self_psychiatric_history"
                                            label="Self"
                                            labelClass="z-index-30"
                                            v-model="form_data.self_psychiatric_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="family_psychiatric_history"
                                            label="Family"
                                            labelClass="z-index-30"
                                            v-model="form_data.family_psychiatric_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row">
                                    <label>SOCIAL HISTORY</label>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="occupation"
                                            label="Occupation"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.occupation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="education"
                                            label="Education"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.education"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="living_situation"
                                            label="Living Situation"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.living_situation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="family_of_origin"
                                            label="Family of Origin"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.family_of_origin"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="immediate_family"
                                            label="Immediate Family"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.immediate_family"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="social_support"
                                            label="Social Support"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.social_support"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="religion"
                                            label="Religion"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.religion"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="exercise_habits"
                                            label="Exercise Habits"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.exercise_habits"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="pets"
                                            label="Pets"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.pets"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="hobbies_interests"
                                            label="Hobbies/Interests"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.hobbies_interests"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>ALCOHOL AND DRUG USE/HISTORY How much are you using?</label>
                                </div>
                                <div class="row ">
                                    <label>SELF</label>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="etoh"
                                            label="ETOH"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.etoh"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="thc"
                                            label="THC"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.thc"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="tobacco"
                                            label="Tobacco"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.tobacco"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="caffeine"
                                            label="Caffeine"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.caffeine"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row  input-row">
                                    <document-textarea
                                            name="narcotic_pain_medications"
                                            label="Narcotic Pain Medications"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.narcotic_pain_medications"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="street_club_drugs"
                                            label="Street/Club Drugs"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.street_club_drugs"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row sub-title">
                                    <label>FAMILY</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="family"
                                            v-model="form_data.family"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row">
                                    <label>TRAUMA/ABUSE HISTORY</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="abuse_history"
                                            v-model="form_data.abuse_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row">
                                    <label>CLINICAL SYMPTOMS/HISTORY: Check any that apply, describe further as needed</label>
                                </div>
                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container without-label">
                                        <!--<label class="control-label">Clinical Symptoms/History: Check any that Apply, Describe Further as Needed</label>-->
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="clinical_history">
                                                <table class="table borderless">
                                                    <tbody>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.depressed_mood_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Depressed Mood
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.feeling_worthless_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Feeling Worthless
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.descreased_concentration_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Decreased Concentration
                                                            </label>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.sadness_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Sadness
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.increased_sleep_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Increased Sleep
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.indecisiveness_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Indecisiveness
                                                            </label>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.irritable_mood_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Irritable Mood
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.decreased_sleep_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Decreased Sleep
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.weight_gain_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Weight Gain
                                                            </label>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.decreased_interest_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Decreased Interest or Pleasure
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.pyschomotor_agitation_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Pyschomotor/Agitation
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.wight_loss_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Weight Loss When Not Dieting
                                                            </label>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.excessive_guilt_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Excessive Guilt
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.increased_appetite_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Increased Appetite
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.decreased_energy_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Decreased Energy/Fatigue
                                                            </label>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.recurrent_thoughts_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Recurrent Thoughts of Death
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.anhedonia_and_decreased_libido_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Anhedonia and Decreased Libido
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.periods_energy_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Periods of High Energy
                                                                and/or Impulsive Behavior
                                                            </label>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.feeling_hopeless_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Feeling Hopeless
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.low_self_esteem_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Low Self - Esteem
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <label class="checkbox-inline">
                                                                <input type="checkbox" class="checkbox-document-form-control"
                                                                       v-model="form_data.clinical_history.tearfulness_cb"
                                                                       @change="setHasValue"
                                                                       :disabled="statuses.editingDisabled"
                                                                >
                                                                Tearfulness
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
                                            name="additional_symptoms_descriptions"
                                            label="Additional Symptoms/Descriptions"
                                            labelClass="z-index-30"
                                            v-model="form_data.additional_symptoms_descriptions"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row">
                                    <label>BARRIERS TO CARE: Cultural, religious, or other barriers to care identified?</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="barries_to_care"
                                            v-model="form_data.barries_to_care"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>MENTAL STATUS EXAM: Describe</label>
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
                                                       name="functional_status_rb"
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

                                <div class="row ">
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
                                        <label class="control-label input-label">
                                            In the past 3 months, how impaired has the patient been in the following areas of:
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <!--<document-select-->
                                            <!--name="appropriate_self_care"-->
                                            <!--label="1. Age appropriate self-care"-->
                                            <!--v-model="form_data.appropriate_self_care_select"-->
                                            <!--:options="functional_status_options"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-select>-->
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            1. Age appropriate self-care
                                        </label>
                                        <div class="col-xs-4" v-for="option in this.functional_status_options">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.appropriate_self_care_rb"
                                                       :value="option.value"
                                                       name="appropriate_self_care_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                {{ option.label }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!--<document-select-->
                                            <!--name="interpersonal_relationships"-->
                                            <!--label="2. Interpersonal relationships (i.e. friends, peers, etc)"-->
                                            <!--v-model="form_data.interpersonal_relationships_select"-->
                                            <!--:options="functional_status_options"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-select>-->
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            2. Interpersonal relationships (i.e. friends, peers, etc)
                                        </label>
                                        <div class="col-xs-4" v-for="option in this.functional_status_options">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.interpersonal_relationships_rb"
                                                       :value="option.value"
                                                       name="interpersonal_relationships_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                {{ option.label }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!--<document-select-->
                                            <!--name="work_tasks"-->
                                            <!--label="3. Work/school tasks?"-->
                                            <!--v-model="form_data.work_tasks_select"-->
                                            <!--:options="functional_status_options"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-select>-->
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            3. Work/school tasks?
                                        </label>
                                        <div class="col-xs-4" v-for="option in this.functional_status_options">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.work_tasks_rb"
                                                       :value="option.value"
                                                       name="work_tasks_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                {{ option.label }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!--<document-select-->
                                            <!--name="social_activities"-->
                                            <!--label="4. Participation in usual social/community activities?"-->
                                            <!--v-model="form_data.social_activities_select"-->
                                            <!--:options="functional_status_options"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-select>-->
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            4. Participation in usual social/community activities?
                                        </label>
                                        <div class="col-xs-4" v-for="option in this.functional_status_options">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.social_activities_rb"
                                                       :value="option.value"
                                                       name="social_activities_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                {{ option.label }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!--<div class="row">-->
                                    <!--<table class="table table-bordered document-table">-->
                                        <!--<tbody>-->
                                        <!--<tr>-->
                                            <!--<td scope="row" class="param-td-title">-->
                                                <!--<document-textarea-->
                                                        <!--name="appropriate_self_care"-->
                                                        <!--label="1. Age appropriate self care"-->
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
                                                        <!--label="Significant deterioration: please describe if there is-->
                                                               <!--reasonable probability of significant deterioration-->
                                                               <!--in an important area of life functioning"-->
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
                                <div class="row ">
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
                                <div class="row ">
                                    <!--<document-select-->
                                            <!--name="cut_down_amount"-->
                                            <!--label="During the past 2 weeks, how much has the patient had to cut-->
                                                    <!--down the amount of time spent on work or other activities as-->
                                                    <!--a result of any emotional problems (such as feeling depressed-->
                                                    <!--or anxious)?"-->
                                            <!--v-model="form_data.cut_down_amount_select"-->
                                            <!--:options="functional_status_options"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-select>-->
                                    <div class="form-group input-container col-xs-12 radio-with-label-column">
                                        <label class="control-label input-label">
                                            During the past 2 weeks, how much has the patient had to cut
                                            down the amount of time spent on work or other activities as
                                            a result of any emotional problems (such as feeling depressed
                                            or anxious)?
                                        </label>
                                        <div class="col-xs-4" v-for="option in this.functional_status_options">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.cut_down_amount_rb"
                                                       :value="option.value"
                                                       name="cut_down_amount_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                {{ option.label }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row input-check-div">
                                    <div class="form-group form-group-bordered col-md-12 fix-row-1 current-status-container">
                                        <label class="control-label">Intervention provided today (select what applies)</label>
                                        <div class="checkbox">
                                            <div class="checkbox-group" data-required="intervention">
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

                                <br/>
                                <div class="row ">
                                    <label>RISK ASSESSMENT</label>
                                </div>

                                <div class="row ">
                                    <document-textarea
                                            name="self_harming"
                                            label="Suicidal/harm to self: ideation, plan and intent"
                                            labelClass="z-index-30"
                                            v-model="form_data.self_harming"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="homicidal_harm_ideation"
                                            label="Homicidal/harm to others ideation, plan and intent"
                                            labelClass="z-index-30"
                                            v-model="form_data.homicidal_harm_ideation"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>GENERAL SUMMARY AND CLINICAL IMPRESSION</label>
                                </div>
                                <div class="row ">
                                    <document-textarea
                                            name="general_summary"
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
                                        <label class="control-label diagnosis-label z-index-30">Primary</label>
                                        <div class="fastselect-disabled" v-if="!form_data.diagnoses_editable || statuses.editingDisabled || (!!this.$store.state.currentDocumentData && form_data.selected_diagnoses_1 && form_data.selected_diagnoses_1.length > 0)"></div>
<!--                                        <input type="text"-->
<!--                                               multiple-->
<!--                                               id="primaryMultipleSelect"-->
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
                                        <label class="control-label diagnosis-label z-index-30">Secondary</label>
                                        <div class="fastselect-disabled" v-if="!form_data.diagnoses_editable || statuses.editingDisabled || (!!this.$store.state.currentDocumentData && form_data.selected_diagnoses_2 && form_data.selected_diagnoses_2.length > 0)"></div>
<!--                                        <input type="text"-->
<!--                                               multiple-->
<!--                                               id="secondaryMultipleSelect"-->
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
                                        <label class="control-label diagnosis-label z-index-30">Notable medical condition impacting mental well-being</label>
                                        <div class="fastselect-disabled" v-if="!form_data.diagnoses_editable || statuses.editingDisabled || (!!this.$store.state.currentDocumentData && form_data.selected_diagnoses_3 && form_data.selected_diagnoses_3.length > 0)"></div>
<!--                                        <input type="text"-->
<!--                                               multiple-->
<!--                                               id="notableMultipleSelect"-->
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
                                <div class="row">
                                    <label>PROVIDER'S INFORMATION</label>
                                </div>
                                <div class="row  input-row">
                                    <document-input
                                            name="provider_name"
                                            label="Provider Name"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.provider_name"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="provider_license_no"
                                            label="Provider License No."
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.provider_license_no"
                                            :disabled="true"
                                    ></document-input>
                                </div>

                                <div class="row  input-row">
                                    <document-input
                                            name="provider_email"
                                            label="Provider Email"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.provider_email"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="email"
                                    ></document-input>
                                    <document-input
                                            name="provider_phone"
                                            label="Provider Phone"
                                            labelClass="z-index-30"
                                            size="col-md-6"
                                            v-model="form_data.provider_phone"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-mask="'###-###-####'"
                                            validateRules="regex:^([\d]{3}-){2}[\d]{4}$"
                                    ></document-input>
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
                document_name: 'kp-initial-assessment-adult-wh',
                document_title: 'KP Initial Assessment (Adult) - Woodland Hills',
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
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_title: this.parseProviderTitle(this.currentProvider.provider_name),
                    provider_license_no: this.currentProvider.license_no,
                    provider_email: this.currentProvider.email,
                    provider_phone: this.currentProvider.phone,
                    education: null,
                    mrn: null,
                    length_of_session: null,
                    individuals_present: null,
                    occupation: null,
                    family_of_origin: null,
                    religion: null,
                    living_situation: null,
                    social_support: null,
                    immediate_family: null,
                    other_sources: null,
                    exercise_habits: null,
                    pets: null,
                    hobbies_interests: null,
                    chief_complaint: null,
                    duration_of_symptoms: null,
                    self_psychiatric_history: null,
                    family_psychiatric_history: null,
                    abuse_history: null,
                    barries_to_care: null,
                    clinical_history: {
                        depressed_mood_cb: null,
                        feeling_worthless_cb: null,
                        descreased_concentration_cb: null,
                        sadness_cb: null,
                        increased_sleep_cb: null,
                        indecisiveness_cb: null,
                        irritable_mood_cb: null,
                        decreased_sleep_cb: null,
                        weight_gain_cb: null,
                        decreased_interest_cb: null,
                        pyschomotor_agitation_cb: null,
                        wight_loss_cb: null,
                        excessive_guilt_cb: null,
                        increased_appetite_cb: null,
                        decreased_energy_cb: null,
                        recurrent_thoughts_cb: null,
                        anhedonia_and_decreased_libido_cb: null,
                        periods_energy_cb: null,
                        feeling_hopeless_cb: null,
                        low_self_esteem_cb: null,
                        tearfulness_cb: null,
                    },
                    additional_symptoms_descriptions: null,
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
                    etoh: null,
                    thc: null,
                    tobacco: null,
                    caffeine: null,
                    narcotic_pain_medications: null,
                    street_club_drugs: null,
                    family: null,
                    significant_impairment: null,
                    // appropriate_self_care: null,
                    appropriate_self_care_rb: null,
                    // interpersonal_relationships: null,
                    interpersonal_relationships_rb: null,
                    // work_tasks: null,
                    work_tasks_rb: null,
                    // social_activities: null,
                    social_activities_rb: null,
                    significant_deterioration: null,
                    // cut_down_amount: null,
                    cut_down_amount_rb: null,
                    general_summary: null,
                    self_harming: null,
                    homicidal_harm_ideation: null,
                    treatment_plan: null,
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
                    //diagnoses
                    selected_diagnoses_1: this.$store.state.currentPatient.diagnoses || [],
                    selected_diagnoses_2: [],
                    selected_diagnoses_3: [],
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp-initial-assessment-adult-wh';
            let document_name = self.getFormName(menu_item_selector);
            self.document_name = document_name;
            self.document_title = self.getFormTitle(menu_item_selector);

            window.setTimeout(() => {
                $('#'+this.document_name).on('shown.bs.modal', function() {
                    $('body').addClass('custom-modal');

                    autosize($('#' +document_name).find('textarea'));

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
//            checkFromDataNotNull(data){
//                Object.keys(data).forEach(function(k) {
//
//                    if(typeof data[k] == 'object'){
//
//                        if(this.checkFromDataNotNull(data[k])){
//
//                            return true;
//                        }
//                    }
//                    else{
//
//                        if(data[k] !== null){
//
//                            return true;
//                        }
//                    }
//
//                    return false;
//                });
//            },
        },
    }
</script>
