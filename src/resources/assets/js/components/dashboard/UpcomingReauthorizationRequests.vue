<template>
    <div class="container">
        <div class="row">
            <div v-if="loading.page" class="text-center page-loader-wrapper">
                <pageloader add-classes="page-loader"></pageloader>
            </div>
            <div v-else class="vue-wrapper">
                <doctors-availability-alert />

                <h2 class="text-center">Reauthorization Requests</h2>

                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <doctors-dropdown v-if="is_admin" label="Therapist Name" />
                                <patient-status-checkboxes with-colors="true" :invisible-statuses-for-doctors="invisible_statuses_for_doctors" v-show="selected_provider_id || !is_admin" />
                                <div class="table-responsive" v-show="selected_provider_id || !is_admin">
                                    <table id="statistic-table" class="table table-condenced table-striped table-bordered">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Patient</th>
                                            <th>Insurance</th>
                                            <th>Therapist Name</th>
                                            <th>Status</th>
                                            <th>Authorization Number</th>
                                            <th>Eff. Start Date</th>
                                            <th>Eff. Stop Date</th>
                                            <th>Visits Auth</th>
                                            <th>Visits Auth Left</th>
                                            <th>Document Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr
                                            v-for="(patient, index) in patients"
                                            :class="{'almost-overdue': !isOverdue(patient) && isAlmostOverdue(patient), 'overdue': isOverdue(patient)}"
                                        >
                                            <td class="text-center">{{ index + 1 }}</td>
                                            <td>
                                                <a :href="`${'/chart/' + patient.id}`">{{ patient.patient_name }}</a>
                                            </td>
                                            <td>
                                                {{ patient.primary_insurance }}
                                            </td>
                                            <td>
                                                <ul class="provider-list-col">
                                                    <li v-for="provider in patient.providers">
                                                        {{ provider.provider_name }}
                                                    </li>
                                                </ul>
                                            </td>
                                            <td :style="{color: '#'+patient.status_color}">
                                                {{ patient.status }}
                                            </td>
                                            <td class="text-center">
                                                {{ patient.insurance_authorization_number || 'N/A' }}
                                            </td>
                                            <td class="text-center">
                                                {{ formatedDate(patient.insurance_eff_start_date) }}
                                            </td>
                                            <td class="text-center">
                                                {{ formatedDate(patient.insurance_eff_stop_date) }}
                                            </td>
                                            <td class="text-center">
                                                {{ patient.insurance_visits_auth }}
                                            </td>
                                            <td class="text-center">
                                                {{ patient.insurance_visits_auth_left }}
                                            </td>
                                            <td class="text-center">
                                                {{ getRequestDoumentStatus(patient) }}
                                            </td>
                                        </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th></th>
                                                <th>Patient</th>
                                                <th>Insurance</th>
                                                <th>Therapist Name</th>
                                                <th>Status</th>
                                                <th>Authorization Number</th>
                                                <th>Eff. Start Date</th>
                                                <th>Eff. Stop Date</th>
                                                <th>Visits Auth</th>
                                                <th>Visits Auth Left</th>
                                                <th>Document Status</th>
                                            </tr>
                                        </tfoot>
                                    </table>
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
    export default {
        mounted() {
            this.$store.dispatch('getUserRoles');

            if (this.isOnlyOverdue) {
                this.selected_statuses = this.initialStatusesForRedirectAction;
            }

            this.init(-1, this.selected_statuses);
        },

        data() {
            return {
                patients: {},
                loading: {
                    page: false,
                },
                selected_provider_id: 0,
                selected_statuses: [1],
                invisible_statuses_for_doctors: ['Unassign'],
                table: null,
                initialStatusesForRedirectAction: [1, false, false, 4, false, 5, 8]
            }
        },

        computed: {
            patient_statuses() {
                return this.$store.state.patient_statuses;
            },
            is_admin() {
                return this.$store.state.isUserAdmin;
            },
            isOnlyOverdue() {
                return this.$route.query.is_only_overdue;
            }
        },

        methods: {
            getVisitsAuth(patient) {
                if(!patient.visits_auth) {
                    return 0;
                }
                return patient.visits_auth;
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
                this.$store.dispatch('getUpcomingReauthorizationRequests', data).then(response => {
                    this.patients = response.data;

                    if (this.isOnlyOverdue) {
                        this.patients = response.data.filter(patient => this.isAlmostOverdue(patient) || this.isOverdue(patient))
                    }

                    this.loading.page = false;
                    if(this.table) {
                        this.table.destroy();
                    }
                    window.setTimeout(function () {
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
                                null,
                                {searchable: false},
                                {searchable: false},
                                {searchable: false},
                                {searchable: false},
                                {searchable: false},
                                {searchable: false},
                            ]
                        });
                    }, 500);
                });
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

            setSelectedProviderId(id) {
                this.selected_provider_id = id;
                this.init(this.selected_provider_id, this.getStatusesDataset());
            },

            getSelectedProviderId() {
                return this.selected_provider_id;
            },

            /**
             * Returns full patient name
             * @param patient
             * @returns {string}
             */
            patientFullName(patient) {
                return patient.first_name + ' ' + patient.last_name;
            },

            formatedDate(date) {
                return moment(date).format('MM/DD/YY');
            },

            isAlmostOverdue(patient) {
                const currentDate = moment(moment().format('YYYY-MM-DD'));
                if (
                    patient.insurance_eff_stop_date
                        && moment(patient.insurance_eff_stop_date).diff(currentDate, 'days') <= patient.insurance_plan.reauthorization_notification_days_count
                        && moment(patient.insurance_eff_stop_date).diff(currentDate, 'days') > 0
                ) {
                    return true;
                }
                if (
                    patient.insurance_visits_auth_left <= patient.insurance_plan.reauthorization_notification_visits_count 
                        && patient.insurance_visits_auth_left > 0
                ) {
                    return true;
                }

                return false;
            },

            isOverdue(patient) {
                if (!patient.insurance_authorization_number) {
                    return true;
                }
                const currentDate = moment(moment().format('YYYY-MM-DD'));
                if (patient.insurance_eff_stop_date && moment(patient.insurance_eff_stop_date).diff(currentDate, 'days') <= 0) {
                    return true;
                }
                if (patient.insurance_visits_auth_left <= 0) {
                    return true;
                }

                return false;
            },

            getRequestDoumentStatus(patient) {
                let title = "Not created";

                if (patient.insurance_plan && !patient.insurance_plan.requires_reauthorization_document) {
                    title = "Not required";
                } else if (patient.reauhtorization_request_document) {
                    title = "Created";
                }

                return title;
            },
        },
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

    .patients-container {
        padding-left: 20px;
    }

    #statistic-table {
        margin-bottom: 0 !important;
    }

    .overdue, .overdue a {
        background-color: #f8d7da !important;
    }
    .almost-overdue, .almost-overdue a {
        background-color: #fff3cd !important;
    }

    ul.provider-list-col {
        list-style: none;
        padding-left: 0;
    }
</style>