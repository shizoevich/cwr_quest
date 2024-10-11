<template>
    <div class="table-responsive" style="border:none;">
        <table class="table table-bordered">
            <tbody>
                <tr v-for="plan in plans" v-bind:key="plan.id">
                    <td>
                        <span></span>{{ plan.name }}
                        <span 
                            class="badge"
                            style="cursor: pointer"
                            data-toggle="tooltip"
                            data-html="true"
                            data-placement="right"
                            :title="getChildPlansTooltipText(plan.child_plans)"
                        >
                            {{ plan.child_plans.length }}
                        </span>
                    </td>
                    <td>
                        <label style="font-weight: normal">
                            <input
                                type="checkbox"
                                v-model="plan_configs[plan.id].dont_need_collect_copay_for_telehealth"
                                @change="updateConfig(plan.id)"
                            >
                            Plan covers the CoPay (Telehealth Session)
                        </label>
                        <br />
                        <label style="font-weight: normal">
                            <input
                                type="checkbox"
                                v-model="plan_configs[plan.id].is_verification_required"
                                @change="updateConfig(plan.id)"
                            >
                            Plan requires reauthorization
                        </label>
                        <br />

                        <label v-if="plan_configs[plan.id].is_verification_required" style="font-weight: normal">
                            <input
                                type="checkbox"
                                v-model="plan_configs[plan.id].requires_reauthorization_document"
                                @change="updateConfig(plan.id)"
                            >
                            Plan requires document for reauthorization
                        </label>
                        <br />

                        <div v-if="plan_configs[plan.id].is_verification_required">
                            <h5 style="margin-bottom: 10px;">
                                <b>Reauthorization warning when left:</b>
                            </h5>
                            <div class="notify-inputs">
                                <label style="font-weight: normal;">
                                    <span>Visits count</span>
                                    <el-input-number
                                        v-model="plan_configs[plan.id].reauthorization_notification_visits_count"
                                        :min="1"
                                        :controls="false"
                                        class="form-field form-field-number"
                                        @blur="checkInputNumbersChange(plan.id)"
                                    />
                                </label>
                                <label style="font-weight: normal;">
                                    <span>Days count</span>
                                    <el-input-number
                                        v-model="plan_configs[plan.id].reauthorization_notification_days_count"
                                        :min="1"
                                        :controls="false"
                                        class="form-field form-field-number"
                                        @blur="checkInputNumbersChange(plan.id)"
                                    />
                                </label>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import { Notification } from 'element-ui';

export default {
    name: 'InsurancePlans',
    props: {
        plans: {
            required: true,
            type: Array,
        }
    },

    data() {
        return {
            plan_configs: {},
        };
    },

    beforeMount() {
        for (let plan of this.plans) {
            this.plan_configs[plan.id] = {
                dont_need_collect_copay_for_telehealth: !plan.need_collect_copay_for_telehealth,
                is_verification_required: plan.is_verification_required,
                requires_reauthorization_document: plan.requires_reauthorization_document,
                reauthorization_notification_visits_count: plan.reauthorization_notification_visits_count,
                reauthorization_notification_days_count: plan.reauthorization_notification_days_count
            };
        }
    },

    mounted() {
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    },

    methods: {
        updateConfig(planId) {
            let data = {
                need_collect_copay_for_telehealth: !this.plan_configs[planId].dont_need_collect_copay_for_telehealth,
                is_verification_required: this.plan_configs[planId].is_verification_required,
            };
            if (this.plan_configs[planId].is_verification_required) {
                data['requires_reauthorization_document'] = this.plan_configs[planId].requires_reauthorization_document,
                data['reauthorization_notification_visits_count'] = this.plan_configs[planId].reauthorization_notification_visits_count;
                data['reauthorization_notification_days_count'] = this.plan_configs[planId].reauthorization_notification_days_count;
            }

            axios.put('/api/system/insurances/plans/' + planId, data).then(response => {
                Notification.success({
                    title: 'Success',
                    message: '"' + response.data.plan.name + '" successfully updated.',
                    type: 'success'
                });

                this.plans = this.plans.map(plan => {
                    if (plan.id === planId) {
                        return response.data.plan;
                    }

                    return plan;
                });

            });
        },

        checkInputNumbersChange(id) {
            const selectedPlan = this.plans.find(plan => plan.id === id);
            if (selectedPlan &&
                ((selectedPlan.reauthorization_notification_visits_count !== this.plan_configs[id].reauthorization_notification_visits_count) ||
                    (selectedPlan.reauthorization_notification_days_count !== this.plan_configs[id].reauthorization_notification_days_count))) {
                this.updateConfig(id);
            }
        },

        getChildPlansTooltipText(childPlans) {
            const planNames = childPlans.map(plan => plan.name);

            return planNames.join('<br />')
        },
    },
}
</script>

<style lang="scss" scoped>
.notify-inputs {
    display: flex;
    gap: 20px;

    .form-field-number {
        width: 100px;
    }
}
</style>