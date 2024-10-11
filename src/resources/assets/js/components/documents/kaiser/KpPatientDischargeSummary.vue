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
                                    KP PC Behavioral Health Department
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <h5 class="modal-title">
                                    Please return typed via Confidential Fax or Secure Email/Scan to:
                                    <a href="mailto:External-Referral-Team-STR@kp.org">External-Referral-Team-STR@kp.org</a>
                                    or Fax: 818-758-1361
                                </h5>
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
                                            label="Medical Record Number"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('date_of_discharge'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Discharge Date</label>
                                        <el-date-picker
                                                v-model="form_data.date_of_discharge"
                                                type="date"
                                                name="date_of_discharge"
                                                @focus="pickerFocus('date_of_discharge')"
                                                @blur="pickerBlur('date_of_discharge')"
                                                :editable="false"
                                                :format="datePickerFormat"
                                                :value-format="datePickerValueFormat"
                                                @change="resetDateError('date_of_discharge')"
                                                :disabled="statuses.editingDisabled"
                                                :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <!--<date-of-service-->
                                            <!--:editingDisabled="statuses.editingDisabled"-->
                                            <!--:patient="Boolean(patient)"-->
                                            <!--size="col-md-6"-->
                                    <!--&gt;</date-of-service>-->
                                    <document-textarea
                                            name="dates_of_service"
                                            label="Dates of Service"
                                            size="col-md-6"
                                            v-model="form_data.dates_of_service"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="number_of_session"
                                            label="Number of Sessions"
                                            v-model="form_data.number_of_session"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>PRESENTING PROBLEM/SYMPTOMS</label>
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
                                <div class="row ">
                                    <label>TREATMENT PLAN</label>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="goal_1"
                                            label="Goal 1"
                                            size="col-md-6"
                                            v-model="form_data.goal_1"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="progress_made_1"
                                            label="Progress Made"
                                            size="col-md-6"
                                            v-model="form_data.progress_made_1"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row input-row">
                                    <document-textarea
                                            name="goal_2"
                                            label="Goal 2"
                                            size="col-md-6"
                                            v-model="form_data.goal_2"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="progress_made_2"
                                            label="Progress Made"
                                            size="col-md-6"
                                            v-model="form_data.progress_made_2"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <br/>
                                <div class="row ">
                                    <label>REASON FOR DISCHARGE</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="reason_for_discharge"
                                            v-model="form_data.reason_for_discharge"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>
                                <div class="row">
                                    <div class="form-group input-container col-lg-12 radio-without-label">
                                        <div class="col-md-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="continued_treatment_rb_1"
                                                       name="continued_treatment_rb"
                                                       v-model="form_data.continued_treatment_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="Recommend"
                                                       @change="setHasValue"
                                                >
                                                Recommend Continued Treatment
                                            </label>
                                        </div>
                                        <div class="col-md-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="continued_treatment_rb_2"
                                                       name="continued_treatment_rb"
                                                       v-model="form_data.continued_treatment_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="Not recommended"
                                                       @change="setHasValue"
                                                >
                                                No Further Treatment Recommended/Needed
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <br/>
                                <div class="row ">
                                    <label>RECOMMENDATIONS (include any referrals provided)</label>
                                </div>
                                <div class="row">
                                    <document-textarea
                                            name="recommendation"
                                            v-model="form_data.recommendation"
                                            @change="setHasValue"
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
    import dateOfService from './../../../mixins/date-of-service';

    export default {
        mixins: [validate, save, methods, dateOfService],
        data(){
            return{
                document_name: 'kp-patient-discharge-summary',
                document_title: 'KP Patient Discharge Summary - Panorama City',
            }
        },
        beforeMount(){
            if(!this.form_data && this.$store.state.currentDocument){
                this.patient_id = this.$store.state.currentPatient.id;
                this.form_data = {
                    first_name: this.$store.state.currentPatient.first_name,
                    last_name: this.$store.state.currentPatient.last_name,
                    date: this.formatDate(new Date(), this.momentDateFormat),
                    dates_of_service: null,
                    date_of_discharge: null,
                    mrn: null,
                    number_of_session: null,
                    presenting_problem: null,
                    goal_1: null,
                    progress_made_1: null,
                    goal_2: null,
                    progress_made_2: null,
                    reason_for_discharge: null,
                    continued_treatment_rb: null,
                    recommendation: null,

                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp-patient-discharge-summary';
            let document_name = self.getFormName(menu_item_selector);
            self.document_name = document_name;
            self.document_title = self.getFormTitle(menu_item_selector);

            window.setTimeout(() => {
                $('#'+document_name).on('shown.bs.modal', function() {
                    $('body').addClass('custom-modal');

                    autosize($('#'+document_name).find('textarea'));

                    $('.input-container').on('click', function(){

                        $(this).find('.input-element').focus();
                    });

                    $('#'+document_name).find('input.el-input__inner').addClass('input-element');

//                    self.initDateOfService();

                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                });
            },500);
        },
        methods: {
            getCustomValidation() {

                let error = false;

//                if(this.getCustomValidateionDateOfService()){
//
//                    error = true;
//                }

                return error;
            }
        },
    }
</script>
