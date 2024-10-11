<template>
    <div class="appointments-table-container">
        <div class="appointments-wrapper">
            <h5 class="table-title">Visit Created Appointments ({{ appointments.length }}):</h5>
            <div class="table-responsive" v-if="appointments.length">
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>

                            <template v-if="is_admin">
                                <th>Expected Length</th>
                                <th>Actual Length</th>
                            </template>

                            <th>Provider/Staff</th>
                            <th>Progress Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(appointment, index) in appointments" :class="{ odd: (index % 2 === 0) }" :key="index">
                            <td v-html="appointment.date"></td>
                            <td>{{ appointment.formatted_time }}</td>

                            <template v-if="is_admin">
                                <td>{{ appointment.visit_length }}</td>
                                <td>{{ appointment.actual_visit_length || '-' }}</td>
                            </template>

                            <td>{{ appointment.provider_name }}</td>
                            <td>
                                {{ appointment.note_exists.title }}
                                <span v-if="!is_read_only_mode">
                                    <button v-if="appointment.show_initial_assessment_electronic_button" 
                                        style="margin-left: 10px;" class="btn btn-primary btn-sm"
                                        @click="$parent.getElectronicDocument(appointment.note_exists.initial_assessment_type_slug, appointment.note_exists.initial_assessment_id)">
                                        Open
                                    </button>
                                    <button v-if="appointment.show_initial_assessment_file_button"
                                        style="margin-left: 10px;" class="btn btn-primary btn-sm"
                                        @click="openInitialAssessment(appointment.note_exists.initial_assessment_file_name)">
                                        Open
                                    </button>
                                    <button v-if="appointment.show_open_electronic_note_button" style="margin-left: 10px;"
                                        class="btn btn-primary btn-sm"
                                        @click.prevent="$emit('showPn', appointment.note_exists.note_id)">
                                        Open
                                    </button>

                                    <button
                                        v-if="appointment.show_add_initial_assessment_button"
                                        type="button"
                                        style="margin-left: 10px;"
                                        data-toggle="modal"
                                        data-target="#cwr-initial-assessment"
                                        class="btn btn-primary btn-sm"
                                        :disabled="statuses.loading_initial_assessment"
                                        @click.prevent="showAddInitialAssessmentForm(appointment.id)"
                                    >
                                        Add Initial Assessment
                                    </button>
                                    <template v-if="appointment.show_sync_initial_assessment_from_tridiuum_button">
                                        <i
                                            v-if="!statuses.loading_initial_assessment"
                                            class="fa fa-refresh fa-lg sync-initial-assessment-icon"
                                            title="Reload Initial Assessment from Lucet"
                                            @click.prevent="loadTridiuumInitialAssessment(appointment.id, appointment.patients_id)"
                                        />
                                        <pageloader
                                            v-else
                                            add-classes="sync-initial-assessment-loader"
                                            title="Synchronization"
                                        />
                                    </template>

                                    <button
                                        v-if="appointment.show_add_progress_note_button && (appointment.is_initial != true)"
                                        style="margin-left: 10px;" class="btn btn-sm btn-primary"
                                        :disabled="statuses.loading_note_blank" @click.prevent="showAddPnForm(appointment.id)">
                                        Add Progress Note 
                                    </button>
                                    <button
                                        v-if="appointment.show_provider_finalize_button && (appointment.is_initial != true)"
                                        style="margin-left: 10px;" class="btn btn-primary btn-sm"
                                        @click.prevent="$emit('showPn', appointment.note_exists.note_id)">
                                        Finalize This Note
                                    </button>
                                    <button v-if="appointment.show_admin_finalize_button" style="margin-left: 10px;"
                                        class="btn btn-primary btn-sm"
                                        @click.prevent="$emit('showPn', appointment.note_exists.note_id)">
                                        Open
                                    </button>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="no-appointments" v-else>
                No Visit Created appointment records for this patient.
            </div>
        </div>

        <!--Modals-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
            id="confirm-uncheck-modal" role="dialog" v-show="patient">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4>Are you sure you want to uncheck all checkboxes?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click.prevent="uncheckPaperNotes()">Yes</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FileInfo from '../mixins/file-info';
import { Notification } from "element-ui";

export default {
    data() {
        return {
            statuses: {
                loading_note_blank: false,
                loading_initial_assessment: false,
            },
        }
    },

    mixins: [
        FileInfo,
    ],

    computed: {
        appointments() {
            return this.$store.state.patient_visit_created_appointments;
        },

        is_admin() {
            return this.$store.state.isUserAdmin;
        },

        is_read_only_mode() {
            return this.$store.state.is_read_only_mode;
        },

        patient() {
            return this.$store.state.currentPatient;
        },
    },

    methods: {
        openInitialAssessment(document_name) {
            if (this.isFileHasPreview(document_name)) {
                window.open('/patient/preview-document/' + document_name, '_blank');
            } else {
                window.open('/patient/download-document/' + document_name, '_blank');
            }
        },

        showAddInitialAssessmentForm(appointmentId) {
            this.$emit('addInitialAssessment', appointmentId);
        },

        loadTridiuumInitialAssessment(appointmentId, patientId) {
            const payload = {
                appointment_id: appointmentId,
                patient_id: patientId
            };

            this.statuses.loading_initial_assessment = true;

            this.$store.dispatch('loadTridiuumInitialAssessment', payload).then((response) => {
                this.statuses.loading_initial_assessment = false;

                if (response.data.message) {
                    const type = response.data.status;
                    const message = response.data.message;

                    Notification[type]({
                        title: type.charAt(0).toUpperCase() + type.slice(1),
                        message: message,
                        type: type,
                    });
                }

                if (response.data.status === 'success') {
                    this.$emit('reloadTabs');
                }
            });
        },

        openConfirmUncheckModal() {
            $('#confirm-uncheck-modal').modal('show');
        },

        uncheckPaperNotes() {
            axios({
                method: 'post',
                url: '/dashboard/uncheck-paper-notes',
                data: { patientId: this.patient.id },
            }).then(() => {
                this.$store.dispatch('getPatientVisitCreatedAppointments', this.patient.id);
                this.$store.dispatch('getPatientNoteAndAppointmentCount', this.patient.id);
                this.$store.dispatch('getSearchedPatients', { query: "" });
            }).catch(error => {
                console.log(error);
            });
            $('#confirm-uncheck-modal').modal('hide');
        },

        showAddPnForm(appointmentId) {
            this.$emit('addPn', appointmentId);
            this.statuses.loading_note_blank = true;
            let interval = window.setInterval(() => {
                this.statuses.loading_note_blank = this.$parent.getLoadingNoteBlankStatus();
                if (!this.statuses.loading_note_blank) {
                    clearInterval(interval);
                }
            }, 1000);
        },

        changePnStatus(appointment) {
            appointment.note_exists.saving = true;
            let payload = {
                on_paper: appointment.note_exists.on_paper,
                appointment_id: appointment.id
            };
            this.$store.dispatch('changeOnPaperAppointmentNote', payload).then(response => {
                if (!response.data.success) {
                    appointment.note_exists.on_paper = !appointment.note_exists.on_paper;
                } else {
                    this.$store.dispatch('getPatientNoteAndAppointmentCount', this.patient.id);
                }
                appointment.note_exists.saving = false;
            });
        }
    }
}
</script>

<style lang="scss" scoped>
.sync-initial-assessment-icon {
  cursor: pointer;
  opacity: 0.5;
  margin-left: 4px;

  &:hover {
    opacity: 1;
  }
}

.sync-initial-assessment-loader {
  width: 16px;
  margin-left: 4px;
}
</style>
