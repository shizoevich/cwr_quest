<template>
    <div>
        <div class="panel dashboard-panel" :class="getPanelClass()">
            <div class="panel-heading">
                <div class="panel-heading-container">
                    <div class="head-label">
                        <div class="head-label-container">
                            <div>Inactive Patients</div>
                            <a
                                :href="btn_view_href"
                                target="_blank"
                                class="head-view-link"
                            >
                                Details
                            </a>
                        </div>
                    </div>
                    <div class="head-count">
                        <span v-if="inactive_patients.length">
                            {{ inactive_patients.length }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding-top: 0">
                <table class="table table-layout-fixed" v-if="inactive_patients.length">
                    <thead class="sticky-thead">
                        <tr>
                            <th>Patient</th>
                            <th>Insurance</th>
                            <th v-if="is_admin">Provider</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in inactive_patients">
                        <td class="panel-patient-name">
                            <router-link :style="{color: '#' + item.status.hex_color}" :to="`${'/chart/' + item.id}`" :title="item.full_name">
                                {{ item.full_name }}
                            </router-link>
                        </td>
                        <td>{{ item.insurance ? item.insurance.insurance : '-' }}</td>
                        <td v-if="is_admin">{{ item.provider_name || '-' }}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="panel-loader-container dashboard-table-empty" v-else-if="loading">
                    <pageloader add-classes="panel-loader" />
                </div>
                <div class="dashboard-table-empty" v-else>
                    No inactive patients
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                inactive_patients: [],
                loading: false,
            };
        },
        computed: {
            btn_view_href() {
                let url = '/statistic/patients-assigned-to-therapists';
                const patientStatuses = this.$store.state.patient_statuses;
                const inactiveStatus =  patientStatuses ? patientStatuses.find(status => status.status === 'Inactive') : null;

                if (inactiveStatus) {
                    url += `?patient_statuses[]=${inactiveStatus.id}`;
                }

                return url;
            },
            is_admin() {
                return this.$store.state.isUserAdmin;
            }
        },

        mounted() {
            this.loading = true;
            this.$store.dispatch('getDashboardProviderInactivePatients').then(response => {
                if (response.status === 200) {
                    this.inactive_patients = response.data;
                    this.loading = false;
                }
            });
        },

        methods: {
            getPanelClass() {
                if (this.inactive_patients && this.inactive_patients.length) {
                    return 'panel-yellow';
                }

                return 'panel-green';
            },
        }
    }
</script>