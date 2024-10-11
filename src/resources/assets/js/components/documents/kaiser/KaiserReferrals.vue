<template>
    <div>
        <div class="modal modal-vertical-center fade" :id="modal_id" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <button
                                    type="button"
                                    class="close"
                                    data-dismiss="modal"
                                    @click.prevent="closeModal"
                                >
                                    &times;
                                </button>
                                <h4 class="modal-title">
                                    Updated Referral Form Submission Process for Kaiser Patients
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        <p>
                            As part of our ongoing efforts to enhance healthcare services, we are now
                            included in a pilot program by Kaiser aimed at streamlining the referral
                            process. It's essential to adhere to the following updated guidelines to
                            ensure compliance and continuity of care for Kaiser patients.
                        </p>

                        <h4 v-if="patientFullName" style="margin-top: 20px;margin-bottom: 20px;">
                            <b>Patient Name: {{ patientFullName }}</b>
                        </h4>

                        <p>
                            <b>IMPORTANT:</b>
                            <b><u>Initial Assessment must be completed</u></b> prior to any referral submissions by using the Lucet system (formerly known as Tridiuum).
                        </p>
                        
                        <div class="row" style="margin-bottom: 5px;">
                            <div class="col-lg-12">
                                <div
                                    class="input-group kaiser-referrals-select-wrapper"
                                    :class="{'has-error': selectedDocumentError}"
                                >
                                    <label>Referral Options:</label>
                                    <select v-model="selectedDocument" class="form-control kaiser-referrals-select">
                                        <option
                                            v-for="option in documentOptions"
                                            :value="option.value" 
                                            :key="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
            
                                    <div v-if="selectedDocumentError" class="invalid-feedback">
                                        {{ selectedDocumentError }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Comment:</label>
                                    <textarea v-model="comment" rows="4" maxlength="255" class="form-control no-resize"></textarea>
                                </div>
                            </div>
                        </div>

                        <p>
                            After clicking <b>Continue</b>, you will be redirected to an external site
                            managed by Kaiser to complete the referral form corresponding to
                            your selection. A comment in the chart of {{ patientFullName || 'the patient' }} will
                            automatically be generated to document this action.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary document-button" @click.prevent="saveAndClose">
                            Continue
                        </button>
                        <button type="button" class="btn btn-default document-button" @click.prevent="closeModal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { START_FILLING_REFERRAL_FORM_COMMENT_TYPE } from '../../../settings';

export default {
    props: {
        patient: {
            type: Object,
            require: false,
        },
    },
    data() {
        return {
            modal_id: 'kaiser-referrals',
            comment: null,
            selectedDocument: null,
            selectedDocumentError: null,
            documentOptions: [
                {
                    label: 'Medication Consultation',
                    value: 1
                },
                {
                    label: 'Intensive Treatment',
                    value: 2
                },
                {
                    label: 'Group Therapy',
                    value: 3
                },
                {
                    label: 'Family Counseling / Couples Counseling',
                    value: 4
                },
                {
                    label: 'Psychological Testing',
                    value: 5
                },
            ]
        }
    },
    computed: {
        provider() {
            return this.$store.state.currentProvider;
        },
        patientFullName() {
            if (!this.patient) {
                return '';
            }

            return this.patient.first_name + ' ' + this.patient.last_name;
        },
    },
    methods: {
        validate() {
            if (!this.selectedDocument) {
                this.selectedDocumentError = 'Referral option is required';
                return false;
            }

            return true;
        },
        closeModal() {
            $('#' + this.modal_id).modal('hide');
            this.resetData();
        },
        resetData() {
            this.comment = null;
            this.selectedDocument = null;
            this.resetErrors();
        },
        resetErrors() {
            this.selectedDocumentError = null;
        },
        saveAndClose() {
            if (!this.validate()) {
                return
            }

            this.resetErrors();

            let storeCommentRequestData = {
                patient_id: parseInt(this.$route.params.id),
                provider_id: this.provider.id,
                comment: this.comment,
                comment_type: START_FILLING_REFERRAL_FORM_COMMENT_TYPE,
                metadata: {
                    document_to_fill_name: this.documentOptions.find((item) => item.value === this.selectedDocument).label,
                }
            };

            this.$store.dispatch("storeComment", storeCommentRequestData).then((response) => {
                if (this.$route.name === 'patient-chart' && this.$route.params.id === String(storeCommentRequestData.patient_id)) {
                    this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {
                        id: storeCommentRequestData.patient_id,
                    });

                    this.closeModal();

                    const url = "https://forms.office.com/pages/responsepage.aspx?id=xHuKPzfjpUeg_A1RLA4F8akECKOe7KRCoejFWmj7J5FUOEVMRkFTS0VCT05VN0xVTFRMMklCMFVTVS4u";
                    window.open(url, '_blank');

                    this.$store.state.currentDocument = null;
                    this.$store.state.currentDocumentEditingStatus = null;
                    this.$store.state.currentDocumentSignature = null;
                }
                this.$store.dispatch("getProviderMessages");
            });
        }
    },
}
</script>

<style>
.kaiser-referrals-select-wrapper {
    width: 100%;
}

.kaiser-referrals-select {
    width: 100%;
    border-radius: 4px !important;
}
</style>
