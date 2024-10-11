<template>
    <div class="timesheets-filters" v-loading.fullscreen.lock="isLoading">
        <el-form ref="patientForm" :model="formData" class="timesheets-filters-form">
            <div class="form-row">
<!--                <div class="form-col">-->
<!--                    <el-form-item label="Billing Period Type" prop="billing_period_type">-->
<!--                        <el-select v-model="formData.billing_period_type" placeholder=""-->
<!--                                   @change="changeBillingPeriodType">-->
<!--                            <el-option-->
<!--                                    v-for="item in billingPeriodTypeList"-->
<!--                                    :key="item.id"-->
<!--                                    :label="item.text"-->
<!--                                    :value="item.value">-->
<!--                            </el-option>-->
<!--                        </el-select>-->
<!--                    </el-form-item>-->
<!--                </div>-->
                <div class="form-col">
                    <el-form-item label="Billing Period" prop="billing_period">
                        <el-select v-model="formData.billing_period" placeholder="" @change="changeBillingPeriod">
                            <el-option
                                    v-for="item in billingPeriodList"
                                    :key="item.id"
                                    :label="item.date"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </div>
                <div class="form-col">
                    <el-form-item label="Status" prop="billing_period">
                        <el-select v-model="formData.status" placeholder="" @change="changeStatus">
                            <el-option
                                    v-for="item in statusList"
                                    :key="item.id"
                                    :label="item.status"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </div>
            </div>
        </el-form>
    </div>
</template>

<script>
    export default {
        name: 'TimesheetsPanelFilters',
        data() {
            return {
                isLoading: false,
                formData: {
                    billing_period_type: 'bi_weekly',
                    billing_period: null,
                    status: 1,
                },
                billingPeriodTypeList: [
                    {id: 1, text: 'Bi-Weekly', value: 'bi_weekly'},
                    {id: 2, text: 'Monthly', value: 'monthly'}
                ],
                statusList: [
                    {id: 1, status: 'All', property: 'all', isEqualsNull: false},
                    {id: 2, status: 'Signed', property: 'signed_at', isEqualsNull: false},
                    {id: 3, status: 'Not Signed', property: 'signed_at', isEqualsNull: true},
                    {id: 4, status: 'Reviewed', property: 'reviewed_at', isEqualsNull: false}
                ],
                globalBillingPeriodList: [],
                billingPeriodList: [],
                timesheetsList: [],
                timesheetsListFromBillingPeriod: [],
            }
        },
        watch: {
            timesheetsList: {
                handler(value) {
                    this.$emit('changeTimesheetsList', value)
                },
                deep: true
            },
        },
        methods: {
            initTimesheetsData() {
                this.isLoading = true;
                this.$store.state.timesheets.billing_period_id = this.formData.billing_period;
                this.$store.dispatch('getTimesheetsList', {billing_period_id: this.formData.billing_period})
                    .then(({data}) => {
                        this.timesheetsListFromBillingPeriod = data;
                        this.timesheetsList = data;
                        this.changeStatus();
                    })
                    .catch(() => {
                        this.$message({
                            type: 'error',
                            message: 'Oops, something went wrong!',
                            duration: 10000,
                        });
                    })
                    .finally(() => this.isLoading = false)
            },
            initBillingPeriodList() {
                if( this.$store.state.timesheets.status != null && Number(this.formData.status)===1) {
                    this.formData.status = this.$store.state.timesheets.status;
                }
                this.isLoading = true;
                this.$store.dispatch('getBillingPeriodList')
                    .then(({data}) => {
                        this.globalBillingPeriodList = this.changeBillingPeriodsData(data.billing_periods);
                        this.changeBillingPeriodType();
                    })
                    .finally(() => {
                        this.isLoading = false;
                    })
            },
            changeBillingPeriodsData(data) {
                for (const dataKey in data) {
                    let isMonthly = dataKey === 'monthly';
                    if (data.hasOwnProperty(dataKey) && Array.isArray(data[dataKey])) {
                        data[dataKey].forEach((item) => {
                            if (isMonthly) {
                                item.date = moment(item.start_date).format('MMMM YYYY');
                                return;
                            }
                            item.date = moment(item.start_date).format('MM/DD/YYYY') + ' - ' + moment(item.end_date).format('MM/DD/YYYY');
                        })
                    }
                }
                return data;
            },
            changeBillingPeriodType() {
                let indexFirstElement = this.formData.billing_period_type === 'monthly' ? 0 : 1;
                this.billingPeriodList = this.globalBillingPeriodList[this.formData.billing_period_type];
                this.formData.billing_period = this.$store.state.timesheets.billing_period_id == null
                    ? this.billingPeriodList[indexFirstElement].id : this.$store.state.timesheets.billing_period_id;
                this.changeBillingPeriod();
            },
            changeBillingPeriod() {
                this.initTimesheetsData();
                this.$emit('changeBillingPeriod', Number(this.formData.billing_period));
            },
            changeStatus() {

                this.$store.state.timesheets.status = this.formData.status;
                let currentElement = this.statusList.find(item => Number(item.id) === Number(this.formData.status));
                if(currentElement.property === 'all') {
                    this.timesheetsList = this.timesheetsListFromBillingPeriod;
                    return;
                }
                this.timesheetsList = this.timesheetsListFromBillingPeriod.filter(item => {
                    if (currentElement.isEqualsNull) {
                        return item[currentElement.property] === null;
                    }
                    if (currentElement.property === 'signed_at') {
                        return item[currentElement.property] !== null && item['reviewed_at'] === null
                    }
                    return item[currentElement.property] !== null;
                })
            },
        },
        mounted() {
            this.initBillingPeriodList();
        }
    }
</script>

<style lang="scss">
    .timesheets-filters {
        margin-bottom: 15px;

        &-form {

            .form-row {
                display: flex;
                flex-direction: column;
                margin: 0 -15px;

                @media (min-width: 768px) {
                    flex-direction: row;
                    align-items: flex-end;
                    margin: 0 -25px;
                }

                .form-col {
                    padding: 0 15px;

                    @media (min-width: 768px) {
                        padding: 0 25px;
                    }
                }

                .el-form-item {
                    display: flex;
                    align-items: flex-start;
                    flex-direction: column;
                }
            }
        }
    }
</style>

