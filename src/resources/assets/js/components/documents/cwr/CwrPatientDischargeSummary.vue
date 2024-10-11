<template>
    <div>
        <div class="black-layer" v-if="statuses && statuses.saving" style="position: fixed; top: 0;">
            <pageloader add-classes="saving-loader" image-alt="Saving..."></pageloader>
        </div>

        <div class="modal modal-vertical-center fade" :id="document_name" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <button
                                    type="button"
                                    class="close"
                                    data-dismiss="modal"
                                    @click.prevent="closeDocument"
                                >
                                    &times;
                                </button>
                                <h4 class="modal-title" v-html="computed_modal_title()"></h4>
                            </div>
                        </div>
                        <div class="row last-row">
                            <div class="col-lg-12 text-left">
                                Date: {{getFormattedDate()}}
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
                                    <document-input
                                        name="date_of_birth"
                                        label="Date of Birth"
                                        size="col-md-4"
                                        v-model="form_data.date_of_birth"
                                        :disabled="true"
                                    ></document-input>
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
                                    <div class="form-group col-md-6 input-container document-date"
                                        :class="{'has-error': errors.has('date_of_initial'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Date of Initial Assessment</label>
                                        <el-date-picker
                                            v-model="form_data.date_of_initial"
                                            type="date"
                                            name="date_of_initial"
                                            @focus="pickerFocus('date_of_initial')"
                                            @blur="pickerBlur('date_of_initial')"
                                            :editable="false"
                                            :format="datePickerFormat"
                                            :value-format="datePickerValueFormat"
                                            @change="resetDateError('date_of_initial')"
                                            :disabled="statuses.editingDisabled"
                                            :picker-options="datePickerBirthDateOptions"
                                        >
                                        </el-date-picker>
                                    </div>
                                    <div class="form-group col-md-6 input-container document-date"
                                         :class="{'has-error': errors.has('date_of_discharge'), 'div-disabled': statuses.editingDisabled}"
                                    >
                                        <label class="control-label input-label">Date of Discharge</label>
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
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="number_of_session"
                                            label="Number of Session"
                                            v-model="form_data.number_of_session"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="presenting_problem"
                                            label="Presenting Problem/Symptoms"
                                            v-model="form_data.presenting_problem"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="summary_of_treatment"
                                            label="Summary of Treatment"
                                            v-model="form_data.summary_of_treatment"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="reason_for_discharge"
                                            label="Reason for Discharge"
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

                                <div class="row">
                                    <document-textarea
                                            name="recommendation"
                                            label="Recommendation (Include Referrals Provided)"
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
                                                <button
                                                    v-if="!statuses.editingDisabled"
                                                    type="submit"
                                                    class="btn btn-primary document-button"
                                                    @click.prevent="saveDocument"
                                                >
                                                    Save
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-default document-button"
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
        data() {
            return {
                document_name: 'cwr-patient-discharge-summary',
                document_title: 'CWR Patient Discharge Summary',
            }
        },
        beforeMount() {
            if (!this.form_data && this.$store.state.currentDocument) {
                this.patient_id = this.$store.state.currentPatient.id;
                this.form_data = {
                    first_name: this.$store.state.currentPatient.first_name,
                    last_name: this.$store.state.currentPatient.last_name,
                    date_of_birth: this.formatDate(this.$store.state.currentPatient.date_of_birth, this.momentDateFormat),
                    date: this.formatDate(new Date(), this.momentDateFormat),
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_title: this.parseProviderTitle(this.currentProvider.provider_name),
                    provider_license_no: this.currentProvider.license_no,
                    date_of_initial: null,
                    date_of_discharge: null,
                    number_of_session: null,
                    presenting_problem: null,
                    summary_of_treatment: null,
                    reason_for_discharge: null,
                    recommendation: null,
                    continued_treatment_rb: null,
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'cwr-patient-discharge-summary';
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

                }).on('hidden.bs.modal', function() {
                    $('body').removeClass('custom-modal');
                });
            },500);
        },
        methods: {
            getCustomValidation() {
                let error = false;
            }
        },
    }
</script>
