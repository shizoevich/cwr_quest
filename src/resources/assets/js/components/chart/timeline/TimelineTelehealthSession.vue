<template>
    <div class="timeline-item" >
        <span class="time">{{getFormattedTime(note.created_at)}}</span>

        <h3 class="timeline-header">
            <span class="label-blue">{{getProviderName(note)}}</span> <span style="font-weight:600;">telehealth session</span>
            (<span class="label-blue">{{note.human_readable_duration}}</span>)
        </h3>

        <div class="timeline-body" v-if="note.child_call_logs && note.child_call_logs.length > 0" style="padding-left:30px;">
            <p v-for="callLog in note.child_call_logs" style="margin-bottom:0;">
                {{ getFormattedDateTime(callLog.date) }} - <span class="label-blue">{{ callLog.caller_name }}</span> {{ callLog.action }} call.
            </p>
        </div>
    </div>
</template>

<script>
    import DatetimeFormated from '../../../mixins/datetime-formated';
    import ProviderInfo from '../../../mixins/provider-info';

    export default {
        name: "timeline-telehealth-session",
        mixins: [
            DatetimeFormated,
            ProviderInfo
        ],
        props: {
            note: Object,
        },
    }
</script>

<style scoped>

</style>