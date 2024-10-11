<template>
    <div class="timesheets-panel">
        <div class="timesheets-panel-header">
            <timesheets-panel-filters @changeTimesheetsList="changeTimesheetsList" @changeBillingPeriod="changeBillingPeriod"/>
        </div>
        <div class="timesheets-panel-body">
            <timesheets-panel-list :timesheets-list="timesheetsList" :billing-period-id="billingPeriodId"/>
        </div>
    </div>
</template>

<script>
    import TimesheetsPanelFilters from './Filters';
    import TimesheetsPanelList from './List';

    export default {
        name: 'TimesheetsPanel',
        components: {
            TimesheetsPanelFilters,
            TimesheetsPanelList
        },
        data() {
            return {
                billingPeriodId: null,
                timesheetsList: [],
            }
        },
        methods: {
            changeBillingPeriod(id) {
                this.billingPeriodId = id;
            },
            changeTimesheetsList(data) {
                this.timesheetsList = data;
            }
        }
    }
</script>