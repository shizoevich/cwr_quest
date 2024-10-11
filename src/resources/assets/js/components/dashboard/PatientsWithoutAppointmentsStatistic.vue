<template>
    <div class="container">
        <div class="row">
            <div class="text-center page-loader-wrapper" v-if="loading.page">
                <pageloader add-classes="page-loader"></pageloader>
            </div>
            <div class="vue-wrapper" v-else>
                <doctors-availability-alert />

                <h2 class="text-center">Patients with no appointments</h2>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <select class="form-control provider-select" v-model="display_depth" @change="init()"
                                        style="display:inline-block;margin-right:20px;">
                                    <option value="-1">All</option>
                                    <option v-for="i in 3" :value="`${i*7}`">{{i}} week</option>
                                    <option v-for="i in 3" :value="`${i*30}`">{{i}} month</option>
                                </select>

                                <patient-status-checkboxes with-colors="true" />
                                <div class="table-responsive">
                                    <table id="statistic-table"
                                           class="table table-condenced table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Patient</th>
                                            <th>Therapist Name</th>
                                            <th>Insurance</th>
                                            <th>Status</th>
                                            <th>Date of creation</th>
                                            <!--<th style="width:50px"></th>--> <!--Stop watching button-->
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(patient, index) of patients">
                                            <td class="text-center">{{index+1}}</td>
                                            <td>
                                                <a :href="`${'/chart/' + patient.id}`">{{ patientFullName(patient) }}</a>
                                            </td>
                                            <td>
                                                <ul class="provider-list">
                                                    <li v-for="provider in patient.providers">{{provider.provider_name}}</li>
                                                </ul>
                                            </td>
                                            <td>
                                                {{patient.primary_insurance}}
                                                <span v-if="patient.secondary_insurance && patient.secondary_insurance.length">
                                                    ({{patient.secondary_insurance}})
                                                </span>
                                            </td>
                                            <td :style="{color: '#'+patient.hex_color}">
                                                {{patient.status}}
                                                <span v-if="patient.secondary_insurance && patient.secondary_insurance.length">
                                                    ({{patient.secondary_insurance}})
                                                </span>
                                            </td>
                                            <td>
                                                <span style="display:none;">{{patient.created_patient_date_timestamp}}</span>   <!--for sorting items in column-->
                                                {{patient.created_patient_date}}
                                            </td>
                                            <!--Stop watching button-->
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
                                            <th>Therapist Name</th>
                                            <th>Insurance</th>
                                            <th>Status</th>
                                            <th>Date of creation</th>
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

        <!--MODALS-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-stop-watching-modal" tabindex="-1"
             role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body" v-if="stop_watching.index !== null">
                        Are you sure you want to stop watching <strong>{{patientFullName(patients[stop_watching.index])}}</strong>?
                    </div>
                    <div class="modal-footer">
                        <pageloader add-classes="stop-watching-loader" v-if="loading.stop_watching"></pageloader>
                        <button type="button" class="btn btn-danger" @click="stopWatching" v-else>Yes</button>
                        <button type="button" class="btn btn-secondary" :disabled="loading.stop_watching" @click="cancelStopWatching">No</button>
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
                    stop_watching: false
                },
                stop_watching: {
                    patient_id: null,
                    index: null
                },
                table: null,
                display_depth: 90,
                selected_statuses: [true, true, true, true, true, true],
            }
        },

        computed: {
            patient_statuses() {
                return this.$store.state.patient_statuses;
            }
        },

        mounted() {
            this.loading.page = true;
            this.$store.dispatch('getPatientStatuses').then(() => {
                this.init();
            });
        },

        methods: {

            setLoadingPage(val) {
                this.loading.page = val;
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

            init() {
                this.loading.page = true;
                let data = {
                    display_depth: this.display_depth,
                    statuses: this.getStatusesDataset()
                };
                console.log(data);
                this.$store.dispatch('getPatientsWithoutAppointmentsStatistic', data).then(response => {
                    this.patients = response.data;
                    this.loading.page = false;
                    const self = this;
                    window.setTimeout(() => {
                        this.table = $('#statistic-table').DataTable({
                            'paging': false,
                            'lengthChange': false,
                            'searching': true,
                            'ordering': true,
                            'info': false,
                            'autoWidth': false,
                            order: [[1, 'asc']],
                            columns: [
                                {searchable: false, sortable: false},
                                null,   //patient
                                {searchable: false},   //therapist
                                {searchable: false},   //Insurance
                                {searchable: false},   //status
                                {searchable: false},   //date
//                            {sortable: false, searchable: false}//stop watching btn
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
            getFormattedDate(str) {
                let date = new Date(str);
                let month = date.getMonth() + 1;
                if(month < 10) {
                    month = "0" + month;
                }
                let day = date.getDate();
                if(day < 10) {
                    day = "0" + day;
                }
                return month + "/" + day + "/" + date.getFullYear()
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
            },

            stopWatching() {
                this.table.row($('#statistic-table #stop-watching-'+this.stop_watching.index).parents('tr')).remove().draw();
                this.loading.stop_watching = true;
                this.$store.dispatch('stopWatchingForPatientsWoAppointments', this.stop_watching.patient_id).then(() => {
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

    ul.provider-list {
        list-style:none;
        margin-bottom:0;
        padding-left: 0;
    }
</style>