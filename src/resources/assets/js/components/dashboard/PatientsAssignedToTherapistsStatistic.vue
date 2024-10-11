<template>
    <div class="container">
        <div class="row">
            <div class="text-center page-loader-wrapper" v-show="loading.page || loading.diagrams || !is_initialized">
                <pageloader add-classes="page-loader"></pageloader>
            </div>
            <div class="vue-wrapper" v-show="!loading.diagrams && !loading.page && is_initialized">
                <doctors-availability-alert />

                <h2 class="text-center">Assigned patients</h2>

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
                                                <doctors-dropdown v-if="is_admin" label="Therapist Name" />
                                                <patient-status-checkboxes
                                                    v-show="(selected_provider_id || !is_admin) && is_initialized"
                                                    ref="patientStatuses"
                                                    :invisible-statuses="invisible_statuses"
                                                    with-colors="true"
                                                />
                                                <div v-for="item in statistic">
                                                    <h3 class="text-center">{{item.provider_name}}</h3>
                                                    <ul class="status-statistic">
                                                        <li v-for="status in item.statuses">
                                                            {{status.status}}: {{status.patient_count}}
                                                        </li>
                                                    </ul>

                                                    <div class="table-responsive">
                                                        <table class="statistic-table table table-condenced table-striped table-bordered" v-if="item.provider_name !== 'All Patients'">
                                                            <thead>
                                                            <tr>
                                                                <th class="statistic-row-no-th"></th>
                                                                <th class="width50percent">Patient</th>
                                                                <th class="width50percent">Insurance</th>
                                                                <th class="width50percent">Status</th>
                                                                <th class="width50percent">Number of visits created</th>
                                                                <th class="width50percent">Date of Assignment</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr v-for="(patient, index) in item.patients">
                                                                <td class="text-center">{{index+1}}</td>
                                                                <td>
                                                                    <a :href="`${'/chart/' + patient.id}`">{{ patient.patient_name}}</a>
                                                                </td>
                                                                <td>
                                                                    {{ patient.primary_insurance }}
                                                                    <span v-if="patient.secondary_insurance">
                                                                        ({{patient.secondary_insurance}})
                                                                    </span>
                                                                </td>
                                                                <td :style="{color: '#'+patient.hex_color}">
                                                                    {{ patient.status }}
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ patient.number_of_visit_created }}
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ patient.date_of_assignment }}
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                            <tfoot>
                                                            <tr>
                                                                <th></th>
                                                                <th>Patient</th>
                                                                <th>Insurance</th>
                                                                <th>Status</th>
                                                                <th>Number of visits created</th>
                                                                <th>Date of Assignment</th>
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
                    </div>

                    <div id="visual-diagrams-tab" class="tab-pane fade in" v-if="is_admin">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <patient-diagram-status-checkboxes with-colors="true"/>
                                        <div id="chart-container"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                statistic: {},
                loading: {
                    page: false,
                    diagrams: false,
                },
                is_initialized: false,
                selected_provider_id: 0,
                selected_statuses: [1],
                selected_diagram_statuses: [1],
                diagram_statistic: {},
                is_drawed: false,
                invisible_statuses: ['Unassign'],
                table: null,
            }
        },

        watch: {
        },

        computed: {
            patient_statuses() {
                return this.$store.state.patient_statuses;
            },

            query_patient_statuses() {
                return this.$route.query['patient_statuses[]'];
            },

            is_admin() {
                return this.$store.state.isUserAdmin;
            }
        },

        beforeMount() {
            if (this.query_patient_statuses) {
                const query_patient_statuses = Array.isArray(this.query_patient_statuses)
                    ? this.query_patient_statuses.map(item => parseInt(item))
                    : [parseInt(this.query_patient_statuses)];

                this.selected_statuses = query_patient_statuses;
            }
        },

        mounted() {
            this.getPatientStatuses();
            this.$store.dispatch('getUserRoles').then(() => {
                this.init(0, this.selected_statuses, true);
                this.is_initialized = true;

                if (this.$refs && this.$refs.patientStatuses) {
                    this.$refs.patientStatuses.setSelectedStatuses(this.selected_statuses, true);
                }
            });
        },

        methods: {
            getPatientStatuses() {
                if (this.patient_statuses.length) {
                    return;
                }

                this.$store.dispatch('getPatientStatuses');
            },

            initDiagrams(statuses) {
                this.loading.diagrams = true;

                this.is_drawed = false;
                let data = {
                    statuses: []
                };
                if (statuses) {
                    data.statuses = statuses;
                }

                this.$store.dispatch('getPatientAssignedToTherapistsStatisticForDiagrams', data).then(response => {
                    this.diagram_statistic = response.data;
                    window.setTimeout(() => {
                        this.loading.diagrams = false;
                        let self = this;
                        let htmlCanvas = '<canvas id="doctors-patient-count-el"></canvas>';
                        $('#chart-container').html(htmlCanvas);
                        if (!this.is_drawed) {
                            let canvasInterval = window.setInterval(() => {
                                let doctors_patient_count_el = document.getElementById('doctors-patient-count-el');
                                if(doctors_patient_count_el) {
                                    doctors_patient_count_el = doctors_patient_count_el.getContext('2d');
                                    new Chart(doctors_patient_count_el, {
                                        type: 'horizontalBar',
                                        data: {
                                            labels: self.diagram_statistic.providers_patient_count.providers,
                                            datasets: [{
                                                data: self.diagram_statistic.providers_patient_count.patient_count,
                                                backgroundColor: self.diagram_statistic.providers_patient_count.colors,
                                            }]
                                        },
                                        options: {
                                            legend: {
                                                display: false,
                                            },
                                            scales: {
                                                xAxes: [{
                                                    ticks: {
                                                        beginAtZero: true
                                                    }
                                                }]
                                            }
                                        }
                                    });
                                    this.is_drawed = true;

                                    clearInterval(canvasInterval);
                                } else {
                                    $('#chart-container').html(htmlCanvas);
                                }
                            }, 500);
                        }
                    }, 500);

                });
            },

            setLoadingPage(val) {
                this.loading.page = val;
            },

            getStatusesDataset(sel_statuses) {
                if (sel_statuses.length && typeof(sel_statuses[0]) !== 'boolean' && typeof(sel_statuses[0]) !== 'undefined') {
                    return sel_statuses;
                }

                let statuses = this.patient_statuses;
                let dataset = [];

                for (let i = 0; i < statuses.length; i++) {
                    if (sel_statuses[i]) {
                        dataset.push(statuses[i]['id']);
                    }
                }

                return dataset;
            },

            setSelectedStatuses(statuses, flag) {
                this.selected_statuses = statuses;
                if (flag) {
                    const dataset = this.getStatusesDataset(this.selected_statuses);
                    this.init(this.selected_provider_id, dataset);
                }
            },

            getSelectedStatuses() {
                return this.selected_statuses;
            },

            setSelectedDiagramStatuses(statuses, flag) {
                this.selected_diagram_statuses = statuses;
                if (flag) {
                    this.initDiagrams(this.getStatusesDataset(this.selected_diagram_statuses));
                }
            },

            getSelectedDiagramStatuses() {
                return this.selected_diagram_statuses;
            },

            setSelectedProviderId(id) {
                this.selected_provider_id = id;
                this.init(this.selected_provider_id, this.getStatusesDataset(this.selected_statuses));
            },

            getSelectedProviderId() {
                return this.selected_provider_id;
            },

            init(provider_id, statuses, is_first_init) {
                this.is_drawed = false;
                if(is_first_init) {
                    this.initDiagrams(this.selected_diagram_statuses);
                } else {
                    this.initDiagrams(this.getStatusesDataset(this.selected_diagram_statuses));
                }
                if(this.selected_provider_id === 0 && this.is_admin) {
                    return false;
                }

                this.loading.page = true;
                const data = {
                    pid: provider_id,
                    statuses: statuses || [],
                };

                this.$store.dispatch('getPatientsAssignedToTherapistsStatistic', data).then(response => {
                    this.statistic = response.data;
                    this.loading.page = false;
                    if(this.table) {
                        this.table.destroy();
                    }
                    const self = this;
                    window.setTimeout(() => {
                        this.table = $('.statistic-table').DataTable({
                            'paging': false,
                            'lengthChange': false,
                            'searching': true,
                            'ordering': true,
                            'info': false,
                            'autoWidth': false,
                            order: [[1, 'asc']],
                            columns: [
                                {searchable: false, sortable: false},
                                null,
                                {searchable: false},
                                {searchable: false},
                                {searchable: false},
                                {
                                  searchable: false,
                                  type: 'date',
                                }
                            ],
                            fnDrawCallback: function () {
                                self.updateTableRowNumbers(this);
                            }
                        });
                    }, 500);
                });
            },
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

    .status-statistic {
        list-style: none;
        padding-left: 0;
    }

    .provider-select {
        max-width: 200px !important;
    }

    .width50percent {
        width: 20% !important;
        white-space: nowrap;
    }

</style>