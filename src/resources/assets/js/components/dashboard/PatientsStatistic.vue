<template>
    <div class="container">
        <div class="row">
            <div class="text-center page-loader-wrapper" v-show="loading.diagrams || loading.page">
                <pageloader add-classes="page-loader"></pageloader>
            </div>
            <div class="vue-wrapper" v-show="!loading.page && !loading.diagrams">
                <doctors-availability-alert />

                <h2 class="text-center">Missing upcoming appointments</h2>

                <ul class="nav nav-tabs" v-if="is_admin">
                    <li class="active"><a data-toggle="tab" href="#data-table-tab">Data table</a></li>
                    <li><a data-toggle="tab" href="#visual-diagrams-tab">Visual Diagrams</a></li>
                </ul>

                <div class="tab-content">
                    <div id="data-table-tab" class="tab-pane fade in active">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div v-if="is_admin" style="display:inline-block;margin-right:20px;">
                                                    <label>Therapist Name</label>
                                                    <select class="form-control provider-select" v-model="selected_provider_id">
                                                        <option value="-1">All</option>
                                                        <option v-for="provider in provider_list" :value="provider.id">{{provider.provider_name}}</option>
                                                    </select>
                                                </div>
                                                <patient-status-checkboxes with-colors="true" :invisible-statuses-for-doctors="invisible_statuses_for_doctors"
                                                    v-show="selected_provider_id || !is_admin"/>
                                                <div class="table-responsive" v-show="selected_provider_id || !is_admin">
                                                    <table id="statistic-table"
                                                           class="table table-condenced table-striped table-bordered">
                                                        <thead>
                                                        <tr>
                                                            <th></th>
                                                            <th>Patient</th>
                                                            <th>Insurance</th>
                                                            <th>Status</th>
                                                            <th>Therapist Name</th>
                                                            <th>Number of authorized visits</th>
                                                            <th>Number of visits created</th>
                                                            <th>Last session (days ago)</th>
                                                            <!--Stop watching btn-->
                                                            <!--<th></th>-->
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr v-for="(patient, index) of patients" v-if="patient.appointment_count > 0">
                                                            <td class="text-center">{{index+1}}</td>
                                                            <td>
                                                                <a :href="`${'/chart/' + patient.id}`">{{ patientFullName(patient) }}</a>
                                                            </td>
                                                            <td class="insurance-container">
                                                                <p class="insurance-preview" v-if="patient.primary_insurance && getInsurancePreview(patient).has_preview">
                                                                    {{getInsurancePreview(patient).text}}
                                                                </p>
                                                                <p :class="{'insurance-full': getInsurancePreview(patient).has_preview}">
                                                                    {{patient.primary_insurance}}
                                                                    <span v-if="patient.secondary_insurance && patient.secondary_insurance.length">
                                                                        ({{patient.secondary_insurance}})
                                                                    </span>
                                                                </p>
                                                            </td>
                                                            <td :style="{color: '#' + patient.status_color}">
                                                                {{patient.status}}
                                                            </td>
                                                            <td>
                                                                {{ patient.provider_name }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{ getVisitsAuth(patient) }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{ patient.appointment_count }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{ patient.last_appointment_date }}
                                                            </td>
                                                            <!--Stop watching btn-->
                                                            <!--<td class="text-center">-->
                                                                <!--<button :id="`${'stop-watching-'+index}`" type="button"-->
                                                                        <!--class="btn btn-danger btn-sm"-->
                                                                        <!--@click="confirmStopWatchingDialog(patient.id, index)">Stop watching-->
                                                                <!--</button>-->
                                                            <!--</td>-->
                                                        </tr>
                                                        </tbody>
                                                        <tfoot>
                                                        <tr>
                                                            <th></th>
                                                            <th>Patient</th>
                                                            <th>Insurance</th>
                                                            <th>Status</th>
                                                            <th>Therapist Name</th>
                                                            <th>Number of authorized visits</th>
                                                            <th>Number of visits created</th>
                                                            <th>Last session (days ago)</th>
                                                            <!--Stop watching btn-->
                                                            <!--<th></th>-->
                                                        </tr>
                                                        </tfoot>
                                                    </table>
                                                </div><!--/.table-responsive-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="visual-diagrams-tab" class="tab-pane fade" v-if="is_admin">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <patients-statistics-visual-diagrams />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


        <!--MODALS-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-stop-watching-modal" tabindex="-1"
             role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header" v-if="stop_watching.index !== null">
                        <h5 class="modal-title">Are you sure you want to stop watching <strong>{{patientFullName(patients[stop_watching.index])}}</strong>?</h5>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="stop-watching-reason">Reason</label>
                            <select id="stop-watching-reason" class="form-control" v-model="stop_watching_reason">
                                <option>Discharged</option>
                                <option>Lost</option>
                                <option>Inactive</option>
                                <option value="-1">Other</option>
                            </select>
                        </div>
                        <div class="form-group" v-if="stop_watching_reason === '-1'">
                            <label for="stop-watching-other-reason">Other Reason</label>
                            <input maxlength="255" id="stop-watching-other-reason" v-model="stop_watching_other_reason" type="text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="stop-watching-comment">Comment</label>
                            <textarea maxlength="255" rows="5" id="stop-watching-comment" v-model="stop_watching_comment" type="text" class="form-control no-resize"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <pageloader add-classes="stop-watching-loader" v-if="loading.stop_watching"></pageloader>
                        <button type="button" class="btn btn-danger" @click="stopWatching" v-else>OK</button>
                        <button type="button" class="btn btn-secondary" :disabled="loading.stop_watching" @click="cancelStopWatching">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import rowNumbersMixin from "../../mixins/row-numbers";

    export default {
        mixins: [rowNumbersMixin],

        data() {
            return {
                patients: {},
                loading: {
                    page: false,
                    stop_watching: false,
                    diagrams: false,
                },
                stop_watching: {
                    patient_id: null,
                    index: null
                },
                table: null,
                selected_provider_id: 0,
                selected_statuses: [1],
                stop_watching_reason: null,
                stop_watching_other_reason: '',
                stop_watching_comment: '',
                invisible_statuses_for_doctors: ['Unassign'],
            }
        },

        watch: {
            selected_provider_id() {
                this.init();
            },

            stop_watching_reason() {
                if(this.stop_watching_reason !== '-1') {
                    this.stop_watching_other_reason = '';
                }

                if(this.stop_watching_reason) {
                    $('select#stop-watching-reason').removeClass('input-error');
                    $('label[for=stop-watching-reason]').removeClass('label-error');
                }
            },

            stop_watching_other_reason() {
                if(this.stop_watching_other_reason !== '') {
                    $('input#stop-watching-other-reason').removeClass('input-error');
                    $('label[for=stop-watching-other-reason]').removeClass('label-error');
                }
            },
            stop_watching_comment() {
                if(this.stop_watching_comment !== '') {
                    $('textarea#stop-watching-comment').removeClass('input-error');
                    $('label[for=stop-watching-comment]').removeClass('label-error');
                }
            }
        },

        computed: {
            provider_list() {
                return this.$store.state.provider_list;
            },

            is_admin() {
                return this.$store.state.isUserAdmin;
            },

            patient_statuses() {
                return this.$store.state.patient_statuses;
            },
        },

        mounted() {
            this.$store.dispatch('isAdmin');
            this.$store.dispatch('getProviderList');
            this.init([1]);
        },

        methods: {

            getVisitsAuth(patient) {
                if(!patient.visits_auth) {
                    return 0;
                }
                return patient.visits_auth;
            },

            getInsurancePreview(patient) {
                let text = "";
                let max_digits_in_text = 10;
                let has_preview = false;
                if(patient) {
                    if(patient.primary_insurance) {
                        text += patient.primary_insurance;
                    }
                    if(patient.secondary_insurance) {
                        text += ' (' + patient.secondary_insurance + ')';
                    }
                    if(text.length > max_digits_in_text) {
                        text = text.substr(0, max_digits_in_text) + '...';
                        has_preview = true;
                    }
                }
                return {
                    text: text,
                    has_preview: has_preview
                };
            },

            setLoadingPage(val) {
                this.loading.page = val;
            },

            getStatusesDataset() {
                let statuses = this.patient_statuses;
                let sel_statuses = this.selected_statuses;
                let dataset = [];
                for(let i = 0; i < statuses.length; i++) {
                    if(sel_statuses[i]) {
                        dataset.push(statuses[i]['id']);
                    }

                }
                return dataset;
            },

            setSelectedStatuses(statuses, flag) {
                this.selected_statuses = statuses;
                if(flag) {
                    this.init();
                }
            },

            getSelectedStatuses() {
                return this.selected_statuses;
            },

            setLoadingDiagrams(status) {
                this.loading.diagrams = status;
            },

            init(defStatuses) {
                let provider_id = this.selected_provider_id;
                if(provider_id === 0 && this.is_admin) {
                    return false;
                }
                let statuses = [];
                if(defStatuses) {
                    statuses = defStatuses;
                } else {
                    statuses = this.getStatusesDataset();
                }

                this.loading.page = true;
                let data = {
                    providerId: provider_id,
                    statuses: []
                };
                if(statuses) {
                    data.statuses = statuses;
                }
                this.$store.dispatch('getPatientsStatistic', data).then(response => {
                    this.patients = response.data;
                    this.loading.page = false;
                    if(this.table) {
                        this.table.destroy();
                    }
                    const self = this;
                    window.setTimeout(() => {
                        this.table = $('#statistic-table').DataTable({
                            'paging': false,
                            'lengthChange': false,
                            'searching': true,
                            'ordering': true,
                            'info': false,
                            'autoWidth': false,
                            'order': [[1, 'asc']],
                            columns: [
                                {searchable: false, sortable: false},
                                null,   //patient
                                {searchable: false},
                                {searchable: false},    //status
                                {searchable: false},   //therapist name
                                {searchable: false},   //Number of authorized visits
                                {searchable: false},   //amount of visits
                                {searchable: false},   //date of last visit
//                                {sortable: false, searchable: false}//Stop watching btn
                            ],
                            fnDrawCallback: function () {
                                self.updateTableRowNumbers(this);
                            }
                        });
                    }, 500);
                });
            },

            /**
             * Returns patient full name (Format: Firstname Lastname)
             * @param patient
             * @returns {string}
             */
            patientFullName(patient) {
                return patient.first_name + ' ' + patient.last_name;
            },

            /**
             * Returns formatted date
             * @param timestamp
             */
            getFormattedDate(timestamp) {
                return this.$moment(timestamp * 1000).format('MM/DD/Y');
            },

            /**
             * Show stop watching confirm dialog
             * @param patientId
             * @param index
             */
            confirmStopWatchingDialog(patientId, index) {
                this.stop_watching.patient_id = patientId;
                this.stop_watching.index = index;
                $('#confirm-stop-watching-modal').modal('show');
            },

            /**
             * Close confirm dialog
             */
            cancelStopWatching() {
                $('#confirm-stop-watching-modal').modal('hide');
                this.stop_watching.patient_id = null;
                this.stop_watching.index = null;
                this.stop_watching_reason = null;
                this.stop_watching_other_reason = '';
                this.stop_watching_comment = '';
            },
            
            stopWatching() {
                let has_errors = false;
                if(!this.stop_watching_reason) {
                    $('select#stop-watching-reason').addClass('input-error');
                    $('label[for=stop-watching-reason]').addClass('label-error');
                    has_errors = true;
                } else {
                    if(this.stop_watching_reason === '-1') {
                        this.stop_watching_other_reason = this.stop_watching_other_reason.trim();
                        if(this.stop_watching_other_reason.trim() === '') {
                            $('input#stop-watching-other-reason').addClass('input-error');
                            $('label[for=stop-watching-other-reason]').addClass('label-error');
                            has_errors = true;
                        }
                    }
                }
                this.stop_watching_comment = this.stop_watching_comment.trim();
                if(this.stop_watching_comment === '') {
                    $('textarea#stop-watching-comment').addClass('input-error');
                    $('label[for=stop-watching-comment]').addClass('label-error');
                    has_errors = true;
                }

                if(has_errors) {
                    return false;
                }
                this.table.row($('#statistic-table #stop-watching-'+this.stop_watching.index).parents('tr')).remove().draw();
                this.loading.stop_watching = true;
                let data = {
                    patient_id: this.stop_watching.patient_id,
                    reason: this.stop_watching_reason,
                    other_reason: this.stop_watching_other_reason,
                    comment: this.stop_watching_comment
                };
                this.$store.dispatch('stopWatching', data).then(response => {
//                    this.patients = response;
                    this.loading.page = true;
                    this.init();
                    this.cancelStopWatching();
                    this.loading.stop_watching = false;
                });
            }
        }
    }
</script>

<style scoped>
    .page-loader-wrapper {
        height: 100vh;
    }

    .page-loader-wrapper:before {
        display: inline-block;
        vertical-align: middle;
        content: " ";
        height: 100%;
    }

    .page-loader {
        max-width: 200px;
        max-height: 200px;
    }

    .stop-watching-loader {
        max-height:35px;
        max-width:35px;
        margin-right: 10px;
    }
</style>