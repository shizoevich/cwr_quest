<template>
    <div>
        <div class="panel dashboard-panel" :class="getPanelClass()">
            <div class="panel-heading">
                <div class="panel-heading-container">
                    <div class="head-label copay-label">
                        Payments
                    </div>
                    <div class="head-count">
                        <span v-if="!loading" :class="{'big-copay': getFormattedMoney(getTotalMissingCopay(), false).length > 7}">
                            {{ getFormattedMoney(getTotalMissingCopay(), false) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding-top: 0">
                <table class="table table-layout-fixed" v-if="patients && patients.length">
                    <thead class="sticky-thead">
                        <tr>
                            <th>Patient</th>
                            <th>Insurance</th>
                            <th>Provider</th>
                            <th width="100px">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr v-for="item in patients">
                        <td>
                            <a :style="{color: '#' + item.status.hex_color}" :href="`${'/chart/' + item.id}`">
                                {{item.patient_name}}
                            </a>
                        </td>
                        <td>{{ item.primary_insurance || '-' }}</td>
                        <td>{{ item.provider_name || '-' }}</td>
                        <td class="count-col">
                            {{getCopay(item)}}
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="panel-loader-container dashboard-table-empty" v-else-if="loading">
                    <pageloader add-classes="panel-loader" />
                </div>
                <div class="dashboard-table-empty" v-else>
                    There are no payment related issues
                </div>
            </div>
        </div>

    </div>
</template>

<script>

    import PatientBalance from './../../../mixins/patient-balance';

    export default {
        data() {
            return {
                patients: null,
                loading: false,
            };
        },

        mixins: [
            PatientBalance
        ],

        mounted() {
            this.loading = true;
            this.$store.dispatch('getDashboardMissingCopay').then(response => {
                if(response.status === 200) {
                    this.patients = response.data;
                    this.loading = false;
                }
            });
        },

        methods: {
            getCopay(item) {
                return this.getFormattedMoney(this.getPatientPreprocessedBalance(item), false)
            },

            getTotalMissingCopay() {
                let total = 0;
                for(let i in this.patients) {
                    total += this.getPatientPreprocessedBalance(this.patients[i]);
                }

                return Math.round(total);
            },

            getPanelClass() {
                if(this.patients && this.patients.length) {
                    return 'panel-red';
                }

                return 'panel-green';
            },
        },
    }
</script>

<style scoped>
    .big-copay {
        font-size: 40px !important;
    }
</style>