<template>
    <div class="comment-wrapper" v-if="commentsList && commentsList.length">
        <div 
            v-for="(comment, key) in commentsList"
            :key="key"
            class="comment-block"
        >
            <div class="col-xs-12" :id="`${'PatientDocumentComment' + comment.id}`">
                <div class="date-block">
                    <span class="comment-time" v-html=" getCommentTime(comment.created_at, true, true) "></span>
                </div>

                <div class="text-block">
                    <span class="label-blue bold" v-if="!comment.is_system_comment">{{ getProviderName(comment) }}: </span>
                    <span v-html="comment.content"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DatetimeFormated from '../../../mixins/datetime-formated';
    import ProviderInfo from '../../../mixins/provider-info';

    export default {
        name: 'timeline-document-comments',
        components: {
        },
        mixins  : [
            DatetimeFormated,
            ProviderInfo
        ],
        props: {
            note: Object,
        },
        data() {
            return {

            }
        },
        computed:  {
            commentsList() {
                return this.note.document_comments.slice().reverse();
            }
        },
        methods: {

        },

    }
</script>

<style scoped lang="scss">
    .patient-document-comments .comment-block:not(:last-of-type) {
        /*border-bottom: 1px solid #eee;*/
    }

    .patient-document-comments .comment-block:not(:first-child) p:first-child {
        margin-top: 11px;
    }
    .comment-wrapper{
        position:relative;
    }
    .show-button{
        position: absolute;
        right: 0;
        top:-65px;
        z-index:100;
        min-width: 180px;
    }

    .label-blue {
        color: #1F83BF !important;
    }

    .date-block {
        /*width: 20%;*/
        display: inline-flex;
        margin-right: 15px;
    }
    .text-block {
        width: auto;
        display: inline;

    }
    table td{
        vertical-align: top !important;
    }
    .comment-time {
        color: #999 !important;
        /*color: #1F83BF !important;*/
        font-family: Arial;
    }
    .comment-block {
        > div {
          display: inline-flex;
        }
    }
</style>