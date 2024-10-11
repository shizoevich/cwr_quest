<template>
    <div class="timeline-item progress-note-block" :class="{'isnt-finalized': note.is_finalized == 0}">
        <div class="btn-group btn-group pull-right">
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
                <li v-if="!is_read_only_mode || is_supervisor_mode">
                    <a href="" @click.prevent="viewNote(note.id)">View/Edit</a>
                </li>
                <li v-if="!is_read_only_mode && note.is_finalized == 1">
                    <a href="" @click.prevent="exportNote(note.id)">Download</a>
                </li>
                <li v-if="!is_read_only_mode && note.is_finalized == 1 && !is_audit_mode">
                    <a href="" @click.prevent="showEmailModal(note)">Send by email</a>
                </li>
                <li v-if="!is_read_only_mode && note.is_finalized == 1 && note.fax_supported && !is_audit_mode">
                    <a href="" @click.prevent="showFaxModal(note)">Send by fax</a>
                </li>
                <li v-if="!is_read_only_mode && isUserAdmin && !note.is_editing_allowed.allowed">
                    <a href="" @click.prevent="allowEditingNote(note)">
                        Allow editing (72 hours)
                    </a>
                </li>
                <li v-if="!is_read_only_mode && isUserAdmin">
                    <a href="" @click.prevent="confirmDeletionDocument(note)">
                        Delete
                    </a>
                </li>
            </ul>
        </div>

        <span class="time">{{ getFormattedTime(note.created_at) }}</span>

        <h3 class="timeline-header">
            <span class="label-blue">{{ note.document_uploader }}</span>
            <span style="font-weight:600;">added</span>
            <span class="label-blue">&laquo;Progress Note&raquo;</span>
        </h3>

        <div class="timeline-body clearfix">
            <div class="col-xs-9">
                <table class="timeline-table">
                    <tr>
                        <td width="33%" align="right">Provider name:</td>
                        <td>
                            <span class="label-blue bold">{{ note.provider_name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="33%" align="right">Diagnosis and ICD Code:</td>
                        <td>
                            <span class="break-word">{{ note.diagnosis_icd_code }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="33%" align="right">Long range Treatment Goal:</td>
                        <td>
                            <span class="break-word">{{ note.long_range_treatment_goal }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td width="33%" align="right">Short term Behavioral Objective(s):</td>
                        <td>
                            <span class="break-word">{{ note.shortterm_behavioral_objective }}</span>
                        </td>
                    </tr>

                    <template v-if="isUserAdmin && !isUserSecretary">
                        <tr v-if="note.additional_comments">
                            <td width="33%" align="right">Additional Comments:</td>
                            <td>
                                <span class="break-word">{{ getTruncatedString(note.additional_comments) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td width="33%" align="right">Plan:</td>
                            <td>
                                <span class="break-word">{{ getTruncatedString(note.plan) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td width="33%" align="right">Interventions:</td>
                            <td>
                                <span class="break-word">{{ getTruncatedString(note.interventions) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td width="33%" align="right">Progress and Outcome:</td>
                            <td>
                                <span class="break-word">{{ getTruncatedString(note.progress_and_outcome) }}</span>
                            </td>
                        </tr>
                    </template>
                </table>
            </div>
            <div class="col-xs-3 inline date-of-service">
                <p>Date of Service: <span style="font-weight:600;">{{ getFormattedDate(note.date_of_service) }}</span></p>
            </div>
            <div class="col-xs-3 inline date-of-service">
                <p>Created At: <span style="font-weight:600;">{{ getFormattedDateTime(note.created_at) }}</span></p>
            </div>
            <div class="col-xs-3 inline date-of-service">
                <p>Updated At: <span style="font-weight:600;">{{ getFormattedDateTime(note.updated_at) }}</span></p>
            </div>
            <div class="col-xs-3 inline date-of-service">
                <p>Finalized At: <span style="font-weight:600;">{{ getFormattedDateTime(note.finalized_at) }}</span></p>
            </div>
        </div>

        <div class="timeline-footer clearfix patient-document-comments">
            <timeline-comment-form
                v-if="!is_audit_mode"
                :form-id="`${note.model + note.id}_comments`"
                :isError="documentCommentError"
                :errorMessage="documentCommentErrorMessage"
                @input="storeDocumentComment(note, $event)"
            />

            <timeline-document-comments 
                v-if="note.document_comments && note.document_comments.length"
                :note="note"
            />
        </div>
    </div>
</template>

<script>
    import TimelineDocumentComments from "./TimelineDocumentComments";
    import TimelineCommentForm from "./TimelineCommentForm";

    import DatetimeFormated from '../../../mixins/datetime-formated';
    import SelfInfo from '../../../mixins/self-info';

    import { truncate } from "./../../../helpers/text";

    export default {
        name: "timeline-note",
        mixins: [
            DatetimeFormated,
            SelfInfo
        ],
        props: {
            note: Object,
        },
        components: {
            TimelineCommentForm,
            TimelineDocumentComments,
        },
        data() {
            return {
                documentCommentError: false,
                documentCommentErrorMessage: 'Error connecting to server. Please try to post comment later.',
                documentComment: ''
            }
        },
        computed: {
            is_read_only_mode() {
                return this.$store.state.is_read_only_mode;
            },
            is_audit_mode() {
                return this.$store.state.is_audit_mode;
            },
            is_supervisor_mode() {
                return this.$store.state.is_supervisor_mode;
            }
        },
        methods: {
            storeDocumentComment(note, payload) {
                return this.$parent.$parent.storeDocumentComment(note, payload);
            },
            viewNote(note_id) {
                return this.$parent.$parent.viewNote(note_id);
            },
            exportNote(note_id) {
                return this.$parent.$parent.exportNote(note_id);
            },
            showFaxModal(note) {
                return this.$parent.$parent.showFaxModal(note);
            },
            showEmailModal(note) {
                return this.$parent.$parent.showEmailModal(note);
            },
            allowEditingNote(note) {
                return this.$parent.$parent.allowEditingNote(note);
            },
            confirmDeletionDocument(note) {
                return this.$parent.$parent.confirmDeletionDocument(note);
            },
            getFormattedDate(date) {
                return date ? this.$moment(date).format('MM/DD/YYYY') : '—';
            },
            getFormattedDateTime(dateTime) {
                return dateTime ? this.$moment(dateTime).format('MM/DD/YYYY h:mm A') : '—';
            },
            getTruncatedString(str) {
                return truncate(str, 250);
            }
        }
    }
</script>

<style scoped>
    .progress-note-block.inline > * {
        display: inline-block;
    }

    .progress-note-block.inline > h4 {
        color: gray;
    }

    .progress-note-block > * {
        font-size: 14px;
    }

    .progress-note-block > h4 {
        color: gray;
    }

    .progress-note-block table tr td:last-child {
        padding-left: 10px;
        font-weight: 600;
    }
    .progress-note-block table tr td {
        vertical-align: top !important
    }
    .btn-d-menu {
        border-radius: 30px;
        width: 30px;
        height: 30px;
        padding: 6px;
        margin: 4px;
    }
    .patient-document-comments {
        padding-bottom: 10px;
        font-size: 12px;
    }
    .date-of-service {
        text-align: right;
    }
</style>