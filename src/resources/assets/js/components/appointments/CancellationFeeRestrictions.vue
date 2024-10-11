<template>
    <div v-if="!canChargeLateCancellationFee" :class="{'only-reasons': onlyReasons, 'alert alert-danger cancellation-fee-alert': !onlyReasons}">
        <span v-if="!onlyReasons">Сancellation fee cannot be collected due to the following reasons:</span>
        <ul v-if="!patientLateCancellationFeeInfo.booking_cancellation_policy.is_supported_by_insurance">
            <li>{{ reasonsForBanChargeCancellationFee.is_supported_by_insurance }}</li>
        </ul>
        <ul v-else>
            <li v-for="(reason, index) in filteredLateCancellationReasons" :key="index">{{ reason }}</li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        patientLateCancellationFeeInfo: {
            type: Object,
            required: true
        },
        onlyReasons: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            reasonsForBanChargeCancellationFee: {
                is_completed_form: "The required document has not been signed by the patient",
                is_card_on_file: "Patient doesn't have credit card on file",
                is_charge_for_cancellation_non_zero: "Cancellation fee amount in form set as zero",
                is_supported_by_insurance: "The type of patient insurance doesn't allow to collect cancellation fee"
            }
        }
    },

    computed: {
        canChargeLateCancellationFee() {
            let canChargeLateCancellationFee = true;

            if (this.patientLateCancellationFeeInfo) {
                const properties = Object.values(this.patientLateCancellationFeeInfo.booking_cancellation_policy);
                canChargeLateCancellationFee = properties.every((property) => property);
            }

            return canChargeLateCancellationFee;
        },
        lateCancellationReasons() {
            const reasons = [];

            if (this.patientLateCancellationFeeInfo) {
                Object.keys(this.patientLateCancellationFeeInfo.booking_cancellation_policy).forEach(key => {
                    if (!this.patientLateCancellationFeeInfo.booking_cancellation_policy[key]) {
                        reasons.push(this.reasonsForBanChargeCancellationFee[key]);
                    }
                }) 
            }

            return reasons;
        },
        filteredLateCancellationReasons() {
            if (!this.patientLateCancellationFeeInfo.booking_cancellation_policy.is_completed_form) {
                return this.lateCancellationReasons.filter(el => el !== this.reasonsForBanChargeCancellationFee.is_charge_for_cancellation_non_zero);
            }

            return this.lateCancellationReasons;
        }
    },
}
</script>

<style lang="scss" scoped>
.cancellation-fee-alert {
    font-size: 14px;
}

.only-reasons {
    ul {
        margin: 0;
        padding-left: 10px;
        list-style: none;

        li:not(:last-child) {
            margin-bottom: 5px;
        }
    }
}
</style>