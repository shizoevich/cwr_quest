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
                                    Los Angeles Medical Center Service Area
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
<!--                                    <a href="mailto:WH-OutsideMedicalCase-Management@kp.org">WH-OutsideMedicalCase-Management@kp.org</a>-->
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
                                    <date-of-service
                                            :editingDisabled="statuses.editingDisabled || !!this.$store.state.currentDocumentData"
                                            :patient="Boolean(patient)"
                                    ></date-of-service>
                                </div>

                                <div class="row  input-row">
                                    <document-textarea
                                            name="mrn"
                                            label="Kaiser MRN"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="living_with"
                                            label="Living With"
                                            size="col-md-4"
                                            v-model="form_data.living_with"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                    <document-textarea
                                            name="individuals_present"
                                            label="Individuals Present"
                                            size="col-md-4"
                                            v-model="form_data.individuals_present"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row  input-row">
                                    <document-textarea
                                            name="other_sources"
                                            label="Other Sources of Information"
                                            size="col-md-12"
                                            v-model="form_data.other_sources"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="presenting_concerns"
                                            label="Presenting Concerns (Include Reasons for Seeking tx, General Functioning at School and Home)"
                                            v-model="form_data.presenting_concerns"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="behavioral_health_history"
                                            label="Behavioral Health History"
                                            v-model="form_data.behavioral_health_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="pre_natal"
                                            label="Pre-Natal or Early Life Trauma"
                                            v-model="form_data.pre_natal"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="drug_or_alcohol_use"
                                            label="Drug or Alcohol Use (Teen or Family)"
                                            v-model="form_data.drug_or_alcohol_use"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="diagnostic_impression"
                                            label="Diagnostic Impression"
                                            v-model="form_data.diagnostic_impression"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="treatment_plan"
                                            label="Assessment and Treatment Plan"
                                            v-model="form_data.treatment_plan"
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
                document_name: 'kp-initial-assessment-child-la',
                document_title: 'KP Initial Assessment (Child) - Los Angeles',
            }
        },
        beforeMount(){
            if(!this.form_data && this.$store.state.currentDocument){
                this.patient_id = this.$store.state.currentPatient.id;
                this.form_data = {
                    first_name: this.$store.state.currentPatient.first_name,
                    last_name: this.$store.state.currentPatient.last_name,
                    date_of_service: null,
                    date: this.formatDate(new Date(), this.momentDateFormat),
                    // birth_date: this.$store.state.currentPatient.date_of_birth,
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_title: this.parseProviderTitle(this.currentProvider.provider_name),
                    provider_license_no: this.currentProvider.license_no,
                    provider_email: this.currentProvider.email,
                    provider_phone: this.currentProvider.phone,
                    // length_of_session: null,
                    mrn: null,
                    living_with: null,
                    individuals_present: null,
                    other_sources: null,
                    presenting_concerns: null,
                    behavioral_health_history: null,
                    pre_natal: null,
                    drug_or_alcohol_use: null,
                    diagnostic_impression: null,
                    treatment_plan: null,
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'kp-initial-assessment-child-la';
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

//                    $('[name="provider_phone"]').mask('000-000-0000');
                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                });
            },500);
        },
        methods: {
            getCustomValidation(){

                let error = false;

//                if(!this.form_data.birth_date){
//                    this.setDateError('birth_date');
//                    error = true;
//                }
//                else{
//                    this.removeDateError('birth_date');
//                }

                if(this.getCustomValidateionDateOfService()){

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
