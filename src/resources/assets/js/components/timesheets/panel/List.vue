<template>
    <el-collapse class="collapse-document timesheets-list">
        <timesheets-list-item
            v-for="(item, index) in timesheetsList"
            :key="`id-${index}`"
            :item="item"
            :billing-period-id="billingPeriodId"
        />
    </el-collapse>
</template>

<script>
    import TimesheetsListItem from './ListItem'

    export default {
        name: 'TimesheetsPanelList',
        components: {TimesheetsListItem},
        props: {
            billingPeriodId: {
                type: Number || null,
                default: null,
            },
            timesheetsList: {
                type: Array,
                default() {
                    return [];
                }
            }
        },
    }
</script>

<style lang="scss">
    .timesheets-list {
        padding: 20px 0;
    }
</style>