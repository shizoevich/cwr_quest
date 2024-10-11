<template>
    <form action="" method="get">
        <div class="form-group inline-block" style="width:170px;margin-right:10px;">
            <label>Therapist Name</label>
            <select name="provider" class="form-control" v-model="provider">
                <option :value="null">All</option>
                <option v-for="provider in providers" :value="provider.id">{{ provider.provider_name }}</option>
            </select>
        </div>

        <div class="form-group date-filter-item">
            <label>Filter By</label>
            <select class="form-control" v-model="selected_filter_type" name="selected_filter_type">
                <option value="1">Date</option>
                <option value="2">Date Range</option>
<!--                <option value="3">Month</option>-->
                <option value="4">Billing Period (Bi-Weekly)</option>
                <option value="5">Billing Period (Monthly)</option>
            </select>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 1 || selected_filter_type == 2">
            <label v-if="selected_filter_type == 1">Date</label>
            <label v-if="selected_filter_type == 2">From</label>
            <ElDatePicker class="date-filter date-filter-2"
                          v-model="date_from"
                          name="date_from"
                          :format="date_format"
                          :value-format="date_format"
                          :editable="false"
                          :clearable="false"/>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 3">
            <label>Month</label>
            <ElDatePicker class="date-filter date-filter-2"
                    v-model="month"
                    name="month"
                    format="dd MMMM yyyy"
                    value-format="dd MMMM yyyy"
                    type="month"
                    :editable="false"
                    :clearable="false"/>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 4">
          <label>Billing Period</label>
          <select class="form-control" v-model="billing_period_id" name="billing_period_id">
            <option :value="period.id" v-for="period in billingPeriods.bi_weekly">{{ $moment(period.start_date).format('MM/DD/YYYY') }} - {{ $moment(period.end_date).format('MM/DD/YYYY') }}</option>
          </select>
        </div>
        <div class="form-group date-filter-item" v-if="selected_filter_type == 5">
          <label>Billing Period</label>
          <select class="form-control" v-model="billing_period_id" name="billing_period_id">
            <option :value="period.id" v-for="period in billingPeriods.monthly">{{ $moment(period.start_date).format('MMMM yyyy') }}</option>
          </select>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 2">
            <label>To</label>
            <ElDatePicker class="date-filter date-filter-2"
                          v-model="date_to"
                          name="date_to"
                          :format="date_format"
                          :value-format="date_format"
                          :editable="false"
                          :clearable="false"/>
        </div>

        <div class="form-group inline-block" style="position:relative;">
            <button class="btn btn-primary" style="width:92px;position:absolute;top:-9px;">Show</button>
        </div>
    </form>
</template>
<script>
    export default {

        props: [
            'propMonth',
            'propFilterType',
            'propBillingPeriodId',
            'propDateFrom',
            'propDateTo',
            'propProviderId',
            'dateToStep',
            'providers',
            'billingPeriods',
        ],

        data() {
            return {
                initialized: false,
                selected_filter_type: 4,
                date_from: null,
                date_to: null,
                date_format: 'MM/dd/yyyy',
                month: null,
                provider: null,
                billing_period_id: null,
            }
        },

        mounted() {
            if(this.propProviderId) {
                this.provider = this.propProviderId;
            }
            if(this.propDateFrom) {
                this.date_from = this.propDateFrom;
            } else {
                this.date_from = new Date();
            }

            if(this.propFilterType) {
                this.selected_filter_type = this.propFilterType;
            }
            if(this.propBillingPeriodId) {
                this.billing_period_id = this.propBillingPeriodId;
            } else if(this.selected_filter_type == 4) {
              this.billing_period_id = this.billingPeriods.bi_weekly[0].id;
            } else if(this.selected_filter_type == 5) {
              this.billing_period_id = this.billingPeriods.monthly[0].id;
            }

            if(this.propMonth) {
                this.month = this.propMonth;
            }

            if(this.propDateTo) {
                this.date_to = this.propDateTo;
            } else {
                this.date_to = this.getDateTo();
            }
            window.setTimeout(() => {
                this.initialized = true;
            }, 500)
        },

        watch: {
            selected_filter_type(value) {
                if(!this.initialized) {
                    return false;
                }
                if(this.billingPeriods) {
                    if(value == 4) {
                        this.billing_period_id = this.billingPeriods.bi_weekly[0].id;
                    } else if(value == 5) {
                        this.billing_period_id = this.billingPeriods.monthly[0].id;
                    } else {
                        this.billing_period_id = null;
                    }
                } else {
                    this.billing_period_id = null;
                }
            },
        },

        methods: {
            getDateTo() {
                let dateStep = 1;
                if(this.dateToStep !== null && this.dateToStep !== undefined) {
                    return this.date_from;
                }
                let tmp = new Date(this.date_from);
                console.log(this.date_from);
                let result = new Date(tmp.setMonth(tmp.getMonth() + dateStep));
                tmp.setMonth(tmp.getMonth() - dateStep);
                return result;
            }
        },

        computed: {},
    }
</script>

<style scoped>
    .date-filter-item {
        width: 225px;
        display: inline-block;
        margin-right: 10px;
    }
</style>