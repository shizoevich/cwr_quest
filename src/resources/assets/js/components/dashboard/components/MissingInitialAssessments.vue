<template>
    <div>
        <div class="panel dashboard-panel" :class="getPanelClass()">
            <div class="panel-heading">
                <div class="panel-heading-container">
                    <div class="head-label">
                        <div class="head-label-container">
                            <div>Missing Initial Assessments</div>
                        </div>
                    </div>
                    <div class="head-count">
                        <span v-if="missing_initial_assessments">
                            {{ missing_initial_assessments_count }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding-top: 0">
                <table class="table table-layout-fixed" v-if="missing_initial_assessments && missing_initial_assessments.length">
                    <thead class="sticky-thead">
                        <tr>
                            <th>Patient</th>
                            <th>Insurance</th>
                            <th v-if="is_admin">Provider</th>
                            <th width="80px">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in missing_initial_assessments">
                            <td class="panel-patient-name">
                                <router-link :style="{color: '#' + item.hex_color}" :to="`${'/chart/' + item.id}`" :title="item.patient_name">
                                    {{ item.first_name }} {{ item.last_name }}
                                </router-link>
                            </td>
                            <td>{{ item.primary_insurance || '-' }}</td>
                            <td v-if="is_admin">{{ item.provider_name || '-' }}</td>
                            <td class="count-col">{{ getMissingNotesCount(item) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="panel-loader-container dashboard-table-empty" v-else-if="loading">
                    <pageloader add-classes="panel-loader" />
                </div>
                <div class="dashboard-table-empty" v-else>
                    All initial assessments are up to date
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                missing_initial_assessments: null,
                loading: false,
            };
        },

        mounted() {
            this.loading = true;
            this.$store.dispatch('getDashboardProviderMissingInitialAssessments').then(response => {
                if (response.status === 200) {
                    this.missing_initial_assessments = response.data;
                    this.loading = false;
                }
            });
        },

        computed: {
            missing_initial_assessments_count() {
                if (this.missing_initial_assessments && this.missing_initial_assessments.length) {
                    let count = 0;
                    for (let i in this.missing_initial_assessments) {
                        count += parseInt(this.missing_initial_assessments[i].missing_initial_assessments_count);
                    }

                    return count;
                }

                return 0;
            },
            is_admin() {
                return this.$store.state.isUserAdmin;
            }
        },

        methods: {
            getPanelClass() {
                return this.missing_initial_assessments && this.missing_initial_assessments.length ? 'panel-red' : 'panel-green';
            },

            getMissingNotesCount(patient) {
                return patient.missing_initial_assessments_count;
            },
        },
    }
</script>