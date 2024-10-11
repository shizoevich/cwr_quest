<template>
    <div class="row">
        <div class="col-md-12">
            <h4>
                Total number of patients without upcoming appointments: {{statistic.total_number_of_patients_without_upcoming_appointments}}
            </h4>
            <canvas id="doctors-patient-without-appointments-count-el"></canvas>

            <div class="table-responsive">
                <table id="doctor-statistic-table"
                       class="table table-condenced table-striped table-bordered">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Therapist Name</th>
                        <th>Total number of assigned patients</th>
                        <th>Patients without upcoming appoinments</th>
                        <th>Total number of "Active" patients</th>
                        <th>"Active" patients without upcoming appoinments</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(provider, index) in provider_list_with_statistics">
                            <td class="text-center">{{ index + 1 }}</td>
                            <td>{{ provider.provider_name }}</td>
                            <td>{{ provider.total_patient_count }}</td>
                            <td>{{ provider.patient_count_with_no_appointments }}</td>
                            <td>{{ provider.active_patient_count }}</td>
                            <td>{{ provider.active_patient_count_with_no_appointments }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>Therapist Name</th>
                        <th>Total number of assigned patients</th>
                        <th>Patients without upcoming appoinments</th>
                        <th>Total number of "Active" patients</th>
                        <th>"Active" patients without upcoming appoinments</th>
                    </tr>
                    </tfoot>
                </table>
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
                is_drawed: false
            }
        },

        computed: {
            provider_list() {
                return this.$store.state.provider_list;
            },

            provider_list_with_statistics() {
                let providers = this.provider_list;
                if (this.statistic.providers_patient_count && this.statistic.provider_patients_with_no_appointments) {
                    for (let i = 0; i < providers.length; i++) {
                        let index1 = this.statistic.providers_patient_count.providers.indexOf(providers[i].provider_name);
                        providers[i]['total_patient_count'] = this.statistic.providers_patient_count.patient_count[index1];
                        
                        let index2 = this.statistic.provider_patients_with_no_appointments.providers.indexOf(providers[i].provider_name);
                        providers[i]['patient_count_with_no_appointments'] = this.statistic.provider_patients_with_no_appointments.patient_count[index2];
                        providers[i]['active_patient_count'] = this.statistic.provider_patients_with_no_appointments.active_patient_count[index2];
                        providers[i]['active_patient_count_with_no_appointments'] = this.statistic.provider_patients_with_no_appointments.active_patient_count_with_no_appointments[index2];
                    }
                }

                return providers;
            }
        },

        mounted() {
            this.getStatistics();
        },

        methods: {
            getStatistics() {
                let parent = this.$parent;
                parent.setLoadingDiagrams(true);

                this.$store.dispatch('getPatientsStatisticForDiagrams').then(response => {
                    this.statistic = response.data;
                    parent.setLoadingDiagrams(false);

                    window.setTimeout(() => {
                        let self = this;
                        $('a[data-toggle="tab"][href="#visual-diagrams-tab"]').on('shown.bs.tab', function (e) {
                            e.target // newly activated tab
                            
                            if (!this.is_drawed) {
                                let doctors_patient_without_appointments_count_el = document.getElementById('doctors-patient-without-appointments-count-el').getContext('2d');
                                let doctors_patient_without_appointments_count_chart = new Chart(doctors_patient_without_appointments_count_el, {
                                    type: 'pie',
                                    data: {
                                        labels: self.statistic.provider_patients_with_no_appointments.providers,
                                        datasets: [{
                                            data: self.statistic.provider_patients_with_no_appointments.patient_count,
                                            backgroundColor: self.statistic.provider_patients_with_no_appointments.colors,
                                        }]
                                    },
                                    options: {
                                        legend: {
                                            display: true,
                                            position: 'right'
                                        }
                                    }
                                });
                                this.is_drawed = true;

                                $('#doctor-statistic-table').DataTable({
                                    'paging': false,
                                    'lengthChange': false,
                                    'searching': true,
                                    'ordering': true,
                                    'info': false,
                                    'autoWidth': false,
                                    'order': [[1, 'asc']],
                                    columns: [
                                        {searchable: false, sortable: false},
                                        null,
                                        {searchable: false},
                                        {searchable: false},
                                        {searchable: false},
                                    ],
                                    fnDrawCallback: function () {
                                        self.updateTableRowNumbers(this);
                                    }
                                });
                            }
                        });

                    }, 500);
                });
            },
            patientFullName(patient) {
                return patient.first_name + ' ' + patient.last_name;
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

    .stop-watching-loader {
        max-height: 35px;
        max-width: 35px;
        margin-right: 10px;
    }

    .provider-select {
        max-width: 200px !important;
    }

    #doctors-patient-count-el {
        margin-top: 50px;
    }

    #doctors-patient-without-appointments-count-el {
        margin-top: 100px;
        margin-bottom: 100px;
    }
</style>