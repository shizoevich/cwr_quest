<template>
    <div class="timeline-item">
        <span class="time">{{getFormattedTime(note.created_at)}}</span>

        <div class="timeline-body" v-if="note.model !== 'CallLog'" v-html="note.comment">
            {{note.comment}}
        </div>
        <div v-else class="timeline-body">
            <p class="head-status-p">
                <b class="label-blue">{{ note.full_admin_name }}</b> 
                called from <span class="label-blue">{{ getUsFormat(note.aws_document_name) }}</span> to 
                <span class="label-blue">{{ getUsFormat(note.original_document_name) }}</span>
            </p>
            <p class="status-p" v-if="note.document_type">
                <span class="label-blue">Status:</span> 
                <span :class="{
                    'call-status-success': note.is_finalized === 'Success',
                    'call-status-failed': note.is_finalized === 'CannotReach' || note.is_finalized === 'NoAnsweringMachine',
                    'call-status-inprogress': note.is_finalized === 'InProgress'
                }">{{ note.document_type }}</span>
            </p>
            <p class="status-p" v-if="note.long_range_treatment_goal && note.long_range_treatment_goal > 0"><span class="label-blue">Duration:</span> {{ getDurationFormat(note.long_range_treatment_goal * 1000) }}</p>
            <p class="status-p">{{ note.comment }}</p>
        </div>
    </div>
</template>

<script>
    import DatetimeFormated from '../../../mixins/datetime-formated';
    import PhoneFormat from '../../../mixins/phone-format';

    export default {
        name: "timeline-alert",
        mixins: [
            DatetimeFormated,
            PhoneFormat
        ],
        props: {
            note: Object,
        },
        data() {
            return {

            }
        },
    }
</script>

<style scoped>

</style>