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
                                    INTERNAL USE ONLY
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    PHYSICIAN REFERRAL REQUEST FORM
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document" novalidate>

                                <div class="row input-row">
                                    <div class="col-md-6 input-container radio-without-label form-group">
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_status_rb"
                                                       value="Routine"
                                                       name="visit_status_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Routine
                                            </label>
                                        </div>
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_status_rb"
                                                       value="Urgent"
                                                       name="visit_status_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Urgent
                                            </label>
                                        </div>
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_status_rb"
                                                       value="Stat"
                                                       name="visit_status_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Stat
                                            </label>
                                        </div>
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_status_rb"
                                                       value="Retro"
                                                       name="visit_status_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Retro
                                            </label>
                                        </div>
                                    </div>
                                    <!--<div class="form-group input-container col-md-6 radio-without-label">-->
                                        <!--<label class="control-label input-label radio-label">-->
                                            <!--Auto Accident-->
                                        <!--</label>-->
                                        <!--<label>-->
                                            <!--<input type="radio"-->
                                                   <!--id="auto_accident_1"-->
                                                   <!--name="auto_accident"-->
                                                   <!--v-model="form_data.auto_accident"-->
                                                   <!--:disabled="statuses.editingDisabled"-->
                                                   <!--value="period_expired"-->
                                                   <!--@change="setHasValue"-->
                                            <!--&gt;-->
                                            <!--Yes-->
                                        <!--</label>-->
                                        <!--<label>-->
                                            <!--<input type="radio"-->
                                                   <!--id="auto_accident_2"-->
                                                   <!--name="auto_accident"-->
                                                   <!--v-model="form_data.auto_accident"-->
                                                   <!--:disabled="statuses.editingDisabled"-->
                                                   <!--value="visits_expired"-->
                                                   <!--@change="setHasValue"-->
                                            <!--&gt;-->
                                            <!--No-->
                                        <!--</label>-->
                                    <!--</div>-->
                                    <div class="form-group input-container col-md-6 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Auto Accident
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                id="auto_accident_rb_1"
                                                name="auto_accident_rb"
                                                v-model="form_data.auto_accident_rb"
                                                :disabled="statuses.editingDisabled"
                                                value="Yes"
                                                @change="setHasValue"
                                                >
                                                Yes
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                id="auto_accident_rb_2"
                                                name="auto_accident_rb"
                                                v-model="form_data.auto_accident_rb"
                                                :disabled="statuses.editingDisabled"
                                                value="No"
                                                @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                    </div>
                                </div>

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
                                    <div class="form-group col-md-4 input-container document-date"
                                         :class="{'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">DOB</label>
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
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                            name="address"
                                            label="Address"
                                            size="col-md-6"
                                            v-model="form_data.address"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="zip_code"
                                            label="Zip Code"
                                            size="col-md-6"
                                            v-model="form_data.zip_code"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                            name="hmo"
                                            label="HMO"
                                            size="col-md-6"
                                            v-model="form_data.hmo"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="cert"
                                            label="Cert. #"
                                            size="col-md-6"
                                            v-model="form_data.cert"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                            name="refer_to"
                                            label="Refer To"
                                            size="col-md-6"
                                            v-model="form_data.refer_to"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="specialty"
                                            label="Specialty"
                                            size="col-md-6"
                                            v-model="form_data.specialty"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <!--<document-textarea-->
                                            <!--name="diagnosis_icd_code"-->
                                            <!--label="ICD Code"-->
                                            <!--size="col-md-6"-->
                                            <!--v-model="form_data.diagnosis_icd_code"-->
                                            <!--@change="setHasValue"-->
                                            <!--:disabled="statuses.editingDisabled"-->
                                    <!--&gt;</document-textarea>-->
                                    <div class="form-group col-md-6 input-container input-container-diagnosis">
                                        <label class="control-label diagnosis-label">ICD Code</label>
                                        <div class="fastselect-disabled" v-if="statuses.editingDisabled"></div>
                                        <input type="text"
                                               multiple
                                               id="diagnoseMultipleSelect"
                                               class="tagsInput"
                                               data-user-option-allowed="true"
                                        />
                                    </div>
                                    <document-textarea
                                            name="no_of_visits"
                                            label="# of Visits"
                                            size="col-md-6"
                                            v-model="form_data.no_of_visits"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12 input-container radio-without-label form-group">
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_purpose_rb"
                                                       value="Consult Only"
                                                       name="visit_purpose_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Consult Only
                                            </label>
                                        </div>
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_purpose_rb"
                                                       value="Office Visit"
                                                       name="visit_purpose_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Office Visit
                                            </label>
                                        </div>
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_purpose_rb"
                                                       value="Follow-up"
                                                       name="visit_purpose_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Follow-up
                                            </label>
                                        </div>
                                        <div class="col-xs-3">
                                            <label class="radio-inline">
                                                <input type="radio" class="checkbox-document-form-control"
                                                       v-model="form_data.visit_purpose_rb"
                                                       value="Other"
                                                       name="visit_purpose_rb"
                                                       @change="setHasValue"
                                                       :disabled="statuses.editingDisabled"
                                                >
                                                Other
                                            </label>
                                        </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <document-textarea
                                            name="reason_for_referral"
                                            label="Reason for Referral (Including History, Meds, Test Results, Etc.)"
                                            v-model="form_data.reason_for_referral"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                            name="cpt"
                                            label="CPT"
                                            size="col-md-6"
                                            v-model="form_data.cpt"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="no_og_units"
                                            label="# of Units"
                                            size="col-md-6"
                                            v-model="form_data.no_og_units"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <div class="form-group input-container col-md-6 radio-with-label-column">
                                        <label class="control-label input-label">
                                            Attachments?
                                        </label>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="attachments_rb_1"
                                                       name="attachments_rb"
                                                       v-model="form_data.attachments_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="Yes"
                                                       @change="setHasValue"
                                                >
                                                Yes
                                            </label>
                                        </div>
                                        <div class="col-xs-6 radio">
                                            <label>
                                                <input type="radio"
                                                       id="attachments_rb_2"
                                                       name="attachments_rb"
                                                       v-model="form_data.attachments_rb"
                                                       :disabled="statuses.editingDisabled"
                                                       value="No"
                                                       @change="setHasValue"
                                                >
                                                No
                                            </label>
                                        </div>
                                    </div>
                                    <document-textarea
                                            name="place_of_service"
                                            label="Place of Service"
                                            size="col-md-6"
                                            v-model="form_data.place_of_service"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-input
                                            name="office_phone_number"
                                            label="Office Phone Number"
                                            size="col-md-6"
                                            v-model="form_data.office_phone_number"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-mask="'###-###-####'"
                                            validateRules="regex:^([\d]{3}-){2}[\d]{4}$"
                                    ></document-input>
                                    <document-input
                                            name="office_fax_number"
                                            label="Office Fax Number"
                                            size="col-md-6"
                                            v-model="form_data.office_fax_number"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            v-mask="'###-###-####'"
                                            validateRules="regex:^([\d]{3}-){2}[\d]{4}$"
                                    ></document-input>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                            name="provider_name"
                                            label="Requesting Provider Name"
                                            size="col-md-6"
                                            v-model="form_data.provider_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-textarea>
                                    <document-textarea
                                            name="office_contact_name"
                                            label="Office Contact Name"
                                            size="col-md-6"
                                            v-model="form_data.office_contact_name"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>


                                <br/>
                                <div class="row ">
                                    <p>
                                        THIS IS NOT TO BE USED AS A REFERRAL. THIS IS A REFERRAL REQUEST ONLY.


                                    </p>
                                    <p>
                                        UM Dept FAX: 747-229-2360
                                    </p>
                                    <p>
                                        Customer Relations Phone: 855-359-6323
                                    </p>
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
    import diagnosisCode from './../../../mixins/diagnosis-and-icd-code';

    export default {
        mixins: [validate, save, methods, diagnosisCode],
        data(){
            return{
                document_name: 'axminster-rfr',
                document_title: 'Axminster RFR Form',
                just_icd_code: true,
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
                    provider_name: this.currentProvider.provider_name,
                    address: null,
                    zip_code: null,
                    hmo: null,
                    cert: null,
                    refer_to: null,
                    specialty: null,
                    diagnosis_icd_code: null,
                    no_of_visits: null,
                    reason_for_referral: null,
                    cpt: null,
                    no_og_units: null,
                    place_of_service: null,
                    office_phone_number: null,
                    office_fax_number: null,
                    office_contact_name: null,
                    attachments_rb: null,
                    auto_accident_rb: null,
                    visit_purpose_rb: null,
                    visit_status_rb: null,
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'axminster-rfr';
            let document_name = self.getFormName(menu_item_selector);
//            let document_name = 'axminster-rfr';
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

                    self.initDiagnosisAndIcdCode();

                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                });
            },500);
        },
        methods: {
            getCustomValidation(){

                let error = false;

                this.setDiagnosisIcdCode();

//                this.form_data.diagnosis_icd_code = this.getDiagnosisIcdCode(false);
//
//                if(this.form_data.diagnosis_icd_code){
//                    this.form_data.diagnosis_icd_code = this.form_data.diagnosis_icd_code.trim();
//
//                    if(this.form_data.diagnosis_icd_code === '') {
//                        this.form_data.diagnosis_icd_code = null;
//                    }
//                }

//                this.form_data.diagnosis_icd_code = this.getDiagnosisIcdCode(false);
//
//                if(this.form_data.diagnosis_icd_code) {
//                    this.form_data.diagnosis_icd_code = this.form_data.diagnosis_icd_code.trim();
//                    if(this.form_data.diagnosis_icd_code === '') {
//                        $('#'+this.document_name).find('#diagnoseMultipleSelect').parents('.form-group').addClass('has-error');
//                        error = true;
//                    }
//                    else{
//                        error = false;
//                    }
//                }else {
//                    $('#'+this.document_name).find('#diagnoseMultipleSelect').parents('.form-group').addClass('has-error');
//                    error = true;
//                }
//
//                if(!this.form_data.date_of_service){
//                    this.setDateError('date_of_service');
//                    error = true;
//                }
//                else{
//                    this.removeDateError('date_of_service');
//                }

                return error;
            },
            getValidationMessage(){
                for (let child in this.$children) {

                    if(this.$children[child].name == 'office_phone_number' || this.$children[child].name == 'office_fax_number'){

                        if(this.$children[child].errors.has('office_phone_number') || this.$children[child].errors.has('office_fax_number')){

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
