<template>
    <el-tooltip class="item" effect="dark" placement="bottom" style="margin: 0px 3px;">
        <template #content>
            <ul class="visit-frequency-changes-list">
                <li v-for="change in visitFrequencyChanges" :key="change.id">
                    {{ formatVisitFrequencyTooltipItem(change) }}
                </li>
            </ul>
        </template>
        <help />
    </el-tooltip>
</template>
  
<script>
export default {
    name: 'VisitFrequencyChangesTooltip',
    props: {
        visitFrequencyChanges: {
            type: Array,
            default: () => []
        }
    },
    methods: {
        formatVisitFrequencyTooltipItem(change) {
            const changedBy = change.changed_by;
            const oldStage = change.old_visit_frequency;
            const newStage = change.new_visit_frequency;
            const changeDate = this.$moment(change.created_at).format('MM/DD/YYYY hh:mm A');
            const oldStageText = oldStage && oldStage.name ? `from "${oldStage.name}"` : '';

            return `${changedBy && changedBy.user_name} changed ${oldStageText} to "${newStage && newStage.name}" at ${changeDate}`;
        },
    }
}
</script>

<style lang="scss" scoped>
.visit-frequency-changes-list {
    padding: 0 0 0 15px;
    margin: 0;

    li:not(:last-child) {
        margin-bottom: 5px;
    }
}
</style>