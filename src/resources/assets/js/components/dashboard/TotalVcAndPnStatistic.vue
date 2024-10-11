<template>
    <div class="container">
        <div class="row">
            <div class="text-center page-loader-wrapper" v-if="loading.page">
                <pageloader add-classes="page-loader"></pageloader>
            </div>
            <div class="vue-wrapper" v-else>
                <doctors-availability-alert />

                <h2 class="text-center">Total Visits Created & Progress Notes Filed Electronically</h2>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group month-filter">
                                    <label>From</label>
                                    <select v-model="date_from" class="form-control">
                                        <!--<option value="-1">All</option>-->
                                        <option v-for="month in months" :value="month.name" :disabled="month.val > date_to">{{month.name}}</option>
                                    </select>
                                </div>
                                <div class="form-group month-filter">
                                    <label>To</label>
                                    <select v-model="date_to" class="form-control">
                                        <!--<option value="-1">All</option>-->
                                        <option v-for="month in months" :value="month.name" :disabled="month.val < date_from">{{month.name}}</option>
                                    </select>
                                </div>

                                <doctors-dropdown label="Therapist" />
                                <div class="table-responsive" v-for="doctor in doctors" v-if="doctor.patients && doctor.patients.length">
                                    <table class="table table-condenced table-striped table-bordered statistic-table">
                                        <thead>
                                            <tr>
                                                <th># (line count)</th>
                                                <th>Therapist Name</th>
                                                <th>Patient Name</th>
                                                <th># of visits created</th>
                                                <th># of notes added</th>
                                                <th># of notes missing</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(patient, index) in doctor.patients" v-if="doctor.patients.length">
                                                <td class="text-center">{{index+1}}</td>
                                                <td class="text-center">
                                                    <span v-if="index === 0">
                                                        {{doctor.provider_name}}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a :href="'/chart/'+patient.id">{{patient.full_name}}</a>
                                                </td>
                                                <td class="text-center">{{patient.appointments_count}}</td>
                                                <td class="text-center">{{patient.note_count}}</td>
                                                <td class="text-center">{{getPatientAppointmentsNotesDiffCount(patient)}}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-right">TOTAL</th>
                                                <th># of patients seen - {{doctor.patients.length}}</th>
                                                <th># of visits created - {{getTotalVc(doctor)}}</th>
                                                <th># of notes added - {{getTotalPn(doctor)}}</th>
                                                <th># of notes missing - {{getTotalMissingPn(doctor)}}</th>
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
</template>

<script>
    export default {

        mounted() {
            this.init();
        },

        data() {
            return {
                doctors: {},
                search_query: '',
                loading: {
                    page: false,
                },
                dates_to_display: [],
                selected_provider_id: -1,
                date_from: 'September',
                date_to: 'September',
                months: [
                    { val: 1, name: 'January'},
                    { val: 2, name: 'February'},
                    { val: 3, name: 'March'},
                    { val: 4, name: 'April'},
                    { val: 5, name: 'May'},
                    { val: 6, name: 'June'},
                    { val: 7, name: 'July'},
                    { val: 8, name: 'August'},
                    { val: 9, name: 'September'},
                    { val: 10, name: 'October'},
                    { val: 11, name: 'November'},
                    { val: 12, name: 'December'},
                ],
                disabled_to_date: [],
            }
        },

        computed: {
        },

        methods: {

            getTotalVc(doctor) {
                let total = 0;
                for(let i in doctor.patients) {
                    total += parseInt(doctor.patients[i].appointments_count);
                }
                return total;
            },

            getTotalPn(doctor) {
                let total = 0;
                for(let i in doctor.patients) {
                    total += parseInt(doctor.patients[i].note_count);
                }
                return total;
            },

            getTotalMissingPn(doctor) {
                let total = 0;
                for(let i in doctor.patients) {
                    total += parseInt(this.getPatientAppointmentsNotesDiffCount(doctor.patients[i]));
                }
                return total;
            },

            setLoadingPage(val) {
                this.loading.page = val;
            },

            setSelectedProviderId(id) {
                this.selected_provider_id = id;
                this.init();
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

            init() {
                this.loading.page = true;
                let data = {
                    providerId: this.selected_provider_id,
                    monthFrom: this.date_from,
                    monthTo: this.date_to,
                };
                this.$store.dispatch('getTotalVcAndPnStatistic', data).then(response => {
                    this.doctors = response.data;
                    this.loading.page = false;
                    window.setTimeout(function() {
                        $('.statistic-table').DataTable({
                            'paging'      : false,
                            'lengthChange': false,
                            'searching'   : true,
                            'ordering'    : true,
                            'info'        : false,
                            'autoWidth'   : false,
                            'order': [[2, 'asc']],
                            columns: [
                                { sortable: false, searchable: false},
                                { sortable: false, searchable: false},
                                null,
                                { searchable: false},
                                { searchable: false},
                                { searchable: false},
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
                let diff = patient.appointments_count - patient.note_count;
                if(diff < 0) {
                    return 0;
                }
                return diff;
            },

        },

        watch: {
            date_from() {
                this.init();
            },
            date_to() {
                this.init();
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

    .month-filter {
        display: inline-block;
        margin-right: 20px;
    }

    .month-filter .form-control {
        width: 200px;
    }
</style>