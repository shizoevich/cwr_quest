<template>
    <div class="salary-management">
        <el-tooltip effect="dark" content="Please select filter by Billing Period" :disabled="!!billingPeriodId">
            <div class="salary-management-controller">
                <button class="btn btn-primary btn-sm" @click="showDialog" :disabled="!billingPeriodId">Add / Edit</button>
            </div>
        </el-tooltip>
        <SalaryManagementDialog :billing-period-id="billingPeriodId" :provider-id="providerId" v-if="isShowDialog && billingPeriodId" :is-show-dialog="isShowDialog"  @closeDialog="closeDialog"/>
    </div>
</template>

<script>
    import SalaryManagementDialog from './dashboard/salary-components/SalaryManagementDialog'
    export default {
        name: 'SalaryManagement',
        components: {
            SalaryManagementDialog
        },
        props: {
            billingPeriodId: {
                required: false,
            },
            providerId     : {
                type    : Number,
                required: true,
            }
        },
        data() {
            return {
                isShowDialog: false,
            }
        },
        methods: {
            showDialog() {
                this.isShowDialog = true;
            },
            closeDialog() {
                this.isShowDialog = false;
            }
        }
    }
</script>

<style lang="scss">
    .salary-management {

        &-controller {
            position: absolute;
            top: 8px;
            right: 5px;
        }
    }
</style>