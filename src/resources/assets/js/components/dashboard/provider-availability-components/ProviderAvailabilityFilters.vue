<template>
    <form action="" method="get">
        <div class="form-group date-filter-item">
            <label>Filter By</label>
            <select class="form-control" v-model="selected_filter_type" name="selected_filter_type">
                <option value="1">Week</option>
                <option value="2">Billing Period (Bi-Weekly)</option>
                <option value="3">Billing Period (Monthly)</option>
            </select>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 1">
            <label>Week</label>
            <ElDatePicker class="date-filter date-filter-2"
                    v-model="week"
                    name="week"
                    type="week"
                    :format="weekFormat"
                    :editable="false"
                    :clearable="false"
                    :picker-options="weekOption"/>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 2">
            <label>Billing Period</label>
            <select class="form-control" v-model="billing_period_id" name="billing_period_id">
                <option v-for="period in billingPeriods.bi_weekly" :key="period.id" :value="period.id">
                    {{ $moment(period.start_date).format('MM/DD/YYYY') }} - {{ $moment(period.end_date).format('MM/DD/YYYY') }}
                </option>
            </select>
        </div>
        <div class="form-group date-filter-item" v-if="selected_filter_type == 3">
            <label>Billing Period</label>
            <select class="form-control" v-model="billing_period_id" name="billing_period_id">
                <option v-for="period in billingPeriods.monthly" :key="period.id" :value="period.id">
                    {{ $moment(period.start_date).format('MMMM yyyy') }}
                </option>
            </select>
        </div>

        <div class="form-group inline-block" style="position:relative;">
            <button class="btn btn-primary" style="width:92px;position:absolute;top:-9px;">Show</button>
        </div>
    </form>
</template>
<script>
    export default {
        name: 'ProviderAvailabilityFilters',

        props: [
            'propFilterType',
            'propWeek',
            'propBillingPeriodId',
            'billingPeriods',
        ],

        data() {
            return {
                initialized: false,
                selected_filter_type: 1,
                week: null,
                billing_period_id: null,
                weekOption: {
                    firstDayOfWeek: 1,
                }
            }
        },

        computed: {
            weekFormat() {
                let startDateOfWeek = this.$moment(this.week).startOf('isoWeek').format('MM/DD/YYYY');
                let lastDateOfWeek = this.$moment(this.week).endOf('isoWeek').format('MM/DD/YYYY');

                return `${startDateOfWeek} - ${ lastDateOfWeek }`;
            }
        },

        mounted() {
            if(this.propFilterType) {
                this.selected_filter_type = this.propFilterType;
            }

            if(this.propWeek) {
                this.week = this.propWeek;
            }
            
            if(this.propBillingPeriodId) {
                this.billing_period_id = this.propBillingPeriodId;
            } else if(this.selected_filter_type == 2) {
              this.billing_period_id = this.billingPeriods.bi_weekly[0].id;
            } else if(this.selected_filter_type == 3) {
              this.billing_period_id = this.billingPeriods.monthly[0].id;
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
                    if(value == 2) {
                        this.billing_period_id = this.billingPeriods.bi_weekly[0].id;
                    } else if(value == 3) {
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
            
        },
    }
</script>

<style scoped>
    .date-filter-item {
        width: 225px;
        display: inline-block;
        margin-right: 10px;
    }
</style>