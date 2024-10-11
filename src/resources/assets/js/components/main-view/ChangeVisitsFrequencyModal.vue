<template>
    <div
        id="modal-change-visits-frequency"
        class="modal modal-vertical-center fade"
        data-backdrop="static"
        data-keyboard="false"
        role="dialog"
    >
        <div class="modal-dialog">
            <div class="modal-content" v-loading="saving">
                <div class="modal-header">
                    <h4 class="modal-title">Change Frequency of Treatment</h4>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between">
                        <div class="flex-1">
                            <div
                                class="input-group"
                                :class="{'has-error': visitFrequencyError}"
                            >
                                <label>Frequency of Treatment:</label>
                                <select v-model="visitFrequency" class="form-control visit-frequency-select">
                                    <option
                                        v-for="item in visitFrequencies"
                                        :value="item.id"
                                        :key="item.id"
                                    >
                                        {{ item.name }}
                                    </option>
                                </select>
        
                                <div v-if="visitFrequencyError" class="invalid-feedback">
                                    {{ visitFrequencyError }}
                                </div>
                            </div>
                        </div>

                        <div v-if="visitFrequencyCommentRequired" class="flex-1">
                            <div
                                class="input-group"
                                :class="{'has-error': visitFrequencyCommentError}"
                            >
                                <label>Reason for change in frequency:</label>
                                <input v-model="visitFrequencyComment" class="form-control" type="text" style="border-radius: 4px;"/>
        
                                <div v-if="visitFrequencyCommentError" class="invalid-feedback">
                                    {{ visitFrequencyCommentError }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click.prevent="changeVisitsFrequency"
                    >
                        Save
                    </button>
                    <button
                        type="button"
                        class="btn btn-default"
                        @click.prevent="closeModal"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { WEEKLY_VISIT_FREQUENCY_ID } from '../../settings';

export default {
    props: {
        patient: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            saving: false,
            visitFrequency: null,
            visitFrequencyError: '',
            visitFrequencyComment: '',
            visitFrequencyCommentError: '',
        };
    },
    computed: {
        visitFrequencies() {
            return this.$store.state.patient_visit_frequencies;
        },
        patientVisitFrequencyId() {
            return this.patient.visit_frequency_id;
        },
        visitFrequencyCommentRequired() {
            return this.visitFrequency !== this.patientVisitFrequencyId && this.visitFrequency !== WEEKLY_VISIT_FREQUENCY_ID;
        },
    },
    watch: {
        visitFrequency() {
            this.visitFrequencyError = '';
        },
        patientVisitFrequencyId() {
            this.getVisitFrequencies();
            this.setVisitFrequency();
        }
    },
    mounted() {
        this.getVisitFrequencies();
        this.setVisitFrequency();
    },
    methods: {
        getVisitFrequencies() {
            if (this.visitFrequencies && this.visitFrequencies.length) {
                return;
            }

            this.$store.dispatch('getPatientVisitFrequenciesList');
        },
        setVisitFrequency() {
            this.visitFrequency = this.patientVisitFrequencyId || null;
        },
        closeModal() {
            $("#modal-change-visits-frequency").modal("hide");
            this.resetData();
        },
        resetData() {
            this.saving = false;
            this.visitFrequency = null;
            this.visitFrequencyError = '';
            this.visitFrequencyComment = '';
            this.visitFrequencyCommentError = '';
        },
        changeVisitsFrequency() {
            if (!this.validate()) {
                return;
            }

            this.saving = true;
            let payload = {
                patient_id: this.patient.id,
                visit_frequency_id: this.visitFrequency,
                comment: this.visitFrequencyComment
            };
            this.$store.dispatch('updatePatientVisitFrequency', payload)
                .then(() => {
                    this.closeModal();
                    this.$store.dispatch('getPatient', { patientId: this.patient.id });
                    this.$store.dispatch("getPatientNotesWithDocumentsPaginated", { id: this.patient.id,});
                })
                .finally(() => {
                    this.saving = false;
                });
        },
        validate() {
            let hasError = false;

            if (!this.visitFrequency) {
                this.visitFrequencyError = 'This field is required';
                hasError = true;
            }

            if (this.visitFrequencyCommentRequired && !this.visitFrequencyComment) {
                this.visitFrequencyCommentError = 'This field is required';
                hasError = true;
            }

            return !hasError;
        }
    }
};
</script>

<style scoped>
.visit-frequency-select {
    width: 100%;
    max-width: 254px;
    border-radius: 4px !important;
    float: none;
    font-weight: 700;
}
</style>
  