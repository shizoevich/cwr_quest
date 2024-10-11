<template>
    <div class="comment-wrapper" v-if="note.document_comments && note.document_comments.length">
        <button class="btn btn-success show-button" v-if="note.document_comments.length > 5" @click="toggleFullList()">
            {{ fullListShow ? "Show last comments" : "Show all comments" }}
        </button>
        <div class="comment-block"
             v-for="(comment, key) in note.document_comments"
             v-show="fullListShow || key >= note.document_comments.length - 5"
             :key="key"
        >
            <div class="row" :id="`${'PatientDocumentComment' + comment.id}`">
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-2">
                    <p>
                        <b>{{ getProviderName(comment) }}:</b>
                    </p>
                    <p>{{ getFormattedDate(comment.created_at) }}</p>
                </div>
                <div class="col-xs-8 col-sm-8 col-md-8 col-lg-10">
                    <p v-html="comment.content"></p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'CommentBlock',
        props: {
            note: Object,
            fullList: {
                type: Boolean,
                default: false,
            }
        },
        data() {
            return {
                fullListShow: this.fullList,
            }
        },
        computed:  {

        },
        methods: {
            getProviderName(note) {
                let providerName = note.provider_name;
                if (providerName !== null && providerName !== undefined) {
                    return providerName;
                } else if (note.full_admin_name !== null && note.full_admin_name !== undefined) {
                    return note.full_admin_name;
                } else if (note.firstname && note.lastname) {
                    return `${note.firstname} ${note.lastname}`;
                }
                return 'Admin';
            },
            getFormattedDate(date) {
                return this.$moment(date).format('MM/DD/YYYY hh:mm A');
            },
            toggleFullList(){
                this.fullListShow = !this.fullListShow;
            }
        },

    }
</script>

<style scoped>
    .patient-document-comments .comment-block:not(:last-of-type) {
        border-bottom: 1px solid #eee;
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
</style>