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
                        <div class="row">
                            <div class="col-lg-12 text-left">
                                Date: {{ getFormattedDate() }}
                            </div>
                            <div class="col-lg-12">
                                <h4 class="modal-title">
                                    Assessment/Diagnostic Interview
                                </h4>
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
                                        size="col-md-3"
                                        v-model="form_data.first_name"
                                        :disabled="true"
                                    ></document-input>
                                    <document-input
                                        name="last_name"
                                        label="Lastname"
                                        size="col-md-3"
                                        v-model="form_data.last_name"
                                        :disabled="true"
                                    ></document-input>
                                    <document-input
                                        name="date_of_birth"
                                        label="Date of Birth"
                                        size="col-md-3"
                                        v-model="form_data.date_of_birth"
                                        :disabled="true"
                                    ></document-input>

                                    <div class="form-group col-md-3">
                                        <label class="control-label" style="z-index:150;">Session Date</label>
                                        <select
                                            v-if="patient"
                                            id="session-date-select"
                                            class="form-control"
                                            name="date_of_service"
                                            v-model="sessionDateSelected"
                                            :disabled="statuses.editingDisabled || !!currentDocumentData"
                                            @change="handleSessionDateChange"
                                            style="padding-left:8px;padding-right:0px;appearance:none;"
                                        >
                                            <option
                                                v-for="option in sessionDateOptions"
                                                :value="option.value"
                                                :key="option.value"
                                            >
                                                {{ option.text }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row input-row">
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
                                    <div class="form-group form-group-bordered col-lg-12">
                                        <label class="control-label">Session Time</label>
                                        <div class="group">
                                            <label class="checkbox-inline wo-pl">
                                                Start Time:
                                                <input readonly id="start_time_picker" type="text" :disabled="statuses.editingDisabled"
                                                       v-model="form_data.start_time"
                                                       class="form-control form-control-xs text-sm with-checkbox"
                                                       maxlength="8" @change="setHasValue" required>
                                            </label>
                                            <label class="checkbox-inline wo-pl">
                                                End Time:
                                                <input readonly id="end_time_picker" type="text" :disabled="statuses.editingDisabled"
                                                       v-model="form_data.end_time"
                                                       class="form-control form-control-xs text-sm with-checkbox"
                                                       maxlength="8" @change="setHasValue" required>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="demographic_information"
                                            label="Patient’s Demographic Information"
                                            v-model="form_data.demographic_information"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="reason_for_referral"
                                            label="Reason for Referral"
                                            v-model="form_data.reason_for_referral"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="history_of_symptoms"
                                            label="History of Present Symptoms"
                                            v-model="form_data.history_of_symptoms"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="family_history"
                                            label="Family History"
                                            v-model="form_data.family_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="social_history"
                                            label="Social History"
                                            v-model="form_data.social_history"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="history_of_services"
                                            label="History of Psychiatric and Psychological Services"
                                            v-model="form_data.history_of_services"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="current_medications"
                                            label="Current Medications"
                                            v-model="form_data.current_medications"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="patients_strengths"
                                            label="Patient’s Strengths"
                                            v-model="form_data.patients_strengths"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="patients_weaknesses"
                                            label="Patient’s Weaknesses"
                                            v-model="form_data.patients_weaknesses"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="patients_ability"
                                            label="Describe Patient’s Ability to Engage and Benefit From Psychotherapy"
                                            v-model="form_data.patients_ability"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <div class="form-group col-lg-12 input-container input-container-diagnosis">
                                        <label class="control-label diagnosis-label" style="z-index:92!important;">Diagnosis</label>
                                        <div
                                            v-if="!form_data.diagnoses_editable || statuses.editingDisabled || !!currentDocumentData" 
                                            class="fastselect-disabled"
                                        ></div>

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
                                            name="treatment_plan"
                                            label="Treatment Plan"
                                            v-model="form_data.treatment_plan"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="long_term_goals"
                                            label="Long Term Goals"
                                            v-model="form_data.long_term_goals"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="short_term_goals"
                                            label="Short Term Goals"
                                            v-model="form_data.short_term_goals"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="treatment_modality"
                                            label="Treatment Modality"
                                            v-model="form_data.treatment_modality"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <document-textarea
                                            name="frequency_of_treatment"
                                            label="Frequency of Treatment"
                                            v-model="form_data.frequency_of_treatment"
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
    import DiagnosesMultiselect from './../../../mixins/diagnoses-multiselect';

    export default {
        mixins: [validate, save, methods, DiagnosesMultiselect],
        data() {
            return {
                document_name: 'cwr-initial-assessment',
                document_title: 'CWR Initial Assessment',
                statuses: {
                    end_time: {
                        h: null,
                        m: null,
                        meridian: null,
                        origin: {}
                    },
                    confirm_diagnoses: false,
                },
                sessionDateOptions: [],
                sessionDateSelected: null
            }
        },
        computed: {
            currentDocumentData() {
                return this.$store.state.currentDocumentData;
            },
            patient() {
                return this.$store.state.currentPatient;
            },
            patientId() {
                return this.patient && this.patient.id;
            },
        },
        watch: {
            start_time() {
                if (!this.form_data.start_time) {
                    return false;
                }
                let tmp1 = this.form_data.start_time.split(':');
                let tmp2 = tmp1[1].split(' ');
                this.statuses.end_time.origin.h = parseInt(tmp1[0]);
                this.statuses.end_time.origin.m = parseInt(tmp2[0]);
                this.statuses.end_time.origin.meridian = tmp2[1];
            },
        },
        beforeMount() {
            if (!this.form_data && this.$store.state.currentDocument) {
                this.patient_id = this.$store.state.currentPatient.id;
                this.form_data = {
                    diagnoses_editable: true,
                    first_name: this.$store.state.currentPatient.first_name,
                    last_name: this.$store.state.currentPatient.last_name,
                    date_of_birth: this.formatDate(this.$store.state.currentPatient.date_of_birth, this.momentDateFormat),
                    date: this.formatDate(new Date(), this.momentDateFormat),
                    birth_date: this.$store.state.currentPatient.date_of_birth,
                    provider_name: this.parseProviderName(this.currentProvider.provider_name),
                    provider_title: this.parseProviderTitle(this.currentProvider.provider_name),
                    provider_license_no: this.currentProvider.license_no,
                    date_of_service: null,
                    appointment_id: null,
                    start_time: moment().tz('America/Vancouver').format('LT'),
                    end_time: moment().tz('America/Vancouver').format('LT'),
                    reason_for_referral: null,
                    demographic_information: null,
                    long_term_goals: null,
                    short_term_goals: null,
                    history_of_symptoms: null,
                    family_history: null,
                    social_history: null,
                    history_of_services: null,
                    current_medications: null,
                    patients_ability: null,
                    patients_strengths: null,
                    patients_weaknesses: null,
                    frequency_of_treatment: null,
                    treatment_plan: null,
                    treatment_modality: null,
                    selected_diagnoses: this.$store.state.currentPatient.diagnoses || []
                }
            }
        },
        mounted() {
            let self = this;
            let menu_item_selector = 'cwr-initial-assessment';
            let document_name = self.getFormName(menu_item_selector);
            self.document_name = document_name;
            self.document_title = self.getFormTitle(menu_item_selector);

            window.setTimeout(() => {
                $('#'+document_name)
                    .on('shown.bs.modal', function() {
                        $('body').addClass('custom-modal');

                        autosize($('#'+document_name).find('textarea'));

                        $('.input-container').on('click', function() {
                            $(this).find('.input-element').focus();
                        });

                        $('#'+document_name).find('input.el-input__inner').addClass('input-element');

                        $('#'+document_name).find('input#start_time_picker').timepicker('setTime', self.form_data.start_time).on('focusin', function() {
                            $(this).timepicker('setTime', self.form_data.start_time);
                            $(this).timepicker('showWidget');
                        }).on('changeTime.timepicker', function(e) {
                            self.form_data.start_time = e.time.value;
                            self.statuses.end_time.h = e.time.hours;
                            self.statuses.end_time.m = e.time.minutes;
                            self.statuses.end_time.meridian = e.time.meridian;
                            self.setHasValue();
                        });

                        $('#'+document_name).find('input#end_time_picker').timepicker('setTime', self.form_data.end_time).on('focusin', function(e) {
                            $(this).timepicker('setTime', self.form_data.end_time);
                            $(this).timepicker('showWidget');
                        }).on('changeTime.timepicker', function(e) {
                            self.form_data.end_time = e.time.value;
                            self.setHasValue();
                        });

                        if (self.$store.state.currentDocumentAppointmentId) {
                            self.initSessionDateWithAppointment(self.$store.state.currentDocumentAppointmentId);
                        } else {
                            self.initSessionDate();
                        }
                    })
                    .on('hidden.bs.modal', function() {
                        $('body').removeClass('custom-modal');
                    });
            }, 200);
        },
        methods: {
            getCustomValidation() {
                let error = false;

                if (this.getCustomValidationDiagnoses()) {
                    error = true;
                }
                if (!this.form_data.date_of_service) {
                    $('#session-date-select').parents('.form-group').addClass('error-focus');
                    error = true;
                }
                if (!this.statuses.confirm_diagnoses) {
                    $('#ia-confirm-diagnoses label').addClass('text-red');
                    error = true;
                }

                return error;
            },
            handleSessionDateChange(event) {
                const selectedValue = event.target.value;
                const selectedItem = this.sessionDateOptions.find(option => option.value.toString() === selectedValue);
                if (!selectedItem) {
                    return;
                }

                this.onSessionDateChanged(selectedItem);
            },
            onSessionDateChanged(selectedItem) {
                this.form_data.appointment_id = selectedItem.appointment_id;
                if (selectedItem.diagnoses && selectedItem.diagnoses.length) {
                    if ('selected_diagnoses' in this.form_data) {
                        this.form_data.selected_diagnoses = selectedItem.diagnoses;
                    } else {
                        this.form_data.selected_diagnoses_1 = selectedItem.diagnoses;
                        this.form_data.selected_diagnoses_2 = [];
                        this.form_data.selected_diagnoses_3 = [];
                    }
                    this.form_data.diagnoses_editable = false;
                } else {
                    if('selected_diagnoses' in this.form_data) {
                        this.form_data.selected_diagnoses = this.patient.diagnoses || [];
                    } else {
                        this.form_data.selected_diagnoses_1 = this.patient.diagnoses || [];
                        this.form_data.selected_diagnoses_2 = [];
                        this.form_data.selected_diagnoses_3 = [];
                    }
                    this.form_data.diagnoses_editable = true;
                }

                this.form_data.date_of_service = selectedItem.value;

                if (this.form_data.start_time !== undefined) {
                    let payload = {
                        patient_id: this.patient.id,
                        date: selectedItem.value
                    };
                    this.$store.dispatch('getAppointmentTimeByAppointmentDate', payload).then(response => {
                        if (response.status === 200 && response.data) {
                            this.form_data.start_time = moment(response.data, 'HH:mm a').format('LT');
                            this.form_data.end_time = moment(response.data, 'HH:mm a').format('LT');
                        }
                    });
                }

                $('#session-date-select').parents('.form-group').removeClass('error-focus');
            },
            initSessionDate() {
                if (!this.patientId) {
                    return;
                }

                $('#session-date-select').parents('.form-group').addClass('select-loader');
                this.$store.dispatch('getAppointmentDocumentDates', {patientId: this.patientId, isInitial: true})
                    .then(response => {
                        this.sessionDateOptions = response.data || [];

                        const initialValues = {
                            text: this.form_data.date_of_service,
                            value: this.form_data.date_of_service
                        };
                        const hasInitServiceOptions = this.sessionDateOptions.find(option => option.value === initialValues.value);
                        if (!hasInitServiceOptions && initialValues.value) {
                            this.sessionDateOptions.push(initialValues);
                        }
                        this.sessionDateSelected = initialValues.value;
                    })
                    .finally(() => {
                        $('#session-date-select').parents('.form-group').removeClass('select-loader');
                    });
            },
            initSessionDateWithAppointment(appointmentId) {
                if (!this.patientId) {
                    return;
                }

                $('#session-date-select').parents('.form-group').addClass('select-loader');
                this.$store.dispatch('getAppointmentDocumentDates', {patientId: this.patientId, isInitial: true})
                    .then(response => {
                        this.sessionDateOptions = response.data || [];

                        const selectedItem = this.sessionDateOptions.find(option => option.appointment_id === appointmentId);
                        if (selectedItem) {
                            this.sessionDateSelected = selectedItem.value;
                            this.onSessionDateChanged(selectedItem);
                            return;
                        }

                        const initialValues = {
                            text: this.form_data.date_of_service,
                            value: this.form_data.date_of_service
                        };
                        const hasInitServiceOptions = this.sessionDateOptions.find(option => option.value === initialValues.value);
                        if (!hasInitServiceOptions && initialValues.value) {
                            this.sessionDateOptions.push(initialValues);
                        }
                        this.sessionDateSelected = initialValues.value;
                    })
                    .finally(() => {
                        $('#session-date-select').parents('.form-group').removeClass('select-loader');
                    });
            },
        },
    }
</script>
