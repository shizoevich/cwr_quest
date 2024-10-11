<template>
    <div class="container">
        <div class="row">
            <div class="text-center page-loader-wrapper" v-if="loading.page">
                <pageloader add-classes="page-loader"></pageloader>
            </div>
            <div class="vue-wrapper" v-else>
                <doctors-availability-alert />

                <h2 class="text-center">Missing Progress Notes</h2>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <doctors-dropdown v-if="is_admin" label="Therapist Name"/>
                                <patient-status-checkboxes with-colors="true" :invisible-statuses="invisible_statuses"
                                       v-show="selected_provider_id || !is_admin" />
                                <div class="table-responsive" v-show="selected_provider_id || !is_admin">
                                    <table id="statistic-table" class="table table-condenced table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Therapist Name</th>
                                                <th># of Patients with Missing notes / Total active patients</th>
                                                <th>Names of Patients with Missing Notes</th>
                                                <th># of Notes Missing per Patient</th>
                                                <th>Visits Created without Notes on File</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="doctor in doctors" v-if="doctor.patients.length">
                                                <td class="provider-name-col">{{ doctor.provider_name }}</td>
                                                <td class="text-center">
                                                    {{doctor.patients.length}} / {{doctor.total_no_of_active_patients}}
                                                </td>
                                                <td>
                                                    <div class="patients-container">
                                                        <ul>
                                                            <li v-for="patient in doctor.patients" style="white-space: nowrap">
                                                                <a :href="'/chart/'+patient.id">{{patientFullName(patient)}}</a>
                                                                <span :style="{color: '#' + patient.hex_color}">({{patient.status}})</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <ul class="date-list">
                                                        <li v-for="patient in doctor.patients">{{ patient.missing_note_count }}</li>
                                                    </ul>
                                                </td>
                                                <td class="text-center">
                                                    <span v-for="patient in doctor.patients">
                                                        <ul class="date-list">
                                                            <li v-if="patient.missing_note_dates && patient.missing_note_dates.length == 1">
                                                                {{patient.missing_note_dates[0]}}
                                                            </li>
                                                            <li v-else-if="!patient.missing_note_dates || patient.missing_note_dates.length <= 0">
                                                                -
                                                            </li>
                                                            <li v-else>
                                                                <a style="cursor:pointer;" @click.prevent="showDatesList(patient.missing_note_dates)">
                                                                    View All
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th>Therapist Name</th>
                                                <th># of Patients with Missing notes / Total active patients</th>
                                                <th>Names of Patients with Missing Notes</th>
                                                <th># of Notes Missing per Patient</th>
                                                <th>Visits Created without Notes on File</th>
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

        <!--Modals-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="patient-dates-list-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Visits Created without Notes on File</h5>
                    </div>
                    <div class="modal-body">
                        <ul v-if="dates_to_display.length">
                            <li v-for="date in dates_to_display">{{date}}</li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" @click="closeDatesList">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {

        mounted() {
            this.$store.dispatch('isAdmin').then(() => {
                this.init(this.selected_provider_id, this.selected_statuses);
            });
        },

        data() {
            return {
                doctors: {},
                search_query: '',
                loading: {
                    page: false,
                },
                selected_statuses: [1],
                dates_to_display: [],
                selected_provider_id: 0,
                invisible_statuses: ['Unassign'],
                table: null,
            }
        },

        computed: {
            patient_statuses() {
                return this.$store.state.patient_statuses;
            },
            is_admin() {
                return this.$store.state.isUserAdmin
            }
        },

        methods: {

            setLoadingPage(val) {
                this.loading.page = val;
            },

            setSelectedProviderId(id) {
                this.selected_provider_id = id;
                this.init(this.selected_provider_id, this.getStatusesDataset());
            },

            getSelectedProviderId() {
                return this.selected_provider_id;
            },

            showDatesList(dates) {
                this.dates_to_display = dates;
                $('#patient-dates-list-modal').modal('show');
            },

            closeDatesList() {
                this.dates_to_display = [];
            },

            init(providerId, statuses) {
                if(this.selected_provider_id === 0 && this.is_admin) {
                    return false;
                }
                this.loading.page = true;
                let data = {
                    providerId: providerId,
                    statuses: []
                };
                if(statuses) {
                    data.statuses = statuses;
                }
                this.$store.dispatch('getDoctorsStatistic', data).then(response => {
                    this.doctors = response.data;
                    this.loading.page = false;
                    if(this.table) {
                        this.table.destroy();
                    }
                    window.setTimeout(function() {
                        this.table = $('#statistic-table').DataTable({
                            'paging'      : false,
                            'lengthChange': false,
                            'searching'   : false,
                            'ordering'    : true,
                            'info'        : false,
                            'autoWidth'   : false,
                            columns: [
                                null,   //first column (dactors)
                                {
//                                sortable: false,
                                    searchable: false,
                                    width: '250px'
                                },  //second column
                                {
                                    sortable: false,
                                    searchable: false
                                },   //third column (patients)
                                {
                                    sortable: false,
                                    searchable: false
                                },
                                {
                                    sortable: false,
                                    searchable: false
                                },
                            ]
                        });
                    },500);
                });
            },

            /**
             * Returns full patient name
             * @param patient
             * @returns {string}
             */
            patientFullName(patient) {
                return patient.first_name + ' ' + patient.last_name;
            },

            getPatientAppointmentsNotesDiffCount(patient) {
                return patient.missing_note_count;
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
                    this.init(this.selected_provider_id, this.getStatusesDataset());
                }
            },

            getSelectedStatuses() {
                return this.selected_statuses;
            },

        },

        watch: {

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

    table.wo-border {
        border-top: none;
        border-left: none;
        margin-bottom: 0;
    }

    .wo-border td {
        padding-left: 0;
        padding-right: 0;
    }

    .search-box {
        margin-bottom: 20px;
    }

    ul.date-list,
    .patients-container ul {
        list-style: none;
        margin-bottom: 0;
    }
    ul.date-list {
        padding-left: 0;
    }


    .patients-container ul {
        padding-left: 20px;
    }

    #statistic-table {
        margin-bottom: 0 !important;
    }
</style>