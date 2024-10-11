<template>
    <div class="timeline-item" :class="{'only-for-admin': note.only_for_admin == 1}">
        <div class="btn-group pull-right">
            <button
                type="button"
                class="btn btn-default btn-sm dropdown-toggle btn-d-menu"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <span class="glyphicon glyphicon-option-horizontal"></span>
            </button>
            <ul class="dropdown-menu pull-right custom-dropdown-menu">
                <li v-if="(!is_read_only_mode || is_supervisor_mode) && isFileHasPreview(note.aws_document_name)">
                    <a href="" @click.prevent="previewDoc(note.aws_document_name)">Preview</a>
                </li>
                <li v-if="!is_read_only_mode">
                    <a href="" @click.prevent="downloadDoc(note.aws_document_name)">Download</a>
                </li>
                <li v-if="!is_read_only_mode && note.only_for_admin == 0 && !is_audit_mode">
                    <a href="" @click.prevent="showEmailModal(note)">Send by email</a>
                </li>
                <li v-if="!is_read_only_mode && note.fax_supported && note.only_for_admin == 0 && !is_audit_mode">
                    <a href="" @click.prevent="showFaxModal(note)">Send by fax</a>
                </li>
                <li v-if="!is_read_only_mode && isUserAdmin">
                    <a href="" @click.prevent="confirmDeletionDocument(note)">Delete</a>
                </li>
                <li v-if="!is_read_only_mode && !isUserAdmin && note.model === 'PatientAssessmentForm' && note.is_editing_allowed.allowed">
                    <a href="" @click.prevent="editAssessmetForm(note)">Edit</a>
                </li>
                <li v-if="!is_read_only_mode && isUserAdmin && note.model === 'PatientAssessmentForm' && !note.is_editing_allowed.allowed">
                    <a href="" @click.prevent="allowEditingAssessmentForm(note)">
                        Allow editing (72 hours)
                    </a>
                </li>
                <li v-if="!is_read_only_mode && isUserAdmin && note.model === 'PatientDocument'">
                    <a href="javascript:void(0);" @click.prevent="confirmChangeStatusDialog(note)">
                        Make as
                        <span v-if="note.only_for_admin == 1">Public</span>
                        <span v-else>Private</span>
                    </a>
                </li>
            </ul>
        </div>

        <span class="time">{{ getFormattedTime(note.created_at) }}</span>


        <h3 class="timeline-header">
            <span class="label-blue">{{note.is_tridiuum_document ? 'Tridiuum' : note.document_uploader}}</span>
            <span style="font-weight: 600;">added document</span>
            <span class="label-blue">&laquo;{{note.other_document_type ? note.other_document_type : note.document_type}}&raquo;</span>
        </h3>

        <div class="timeline-body clearfix">
            <div class="">
                <div class="doc-container">
                    {{getFileIcon(note)}}

                    <div class="image-wrap">
                        <img :src="document_previews[note.aws_document_name]"
                             class="img-responsive inline-block" v-if="document_previews && document_previews[note.aws_document_name]">
                        <img :src="default_document_previews[note.aws_document_name]"
                             class="img-responsive inline-block" v-else>
                    </div>


                    <div class="input-wrap">
                        <span class="document-name inline-block">{{note.original_document_name}}</span>

                        <timeline-comment-form
                            v-if="!is_audit_mode"
                            :form-id="`${note.model + note.id}_comments`"
                            :isError="documentCommentError"
                            :errorMessage="documentCommentErrorMessage"
                            @input="storeDocumentComment(note, $event)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="timeline-footer clearfix patient-document-comments" v-if="note.document_comments && note.document_comments.length">
            <timeline-document-comments :note="note"/>
        </div>
    </div>
</template>

<script>
    import TimelineDocumentComments from "./TimelineDocumentComments";
    import TimelineCommentForm from "./TimelineCommentForm";

    import DatetimeFormated from '../../../mixins/datetime-formated';
    import ProviderInfo from '../../../mixins/provider-info';
    import FileInfo from '../../../mixins/file-info';
    import SelfInfo from '../../../mixins/self-info';

    export default {
        name      : "timeline-document",
        components: {
            TimelineCommentForm,
            TimelineDocumentComments,
        },
        mixins    : [
            DatetimeFormated,
            ProviderInfo,
            FileInfo,
            SelfInfo
        ],
        props     : {
            note: Object,
        },
        data() {
            return {
                default_document_previews  : {},
                documentCommentError       : false,
                documentCommentErrorMessage: 'Error connecting to server. Please try to post comment later.',
                documentComment            : '',
            }
        },
        computed  : {
            is_read_only_mode() {
                return this.$store.state.is_read_only_mode;
            },

            is_audit_mode() {
              return this.$store.state.is_audit_mode;
            },

            is_supervisor_mode() {
                return this.$store.state.is_supervisor_mode;
            },

            patient() {
                return this.$store.state.currentPatient;
            },
        },
        methods   : {
           changeDocumentStatus(document_id, previous_status) {
                let payload = {
                    document_id: document_id,
                };
                if(previous_status == 1) {
                    payload.only_for_admin = false;
                } else {
                    payload.only_for_admin = true;
                }

                this.$store.dispatch('changeDocumentStatus', payload).then(() => {
                    this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: this.patient.id});
                });
            },

            storeDocumentComment(note, payload) {
                return this.$parent.$parent.storeDocumentComment(note, payload);
            },
            previewDoc(docName) {
                window.open('/patient/preview-document/' + docName, '_blank');
            },
            downloadDoc(docName) {
                window.open('/patient/download-document/' + docName, '_blank');
            },
            showEmailModal(note) {
                return this.$parent.$parent.showEmailModal(note);
            },
            showFaxModal(note) {
                return this.$parent.$parent.showFaxModal(note);
            },
            confirmDeletionDocument(doc) {
                return this.$parent.$parent.confirmDeletionDocument(doc);
            },
            confirmChangeStatusDialog(doc) {
                return this.$parent.$parent.confirmChangeStatusDialog(doc);
            },
            editAssessmetForm(form) {
                return this.$parent.$parent.editAssessmetForm(form);
            },
            allowEditingAssessmentForm(form) {
                return this.$parent.$parent.allowEditingAssessmentForm(form);
            },
        }
    }
</script>

<style scoped lang="scss">
    $imageSize: 150px;

    .doc-container {
        display: inline-flex;
        width: 100%;
    }

    .image-wrap {
        width: $imageSize;
        height: $imageSize;
        text-align: center;
        min-width: $imageSize;
        max-width: $imageSize;
        min-height: $imageSize;
        max-height: $imageSize;
    }

    .img-responsive {
        max-width: $imageSize;
        max-height: $imageSize;
    }

    .input-wrap {
        /*min-width: calc(100% - $imageSize);*/
        width: 100%;
        max-width: calc(100% - 150px);
        padding-left: 10px;
    }

    .document-name {
        min-height: 110px;
        font-weight: 600;
    }

    .btn-d-menu {
        border-radius: 30px;
        width: 30px;
        height: 30px;
        padding: 6px;
        margin: 4px;
    }
    .patient-document-comments {
        font-size: 12px;
    }
</style>
