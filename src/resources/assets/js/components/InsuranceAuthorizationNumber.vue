<template>
    <div class="profile-value d-flex align-items-center gap-1">
        <div class="profile-value" v-html="authorizationNumber" :class="{'overdue': isOverdue, 'almost-overdue': isAlmostOverdue}"></div>
        <el-tooltip v-if="patient" class="item" effect="dark" placement="bottom">
            <template #content>
                <div v-html="authorizationNumberTooltipText"></div>
            </template>
            <help />
        </el-tooltip>
    </div>
</template>
  
<script>
export default {
    props: {
        patient: {
            type: Object,
        }
    },
    computed: {
        disableTooltip() {
            return !this.patient;
        },

        authorizationNumber() {
            return this.patient && this.patient.insurance_authorization_number ? this.patient.insurance_authorization_number : 'N/A';
        },

        authorizationNumberTooltipText() {
            if (!this.patient) {
                return null;
            }

            if (!this.patient.insurance_authorization_number) {
                return 'Authorization number is not set';
            }

            const { insurance_visits_auth, insurance_visits_auth_left, insurance_eff_start_date, insurance_eff_stop_date } = this.patient;
            return `No. of Visits Authorized: ${insurance_visits_auth || 'N/A'} <br />
                    No. of Visits Left: ${insurance_visits_auth_left || 'N/A'} <br />
                    Eff. Start Date: ${insurance_eff_start_date ? moment(insurance_eff_start_date, 'YYYY-MM-DD').format('MM/DD/YY') : 'N/A'} <br />
                    Eff. Stop Date: ${insurance_eff_stop_date ? moment(insurance_eff_stop_date, 'YYYY-MM-DD').format('MM/DD/YY') : 'N/A'}`;
        },

        isAlmostOverdue() {
            if (!this.patient) {
                return false;
            }

            const { insurance_requires_verification, insurance_visits_auth_left, insurance_eff_stop_date, reauthorization_notification_visits_count, reauthorization_notification_days_count } = this.patient;

            if (
                insurance_requires_verification &&
                (insurance_visits_auth_left <= reauthorization_notification_visits_count
                    && insurance_visits_auth_left > 0)
            ) {
                return true;
            }

            const currentDate = moment(moment().format('YYYY-MM-DD'));
            if (
                insurance_requires_verification && insurance_eff_stop_date
                && moment(insurance_eff_stop_date).diff(currentDate, 'days') <= reauthorization_notification_days_count
                && moment(insurance_eff_stop_date).diff(currentDate, 'days') > 0
            ) {
                return true;
            }

            return false;
        },

        isOverdue() {
            if (!this.patient) {
                return false;
            }
            
            const { insurance_requires_verification, insurance_authorization_number, insurance_visits_auth_left, insurance_eff_stop_date } = this.patient;
            
            if (insurance_requires_verification && !insurance_authorization_number) {
                return true;
            }

            if (insurance_requires_verification && insurance_visits_auth_left <= 0) {
                return true;
            }
            
            const currentDate = moment(moment().format('YYYY-MM-DD'));
            if (insurance_requires_verification && insurance_eff_stop_date && moment(insurance_eff_stop_date).diff(currentDate, 'days') <= 0) {
                return true;
            }

            return false;
        },
    }
};
</script>

<style lang="scss" scoped>
.profile-value {
    width: fit-content !important;
}
</style>