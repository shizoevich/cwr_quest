<template>
    <div
        v-if="completeData"
        id="complete-appointment"
        class="modal appt-modal-vertical-center fade appt-modal appt-modal-success"
        data-backdrop="static"
        data-keyboard="false"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button
                        type="button"
                        class="close"
                        :disabled="is_saving"
                        @click.prevent="closeApptModal()"
                    >
                        &times;
                    </button>
                    <h4 class="modal-title">
                        Complete Appointment
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-6" style="padding-right:10%; margin-bottom:10px;">
                            <h4 class="title">Appointment Info</h4>
                            <div class="row">
                                <div class="col-xs-12">
                                    {{ getFullPatientName() }}, <small class="years-old" v-if="completeData.years_old > 0">{{ completeData.years_old }} years old</small>
                                </div>
                                <div class="col-xs-6">
                                    {{ completeData.appointment_date }}
                                </div>
                                <div class="col-xs-6 text-right">
                                    {{ completeData.appointment_time }}
                                </div>
                            </div>
                        </div>

                        <div class="col-xs-6">
                            <h4 class="title">Insurance</h4>
                            <div class="row">
                                <div class="col-xs-12" v-if="completeData.primary_insurance">
                                   {{ completeData.primary_insurance }}
                                </div>
                                <div class="col-xs-12" v-if="completeData.insurance_plan && completeData.insurance_plan.name">
                                    {{ completeData.insurance_plan.name }}
                                </div>
                                <template v-if="completeData.insurance_plan && completeData.insurance_plan.is_verification_required">
                                    <div class="col-xs-12">
                                        <span
                                            :class="{'almost-overdue-eff-stop-date': completeData.visits_auth_left > 0 && completeData.visits_auth_left <= upcomingReauthorizationRequestsMinVisitsCount, 'overdue-eff-stop-date': completeData.visits_auth_left <= 0}"
                                        >
                                            {{ getVisitsVal(completeData.visits_auth_left) }}
                                        </span>
                                        out of {{ getVisitsVal(completeData.visits_auth) }} visits left
                                    </div>
                                    <div class="col-xs-12" v-if="completeData.eff_start_date && completeData.eff_stop_date">
                                        {{ getFormattedDate(completeData.eff_start_date) }} -
                                        <span :class="getEffClass(completeData)">
                                            {{ getFormattedDate(completeData.eff_stop_date) }}
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div v-if="showPaymentAmount" class="col-xs-12">
                            <h4 class="title co-pay-title">{{ currentTransactionPurposeName }}</h4>
                            <div class="co-pay-container">
                                <div class="co-pay-val">
                                    ${{ paymentAmount }}
                                </div>
                                <div class="cash-container">
                                    <template v-if="paymentAmount">
                                        <h4 style="margin-bottom: 15px;">
                                            Cash / Check / Credit Card
                                        </h4>
                                        <div class="cash-inputs text-center">
                                            <div class="form-group">
                                                <multiselect
                                                    style="width:177px;"
                                                    class="multiselect-green diagnoses-single-select payment-method-select"
                                                    v-model="pay_method"
                                                    :options="pay_methods"
                                                    label="name"
                                                    track-by="id"
                                                    :multiple="false"
                                                    :searchable="false"
                                                    :internal-search="false"
                                                    :showLabels="false"
                                                    placeholder="select type"
                                                    :disabled="is_saving"
                                                ></multiselect>
                                            </div>
                                            <div class="form-group" v-if="pay_method && pay_method.id === 2">
                                                <input
                                                    id="check-no"
                                                    class="form-control"
                                                    type="text"
                                                    placeholder="check #"
                                                    v-model="check_no"
                                                    style="min-height:40px;"
                                                >
                                            </div>
                                        </div>
                                    </template>
                                    <div v-else-if="isCurrentTransactionPurposeCopay" class="alert alert-danger cancellation-fee-alert" style="margin-bottom: 0;">
                                        <span>Co-pay not required or covered by insurance.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="col-xs-12">
                            <hr class="co-pay-divider">
                        </div>

                        <div class="col-xs-12 col-md-8 col-md-offset-2">
                            <div class="form-group">
                                <label class="control-label diagnoses-multiselect-label" style="text-align:left;font-size:14px;">
                                    Diagnosis and ICD Code
                                </label>
                                <multiselect
                                    class="multiselect-green diagnoses-multiselect diagnoses-multiselect-select"
                                    v-model="selected_diagnoses"
                                    :options="diagnoses"
                                    label="full_name"
                                    track-by="id"
                                    :multiple="true"
                                    :searchable="true"
                                    :loading="is_diagnoses_loading"
                                    :internal-search="false"
                                    :showLabels="false"
                                    placeholder="Start Typing for Search Diagnosis and ICD code"
                                    @search-change="searchDiagnoses"
                                ></multiselect>
                            </div>
                            
                            <div>
                                <label class="control-label" style="text-align:left;font-size:14px;">
                                    Frequency of Treatment
                                </label>
                                <div class="form-group">
                                    <multiselect
                                        class="multiselect-green diagnoses-single-select visit-frequency"
                                        v-model="visit_frequency"
                                        :options="visitFrequencies"
                                        label="name"
                                        track-by="id"
                                        :multiple="false"
                                        :searchable="false"
                                        :internal-search="false"
                                        :showLabels="false"
                                    ></multiselect>
                                </div>
                            </div>
                            <div v-if="isVisitFrequencyCommentRequired">
                                <label class="control-label" style="text-align:left;font-size:14px;">
                                    Reason for change in frequency:
                                </label>
                                <div class="form-group">
                                    <input id="visit-frequency-comment" class="form-control" v-model="visit_frequency_comment" type="text"/>
                                </div>
                            </div>

                            <div>
                                <label class="control-label" style="text-align:left;font-size:14px;">
                                    Visit Reason
                                </label>
                                <div class="form-group">
                                    <multiselect
                                        class="multiselect-green diagnoses-single-select reason-for-visit"
                                        v-model="reason_for_visit"
                                        :options="filteredVisitReasonList"
                                        label="name"
                                        track-by="id"
                                        :multiple="false"
                                        :searchable="false"
                                        :internal-search="false"
                                        :showLabels="false"
                                    />
                                </div>
                            </div>
                            <!-- <div v-if="fee_per_visit" class="alert alert-info form-group">
                                You will be paid: {{ fee_per_visit }}$
                            </div> -->

                            <div class="form-group checkbox-form-group" id="confirm-diagnoses-group">
                                <p><b>IMPORTANT!</b></p>
                                <p><b>Please make sure Diagnosis and ICD Codes information provided is complete and accurate before changing the status of this appointment to COMPLETED.</b></p>
                                <p>You will not be able to change Diagnosis and ICD Codes in the Progress Note for this visit once the billing has been submitted to insurance company for reimbursement.</p>
                                <p>Usually the billing is submitted within 24 - 48 hours after the appointment status has been changed to COMPLETED.</p>
                                <label class="control-label">
                                    <input type="checkbox" v-model="confirm_diagnoses">
                                    I understand information provided above and confirm that Diagnosis and ICD Codes specified above are correct.
                                </label>
                            </div>
                            <div class="form-group-divider"></div>
                            <div class="form-group checkbox-form-group" id="confirm-status-changing-group">
                                <label class="control-label">
                                    <input type="checkbox" v-model="confirm_status_changing">
                                    I understand that changing the status of this appointment to 'completed' is used as proof that I provided these services and that billing will be submitted based on this confirmation.
                                </label>
                            </div>
                            <div class="form-group checkbox-form-group" id="confirm-services-group">
                                <label class="control-label">
                                    <input type="checkbox" v-model="confirm_services">
                                    By selecting 'completed', I certify that the patient was present for the scheduled appointment and that the services rendered were medically necessary and appropriate.
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="control-label" style="text-align:left;font-size:14px;" for="complete-appt-comment">Comment</label>
                                <textarea
                                    id="complete-appt-comment"
                                    class="form-control no-resize"
                                    placeholder="Comment..."
                                    v-model="comment"
                                    rows="4"
                                    :disabled="is_saving"
                                    maxlength="255"
                                ></textarea>
                            </div>
                            <p v-if="error_message" class="text-danger">{{ error_message }}</p>
                            <br>
                            <div class="text-center">
                                <el-button @click.prevent="createVisit()" :loading="is_saving" type="success">Complete</el-button>
                            </div>
                        </div>
                        <div class="col-xs-12 text-center">
                            <div class="col-xs-12 text-center">
                                <div class="warning-block">
                                    <div class="text-center" style="margin-botton:10px;">
                                        <b>ATTENTION!!!</b>
                                        THIS ACTION CANNOT BE UNDONE THROUGH THIS SYSTEM.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-loader" v-show="isDataFetching">
                        <pageloader />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect';
    import { PURPOSE_COPAY_ID, PURPOSE_DEDUCTIBLE_ID, PURPOSE_SELF_PAY_ID, WEEKLY_VISIT_FREQUENCY_ID } from '../../settings';
    import {parseMoney} from "../../helpers/parseMoney";

    export default {
        props: {
          isTelehealth: Boolean,
          isDataFetching: Boolean,
        },

        components: { Multiselect },

        data() {
            return {
                pay_method: {
                    id: 3,
                    name: 'credit card',
                },
                pay_methods: [
                    {
                        id: -1,
                        name: 'select type',
                        $isDisabled: true
                    },
                    {
                        id: 1,
                        name: 'cash',
                    },
                    {
                        id: 2,
                        name: 'check',
                    },
                    {
                        id: 3,
                        name: 'credit card',
                    },
                ],
                reason_for_visit: null,
                visit_frequency: null,
                visit_frequency_comment: '',
                initial_visit_frequency_id: null,
                check_no: '',
                is_saving: false,
                error_message: null,
                diagnoses: [],
                selected_diagnoses: [],
                is_diagnoses_loading: false,
                confirm_diagnoses: false,
                confirm_status_changing: false,
                confirm_services: false,
                comment: '',
                // fee_per_visit: null
            };
        },

        computed: {
            isVisitFrequencyCommentRequired() {
                return this.visit_frequency && this.initial_visit_frequency_id !== this.visit_frequency.id && this.visit_frequency.id !== WEEKLY_VISIT_FREQUENCY_ID;
            },

            completeData() {
                return this.$store.state.complete_appointment_data;
            },

            completeAction() {
                return this.$store.state.complete_appointment_action;
            },
            
            isPaymentForbidden() {
                return this.completeData && this.completeData.is_payment_forbidden;
            },

            isCopayForbidden() {
                if (this.isPaymentForbidden || !this.completeData || !this.completeData.co_pay) {
                    return true;
                }

                return this.isTelehealth && this.completeData.insurance_plan && !this.completeData.insurance_plan.need_collect_copay_for_telehealth;
            },

            visitReasonList() {
                return this.$store.state.treatmentModalities;
            },

            filteredVisitReasonList() {
                return this.visitReasonList.filter(treatmentModality => Number(treatmentModality.is_telehealth) === Number(this.isTelehealth));
            },

            upcomingReauthorizationRequestsMinVisitsCount() {
                if (!this.completeData || Array.isArray(this.completeData) || !this.completeData.insurance_plan) {
                    return null;
                }

                return this.completeData.insurance_plan.reauthorization_notification_visits_count;
            },

            visitFrequencies() {
                return this.$store.state.patient_visit_frequencies;
            },

            transactionPurposes() {
                return this.$store.state.transactionPurposes;
            },

            currentTransactionPurpose() {
                if (this.completeData.is_self_pay) {
                    return this.transactionPurposes.find(purpose => purpose.id === PURPOSE_SELF_PAY_ID);
                }

                if (parseMoney(this.completeData.deductible_remaining) > 0) {
                    return this.transactionPurposes.find(purpose => purpose.id === PURPOSE_DEDUCTIBLE_ID);
                }

                return this.transactionPurposes.find(purpose => purpose.id === PURPOSE_COPAY_ID);
            },

            currentTransactionPurposeName() {
                return this.currentTransactionPurpose ? this.currentTransactionPurpose.name : "Co-Pay";
            },

            isCurrentTransactionPurposeCopay() {
                return this.currentTransactionPurpose && this.currentTransactionPurpose.id === PURPOSE_COPAY_ID;
            },

            paymentAmount() {
                if (this.currentTransactionPurpose) {
                    if (this.currentTransactionPurpose.id === PURPOSE_SELF_PAY_ID) {
                        return parseMoney(this.completeData.self_pay);
                    }

                    if (this.currentTransactionPurpose.id === PURPOSE_DEDUCTIBLE_ID) {
                        const deductibleRemaining = parseMoney(this.completeData.deductible_remaining);
                        const insurancePay = parseMoney(this.completeData.insurance_pay);
                        return deductibleRemaining < insurancePay ? deductibleRemaining : insurancePay;
                    }
                }

                return this.isCopayForbidden ? 0 : parseMoney(this.completeData.co_pay);
            },

            provider() {
                return this.$store.state.currentProvider;
            },
            isUserAdmin() {
                return this.$store.state.isUserAdmin;
            },
            showPaymentAmount() {
                return this.isUserAdmin || (this.provider && this.provider.is_collect_payment_available);
            },
        },

        watch: {
            completeData() {
                if (this.completeAction === 'complete') {
                    this.fetchTreatmentModalitiesAndUpdateReason();
                }

                if (this.completeData && this.completeData.diagnoses) {
                    this.selected_diagnoses = this.completeData.diagnoses.slice();
                }

                if (this.completeData.visit_frequency_id) {
                    this.visit_frequency = this.visitFrequencies.find(el => el.id === this.completeData.visit_frequency_id);

                    this.initial_visit_frequency_id = this.visit_frequency.id;
                }

                this.pay_method = {id: 3, name: 'credit card'};
                $('.payment-method-select .multiselect__tags').removeClass('input-error');
            },
            
            isTelehealth() {
                this.updateReasonForVisit();

                this.pay_method = {id: 3, name: 'credit card'};
                $('.payment-method-select .multiselect__tags').removeClass('input-error');
            },

            pay_method() {
                this.check_no = '';

                if (this.pay_method && this.pay_method.id != -1) {
                    $('.payment-method-select .multiselect__tags').removeClass('input-error');
                }
            },

            check_no() {
                if (this.check_no != '') {
                    $('#check-no').removeClass('input-error');
                }
            },

            selected_diagnoses() {
                $('.diagnoses-multiselect-select .multiselect__tags').removeClass('input-error');
                $('.diagnoses-multiselect-label').removeClass('text-red');
                this.confirm_diagnoses = false;
            },

            confirm_diagnoses() {
                if (this.confirm_diagnoses) {
                    $('#confirm-diagnoses-group').removeClass('has-error');
                }
            },

            confirm_status_changing() {
                if (this.confirm_status_changing) {
                    $('#confirm-status-changing-group').removeClass('has-error');
                }
            },

            confirm_services() {
                if (this.confirm_services) {
                    $('#confirm-services-group').removeClass('has-error');
                }
            },

            reason_for_visit(value) {
                if (!value) {
                    // this.fee_per_visit = null;
                    return; 
                }

                $('.reason-for-visit .multiselect__tags').removeClass('input-error');

                // const payload = {
                //     appointmentId: this.completeData.appointment_id,
                //     treatmentModalityId: value.id
                // };
                // this.$store.dispatch('getFeePerVisitForAppointment', payload)
                //     .then(({ data }) => {
                //         this.fee_per_visit = data.fee_per_visit;
                //     })
                //     .catch(() => {
                //         this.fee_per_visit = null;
                //     });
            },

            visit_frequency() {
                $('.visit-frequency .multiselect__tags').removeClass('input-error');
            },

            visit_frequency_comment() {
                $('#visit-frequency-comment').removeClass('input-error');
            },
        },

        mounted() {
            this.searchDiagnoses();
            this.getVisitFrequencies();
            this.getTransactionPurposes();
        },

        methods: {
            searchDiagnoses(query = '') {
                this.is_diagnoses_loading = true;
                axios.get('/api/system/diagnoses/autocomplete?q=' + query).then(response => {
                    this.diagnoses = response.data.diagnoses;
                    this.is_diagnoses_loading = false;
                });
            },

            getVisitFrequencies() {
                if (this.visitFrequencies && this.visitFrequencies.length) {
                    return;
                }

                this.$store.dispatch('getPatientVisitFrequenciesList');
            },

            getTransactionPurposes() {
                if (this.transactionPurposes.length) {
                    return;
                }

                this.$store.dispatch('getTransactionPurposes');
            },

            getFormattedDate(date) {
                return this.$moment(date).format('MM/DD/YYYY');
            },

            getFullPatientName() {
                let name = this.completeData.first_name + " " + this.completeData.last_name;
                if (this.completeData.middle_initial) {
                    name += " " + this.completeData.middle_initial;
                }
                return  name;
            },

            getVisitsVal(val) {
                if (val !== null && val !== undefined) {
                    return val;
                }
                return 0;
            },

            getEffClass(data) {
                let res = '';
                if (data.is_overdue) {
                    res = "overdue-eff-stop-date";
                } else if (data.is_eff_almost_overdue){
                    res = 'almost-overdue-eff-stop-date';
                }
                return res;
            },

            validateForm() {
                let isValid = true;

                if (!this.selected_diagnoses.length) {
                    $('.diagnoses-multiselect-select .multiselect__tags').addClass('input-error');
                    $('.diagnoses-multiselect-label').addClass('text-red');
                    isValid = false;
                }

                if (this.paymentAmount) {
                    if (!this.pay_method) {
                        $('.payment-method-select .multiselect__tags').addClass('input-error');
                        isValid = false;
                    } else if (this.pay_method.id == -1) {
                        $('.payment-method-select .multiselect__tags').addClass('input-error');
                        isValid = false;
                    } else if (this.pay_method.id == 2 && this.check_no == '') {
                        $('#check-no').addClass('input-error');
                        isValid = false;
                    }
                }

                if (!this.confirm_diagnoses) {
                    $('#confirm-diagnoses-group').addClass('has-error');
                    isValid = false;
                }
                if (!this.confirm_status_changing) {
                    $('#confirm-status-changing-group').addClass('has-error');
                    isValid = false;
                }
                if (!this.confirm_services) {
                    $('#confirm-services-group').addClass('has-error');
                    isValid = false;
                }

                if (!this.reason_for_visit) {
                    $('.reason-for-visit .multiselect__tags').addClass('input-error');
                    isValid = false;
                }

                if (!this.visit_frequency) {
                    $('.visit-frequency .multiselect__tags').addClass('input-error');
                    isValid = false;
                }

                if (this.isVisitFrequencyCommentRequired && !this.visit_frequency_comment) {
                    $('#visit-frequency-comment').addClass('input-error');
                    isValid = false;
                }

                return isValid;
            },

            createVisit() {
                this.error_message = null;
                this.is_saving = true;

                if (!this.validateForm()) {
                    this.is_saving = false;
                    return;
                }

                if (this.paymentAmount) {
                    this.payAppointment();
                } else {
                    this.updateDiagnoses();
                }
            },

            payAppointment() {
                let payData = {
                    appointment_id: this.completeData.appointment_id,
                    check_no: this.check_no ? this.check_no : null,
                    payment_amount: this.paymentAmount,
                    transaction_purpose_id: this.currentTransactionPurpose.id
                };
                if (this.pay_methods[this.pay_method.id]) {
                    payData.method = this.pay_methods[this.pay_method.id].name;
                }

                this.$store.dispatch('payAppointment', payData).then(response => {
                    if (response.status === 200) {
                        this.error_message = null;
                        this.updateDiagnoses();
                    } else if (response.status === 409) {
                        this.error_message = response.data.error;
                        this.is_saving = false;
                    }
                });
            },

            updateDiagnoses() {
                this.$store.dispatch('updatePatientDiagnoses', {
                    patient_id: this.completeData.id,
                    data: {
                        diagnoses: this.selected_diagnoses.map(item => item.id),
                    },
                })
                    .then(() => {
                        this.error_message = null;
                        this.completeAppointment();
                    })
                    .catch((error) => {
                        if (error.response.status === 409) {
                            this.error_message = error.response.data.error;
                            this.is_saving = false;
                        } else if (error.response.status === 403) {
                            this.error_message = null;
                            this.completeAppointment();
                        }
                    });
            },

            completeAppointment() {
                // @todo remove
                if (this.reason_for_visit && this.reason_for_visit.id === 16) {
                    try {
                        this.logAdditionalData(`METHOD: completeAppointment; DATA: ${JSON.stringify(this.completeData)}; REASON_FOR_VISIT: ${JSON.stringify(this.reason_for_visit)}; VISIT_REASON_LIST: ${JSON.stringify(this.filteredVisitReasonList)}`);
                    } catch (err) {}
                }

                this.error_message = null;
                let data = {
                    appointmentId: this.completeData.appointment_id,
                    comment: this.comment,
                    patient_id: this.completeData.id,
                    is_telehealth: this.isTelehealth,
                    reason_for_visit: this.reason_for_visit.id,
                    visit_frequency_id: this.visit_frequency.id,
                    change_visit_frequency_comment: this.visit_frequency_comment
                };
                this.$store.dispatch('completeAppointment', data).then(response => {
                    if (response.status === 201) {
                        $('#complete-appointment').modal('hide');
                        this.$store.dispatch("getPatientNotesWithDocumentsPaginated", { id: data.patient_id});
                        this.resetData();
                        this.is_saving = false;
                        this.$emit('completed');
                        this.$store.dispatch('getProviderTodayPatients');
                    } else if (response.status === 404 || response.status === 409) {
                        this.error_message = response.data.error;
                        this.is_saving = false;
                    }
                });
            },

            closeApptModal() {
                $('.input-error').removeClass('input-error');
                $('.has-error').removeClass('has-error');
                $('#complete-appointment').modal('hide');
                this.resetData();
                this.$store.commit('setVal', {key: 'complete_appointment_data', val: []});
            },

            resetData() {
                this.pay_method = {id: 3, name: 'credit card'};
                this.check_no = '';
                this.is_saving = false;
                this.error_message = null;
                this.reason_for_visit = null;
                this.visit_frequency = null;
                this.visit_frequency_comment = '';
                this.initial_visit_frequency_id = null;
                this.selected_diagnoses = [];
                this.is_diagnoses_loading = false;
                this.confirm_diagnoses = false;
                this.confirm_status_changing = false;
                this.confirm_services = false;
                this.comment = '';
            },

            fetchTreatmentModalitiesAndUpdateReason() {
                const params = {
                    patient_id: this.completeData.appointment_patient_id,
                    appointment_id: this.completeData.appointment_id,
                };
                this.$store.dispatch('getTreatmentModalities', params)
                    .then(this.updateReasonForVisit);
            },

            updateReasonForVisit() {
                if (!this.completeData.treatment_modality_id) {
                    return;
                }

                let visitReason = this.visitReasonList.find(el => el.id === this.completeData.treatment_modality_id);
                if (!visitReason) {
                    return;
                }

                if (Number(visitReason.is_telehealth) !== Number(this.isTelehealth)) {
                    visitReason = this.visitReasonList.find(el => visitReason.insurance_procedure_id === el.insurance_procedure_id && Number(el.is_telehealth) === Number(this.isTelehealth));
                }

                this.reason_for_visit = visitReason;
            },

            // @todo remove this method
            logAdditionalData(message) {
                if (!this.completeData.appointment_id) {
                    return;
                }

                const messagePrefix = `APPOINTMENT_ID: ${this.completeData.appointment_id}; ======> `;

                this.$store.dispatch('captureFrontendMessage', {message: (messagePrefix + message)});
            }
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style lang="scss">
#complete-appointment {
    .multiselect__placeholder {
        margin: 0 !important;
    }
}
a.disabled {
    color: gray !important;
    cursor: not-allowed;
}

a.disabled:hover {
    background: inherit !important;
    border: none !important;
}

</style>

<style scoped lang="scss">
    input.co-pay {
        height: 47px;
        border-radius: 7px;
        display: inline-block;
        max-width: 85px;
        font-size: 40px;
        padding: 0 6px;
        -moz-appearance: textfield;
    }
    /* Chrome, Safari, Edge, Opera */
    input.co-pay::-webkit-outer-spin-button,
    input.co-pay::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .has-error .control-label {
        color: red;
    }

    .checkbox-form-group {
        font-size: 14px;
        display: inline-block;
        text-align: left;

        label {
            font-weight: normal;
        }
    }

    .form-group-divider {
        border-bottom: 1px solid #a2a2a2;
        margin-bottom: 20px;
    }

    .co-pay-divider {
        border-top: 1px solid #a2a2a2;
    }
</style>