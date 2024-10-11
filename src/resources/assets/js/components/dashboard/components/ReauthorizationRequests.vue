<template>
    <div>
        <div class="panel dashboard-panel" :class="getPanelClass()">
            <div class="panel-heading">
                <div class="panel-heading-container">
                    <div class="head-label">
                        <div class="head-label-container">
                            <div>Reauthorization Requests</div>
                            <a
                                href="/statistic/upcoming-reauthorization-requests"
                                target="_blank"
                                class="head-view-link"
                            >
                                Details
                            </a>
                        </div>
                    </div>
                    <div class="head-count">
                        <span v-if="reauthorization_requests">
                            {{reauthorization_requests.length}}
                        </span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding-top: 0">
                <table class="table table-layout-fixed" v-if="reauthorization_requests && reauthorization_requests.length">
                    <thead class="sticky-thead">
                        <tr>
                            <th>Patient</th>
                            <th>Insurance</th>
                            <th v-if="is_admin">Provider</th>
                            <th>Eff. Stop Date</th>
                            <th>No. of Visits Left</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in reauthorization_requests">
                        <td class="panel-patient-name">
                            <router-link :style="{color: '#' + item.status_color}" :to="`${'/chart/' + item.id}`" :title="item.patient_name">
                                {{item.patient_name}}
                            </router-link>
                        </td>
                        <td>{{ item.primary_insurance || '-' }}</td>
                        <td v-if="is_admin">{{ item.provider_name || '-' }}</td>

                        <template v-if="item.insurance_authorization_number">
                            <td :class="{'cell-value': true, 'overdue': isOverdueByDate(item), 'almost-overdue': isAlmostOverdueByDate(item)}" >
                                <span>{{ getFormattedDate(item.insurance_eff_stop_date) }}</span>
                            </td>
                            <td :class="{'cell-value': true, 'overdue': isOverdueByVisitsLeft(item), 'almost-overdue': isAlmostOverdueByVisitsLeft(item)}">
                                <span>{{ getVisitsLeftCount(item.insurance_visits_auth_left) }}</span>
                            </td>
                        </template>
                        <td v-else colspan="2" class="overdue">
                            <span>Authorization number is not set</span>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="panel-loader-container dashboard-table-empty" v-else-if="loading">
                    <pageloader add-classes="panel-loader" />
                </div>
                <div class="dashboard-table-empty" v-else>
                    No reauthorization requests alerts
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                reauthorization_requests: null,
                loading: false,
            };
        },

        mounted() {
            this.loading = true;
            this.$store.dispatch('getDashboardReauthorizationRequests').then(response => {
                if(response.status === 200) {
                    this.reauthorization_requests = response.data;
                    this.loading = false;
                }
            });
        },

        computed: {
            is_admin() {
                return this.$store.state.isUserAdmin;
            }
        },

        methods: {
            getPanelClass() {
                if(this.reauthorization_requests && this.reauthorization_requests.length) {
                    return 'panel-red';
                }

                return 'panel-green';
            },

            getFormattedDate(date) {
                if(date) {
                    return this.$moment(date).format('MM/DD/YYYY');
                }

                return 'N/A';
            },

            getVisitsLeftCount(count) {
                if (count !== null) {
                    return count;
                }

                return 'N/A';
            },

            isAlmostOverdueByDate(request) {
                const currentDate = moment(moment().format('YYYY-MM-DD'));
                if (
                    request.insurance_eff_stop_date 
                        && moment(request.insurance_eff_stop_date).diff(currentDate, 'days') <= request.insurance_plan.reauthorization_notification_days_count 
                        && moment(request.insurance_eff_stop_date).diff(currentDate, 'days') > 0
                ) {
                    return true;
                }

                return false;
            },

            isAlmostOverdueByVisitsLeft(request) {
                if (
                    request.insurance_visits_auth_left <= request.insurance_plan.reauthorization_notification_visits_count 
                        && request.insurance_visits_auth_left > 0
                ) {
                    return true;
                }

                return false;
            },

            isOverdueByDate(request) {
                const currentDate = moment(moment().format('YYYY-MM-DD'));
                if (request.insurance_eff_stop_date && moment(request.insurance_eff_stop_date).diff(currentDate, 'days') <= 0) {
                    return true;
                }

                return false;
            },

            isOverdueByVisitsLeft(request) {
                if (request.insurance_visits_auth_left <= 0) {
                    return true;
                }

                return false;
            }
        },
    }
</script>

<style scoped>
    .cell-value {
        width: 25%;
    }
</style>