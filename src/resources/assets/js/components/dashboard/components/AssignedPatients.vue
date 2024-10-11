<template>
    <div>
        <div class="panel dashboard-panel panel-green">
            <div class="panel-heading">
                <div class="panel-heading-container">
                    <div class="head-label">
                        <div class="head-label-container">
                            <div>Assigned Patients</div>
                            <a
                                href="/statistic/patients-assigned-to-therapists"
                                target="_blank"
                                class="head-view-link"
                            >
                                Details
                            </a>
                        </div>
                    </div>
                    <div class="head-count">
                        <span v-if="assigned_patients.length">
                            {{assigned_patients.length}}
                        </span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding-top: 0">
                <table class="table" v-if="assigned_patients.length">
                    <thead class="sticky-thead">
                        <tr>
                            <th>Patient</th>
                            <th>Insurance</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in assigned_patients">
                        <td class="panel-patient-name">
                            <router-link :style="{color: '#' + item.status.hex_color}" :to="`${'/chart/' + item.id}`" :title="item.full_name">
                                {{ item.full_name }}
                            </router-link>
                        </td>
                        <td>{{ item.insurance ? item.insurance.insurance : '-' }}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="panel-loader-container dashboard-table-empty" v-else-if="loading">
                    <pageloader add-classes="panel-loader" />
                </div>
                <div class="dashboard-table-empty" v-else>
                    No assigned patients
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                assigned_patients: [],
                loading: false,
            };
        },

        mounted() {
            this.loading = true;
            this.$store.dispatch('getDashboardProviderAssignedPatients').then(response => {
                if(response.status === 200) {
                    this.assigned_patients = response.data;
                    this.loading = false;
                }
            });
        },
    }
</script>