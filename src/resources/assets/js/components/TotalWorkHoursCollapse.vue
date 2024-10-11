<template>
    <el-collapse class="work-hours-collapse" :class="{'work-hours-collapse--loading': loading}">
        <el-collapse-item>
            <template slot="title">
                <div class="work-hours-collapse__title">
                    Hours Needed To Meet The Quota: <span :class="{'text-red': remainingWorkHours > 0}">{{ remainingWorkHours }}</span>

                    <pageloader addClasses="work-hours-collapse__loader"></pageloader>
                </div>
            </template>

            <table class="work-hours-table">
                <tbody>
                    <tr>
                        <td>
                            Date Range:
                        </td>
                        <td>
                            {{ getFormattedDateInterval(workHoursPeriod.startDate, workHoursPeriod.endDate) }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The specific period during which these metrics are being tracked.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Required Biweekly Quota:
                        </td>
                        <td>
                            {{ workHoursData.minimum_work_hours * 2 }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The minimum number of visits you need to complete in a two-week period to meet your quota.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Recommended Weekly Quota:
                        </td>
                        <td>
                            {{ workHoursData.minimum_work_hours }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The minimum number of visits you should aim to complete each week to stay on track with your biweekly quota.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            Scheduled Appts:
                        </td>
                        <td>
                            {{ workHoursData.active_hours }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The total number of appointments you currently have scheduled for this week.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Completed Visits:
                        </td>
                        <td>
                            {{ workHoursData.completed_hours + workHoursData.visit_created_hours }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The number of visits you have successfully completed during this week.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Cancelled Appts:
                        </td>
                        <td>
                            {{ workHoursData.cancelled_hours }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The total number of appointments that were cancelled during this week.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Submitted Availability:
                        </td>
                        <td>
                            {{ workHoursData.for_appts_availability_hours }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The number of hours you have submitted this week for appointments with new patients.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                    <tr class="remaining-hours-row">
                        <td>
                            Hours Needed To Meet The Quota:
                        </td>
                        <td>
                            <span :class="{'text-red': remainingWorkHours > 0}">
                                {{ remainingWorkHours }}
                            </span>
                            
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    Additional availability hours you need to add to meet your quota.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Availability for Rescheduling:
                        </td>
                        <td>
                            {{ workHoursData.rescheduling_availability_hours }}
                            <el-tooltip class="item" effect="dark" placement="bottom" style="margin-left: 10px;">
                                <template #content>
                                    The number of hours you allocated for rescheduling any cancelled or missed appointments.
                                </template>
                                <help />
                            </el-tooltip>
                        </td>
                    </tr>
                </tbody>
            </table>
        </el-collapse-item>
    </el-collapse>
</template>

<script>
    export default {
        name: 'TotalWorkHoursCollapse',

        props: {
            loading: {
                type: Boolean,
                default: false,
            },
            workHoursData: {
                type: Object,
                required: true
            },
            workHoursPeriod: {
                type: Object
            }
        },

        computed: {
            remainingWorkHours() {
                if (!this.workHoursData) {
                    return 0;
                }

                const { minimum_work_hours=0, active_hours=0, completed_hours=0, visit_created_hours=0, cancelled_hours=0, for_appts_availability_hours=0 } = this.workHoursData;
                const remainingHours = minimum_work_hours - active_hours - completed_hours - visit_created_hours - cancelled_hours - for_appts_availability_hours;

                return remainingHours < 0 ? 0 : remainingHours;
            },
        },

        methods: {
            getFormattedDateInterval(startDate, endDate) {
                const startDateFormatted = moment(startDate).format('MM/DD/YYYY');
                const endDateFormatted = moment(endDate).format('MM/DD/YYYY');

                return startDateFormatted + ' - ' + endDateFormatted;
            },
        }
    }
</script>

<style lang="scss">
    .work-hours-collapse {
        border: 1px solid #EBEEF5;
        border-radius: 4px;

        .el-collapse-item__header {
            height: 40px;
            padding: 0 5px 0 10px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 4px;
        }

        .el-collapse-item__content {
            padding-bottom: 10px;
        }

        &--loading {
            .work-hours-collapse__loader {
                display: block;
            }
        }
    }

    .work-hours-collapse__title {
        position: relative;
        width: 100%;
    }

    .work-hours-collapse__loader {
        display: none;
        position: absolute;
        right: -18px;
        height: 22px;
        width: auto;
        top: calc(50% - 12px);
        background: white;
        z-index: 1;
    }

    .work-hours-table {
        width: 100%;

        td:first-child {
            font-size: 14px;
            padding-left: 10px;
        }

        td:last-child {
            font-size: 16px;
            font-weight: 700;
            text-align: right;
            padding-right: 10px;
        }

        .remaining-hours-row {
            td:first-child {
                padding-bottom: 10px;
                border-bottom: 1px solid #f5f5f5;
            }

            td:last-child {
                padding-bottom: 10px;
                border-bottom: 1px solid #f5f5f5;
            }

            & + tr {
                td:first-child {
                    padding-top: 10px;
                }

                td:last-child {
                    padding-top: 10px;
                }
            }
        }
    }
</style>