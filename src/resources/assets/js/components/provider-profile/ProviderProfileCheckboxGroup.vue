<template>
    <div>
        <benefits-checkbox
            :disabled="disabled"
            :provider-id="provider.id"
            :provider-name="provider.provider_name"
            :checked="!!provider.has_benefits"
        ></benefits-checkbox>

        <training-checkbox
            :disabled="disabled"
            :provider-id="provider.id"
            :provider-name="provider.provider_name"
            :checked="!!provider.is_new"
        ></training-checkbox>

        <collect-payment-checkbox
            :disabled="disabled"
            :provider-id="provider.id"
            :provider-name="provider.provider_name"
            :checked="!!provider.is_collect_payment_available"
        ></collect-payment-checkbox>

        <associate-checkbox
            :disabled="disabled"
            :provider-id="provider.id"
            :provider-name="provider.provider_name"
            :checked="!!provider.is_associate"
        ></associate-checkbox>

        <works-with-upheal-checkbox
            :disabled="disabled"
            :provider-id="provider.id"
            :provider-name="provider.provider_name"
            :checked="!!provider.works_with_upheal"
        ></works-with-upheal-checkbox>
    </div>
</template>

<script>
import BenefitsCheckbox from "./BenefitsCheckbox";
import TrainingCheckbox from "./TrainingCheckbox";
import CollectPaymentCheckbox from "./CollectPaymentCheckbox";
import AssociateCheckbox from "./AssociateCheckbox";
import WorksWithUphealCheckbox from "./WorksWithUphealCheckbox";

export default {
    props: {
        providerData: {
            type: String,
            default: () => '{}'
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },

    components: {
        BenefitsCheckbox,
        TrainingCheckbox,
        CollectPaymentCheckbox,
        AssociateCheckbox,
        WorksWithUphealCheckbox
    },

    data() {
        return {
            provider: {}
        }
    },

    watch: {
        providerData() {
            this.initProvider();
        }
    },

    mounted() {
        this.initProvider();
    },

    methods: {
        initProvider() {
            try {
                this.provider = JSON.parse(this.providerData);
            } catch(e) {
                this.provider = {};
            }
        }
    }
}
</script>