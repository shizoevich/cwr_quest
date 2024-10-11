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
                    <a href="" @click.prevent="viewElectronicDocument(note.document_unique_id, note.id)">
                        View/Edit
                    </a>
                </li>
                <li v-if="!is_read_only_mode">
                    <a :href="`${'/patient/electronic-document/'+note.id+'/download'}`" target="_blank">Download</a>
                </li>
                <li v-if="!is_read_only_mode && !is_audit_mode">
                    <a href="" @click.prevent="showEmailModal(note)">Send by email</a>
                </li>
                <li v-if="!is_read_only_mode && !is_audit_mode && note.fax_supported">
                    <a href="" @click.prevent="showFaxModal(note)">Send by fax</a>
                </li>
                <li v-if="!is_read_only_mode && isUserAdmin && !note.is_editing_allowed.allowed">
                    <a href="" @click.prevent="allowEditingDocument(note)">
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

        <span class="time">{{getFormattedTime(note.created_at)}}</span>

        <h3 class="timeline-header">
            <span class="label-blue">{{note.provider_name}}</span>
            <span style="font-weight:600;">added document</span>
            <span class="label-blue">&laquo;{{note.document_name}}&raquo;</span>
        </h3>

        <div class="timeline-body clearfix">
            <!--<div class="col-xs-9">-->
            <div class="col-lg-12">
                <table v-if="documentTemplateIAA.indexOf(note.document_unique_id) != -1" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Presenting problem:</td>
                        <td><span>{{ note.presenting_problem }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Treatment plan:</td>
                        <td><span>{{ note.treatment_plan }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPIAAWH || note.document_unique_id == documentTemplateIAALA" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">General Summary and clinical impression:</td>
                        <td><span>{{ note.general_summary }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Treatment plan:</td>
                        <td><span>{{ note.treatment_plan }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateIACWH || note.document_unique_id == documentTemplateIACLA" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Presenting concerns:</td>
                        <td><span>{{ note.presenting_concerns }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Treatment plan:</td>
                        <td><span>{{ note.treatment_plan }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPIACPC" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Presenting problem:</td>
                        <td><span>{{ note.presenting_problem }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Treatment plan:</td>
                        <td><span>{{ note.treatment_plan }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateCWRIA" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Long term goals:</td>
                        <td><span>{{ note.long_term_goals }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Short term goals:</td>
                        <td><span>{{ note.short_term_goals }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPRFRPC" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Presenting problem:</td>
                        <td><span>{{ note.presenting_problem }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis and ICD code:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                </table>

                <table v-else-if="documentTemplateKP1RFRWH.indexOf(note.document_unique_id) != -1 || note.document_unique_id == documentTemplateKPRFRLA" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis and ICD code:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Clinical symptoms:</td>
                        <td><span>{{ note.clinical_symptoms }}</span></td>
                    </tr>
                </table>

                <table v-else-if="documentTemplatePDS.indexOf(note.document_unique_id) != -1" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Presenting problem:</td>
                        <td><span>{{ note.presenting_problem }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Reason for discharge:</td>
                        <td><span>{{ note.reason_for_discharge }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateARFR" width="100%">
                    <tr>
                        <td class="td-title" align="right">Requesting provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">ICD code:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Reason for referral:</td>
                        <td><span>{{ note.reason_for_referral }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateFRFR" width="100%">
                    <tr>
                        <td class="td-title" align="right">Requesting provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis and ICD code:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Medical need for service request:</td>
                        <td><span>{{ note.medical_need }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPBHPC" width="100%">
                    <tr>
                        <td class="td-title" align="right">Requesting provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Reason patient is seeking therapy at this time:</td>
                        <td><span>{{ note.therapy_reason }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Additional comments:</td>
                        <td><span>{{ note.additional_comments }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPMER || note.document_unique_id == documentTemplateKPHLOCLA || note.document_unique_id == documentTemplateKPMERLA" width="100%">
                    <tr>
                        <td class="td-title" align="right">Requesting provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis with specifiers:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPMERPC" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Reason for referral:</td>
                        <td><span>{{ note.reason_for_referral }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPBHIOSWH" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                </table>
                <table v-else-if="note.document_unique_id == documentTemplateKPRFGLA" width="100%">
                  <tr>
                      <td class="td-title" align="right">Provider name:</td>
                      <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                  </tr>
                  <tr>
                      <td class="td-title" align="right">Clinical Rationale:</td>
                      <td><span>{{ note.clinical_rationale_for_group_referral }}</span></td>
                  </tr>
                </table>



                <table v-else-if="note.document_unique_id == documentTemplateKPEPCR" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPEPGR" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPEPTR" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateKPEPER" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                    <tr>
                        <td class="td-title" align="right">Diagnosis:</td>
                        <td><span>{{ note.diagnosis_icd_code }}</span></td>
                    </tr>
                </table>

                <table v-else-if="note.document_unique_id == documentTemplateVA" width="100%">
                    <tr>
                        <td class="td-title" align="right">Provider name:</td>
                        <td><span class="label-blue bold">{{ note.provider_name }}</span></td>
                    </tr>
                </table>
            </div>
            <!--<div class="col-xs-3 inline date-of-service">-->
                <!--<p>Date of Service: <span style="font-weight:600;">{{ getFormattedDate(note.date_of_service) }}</span></p>-->
            <!--</div>-->
        </div>

        <div class="timeline-footer clearfix patient-document-comments">

            <timeline-comment-form
                v-if="!is_audit_mode"
                :form-id="`${note.model + note.id}_comments`"
                :isError="documentCommentError"
                :errorMessage="documentCommentErrorMessage"
                @input="storeDocumentComment(note, $event)"
            />

            <timeline-document-comments :note="note"  v-if="note.document_comments && note.document_comments.length"/>
        </div>
    </div>
</template>

<script>
    import TimelineDocumentComments from "./TimelineDocumentComments";
    import TimelineCommentForm from "./TimelineCommentForm";

    import DatetimeFormated from '../../../mixins/datetime-formated';
    import SelfInfo from '../../../mixins/self-info';

    export default {
        name      : "timeline-electronic-document",
        components: {
            TimelineCommentForm,
            TimelineDocumentComments,
        },
        mixins    : [
            DatetimeFormated,
            SelfInfo
        ],
        props     : {
            note: Object,
        },
        data() {
            return {
                documentCommentError       : false,
                documentCommentErrorMessage: 'Error connecting to server. Please try to post comment later.',
                documentComment            : '',
                documentTemplateIAA        : [
                    this.electronicDocumentsTypes('kp_initial_assessment_adult_pc')
                ],
                documentTemplateKPIAAWH    : this.electronicDocumentsTypes('kp_initial_assessment_adult_wh'),
                documentTemplateIACWH      : this.electronicDocumentsTypes('kp_initial_assessment_child_wh'),
                documentTemplateIACLA      : this.electronicDocumentsTypes('kp_initial_assessment_child_la'),
                documentTemplateIAALA      : this.electronicDocumentsTypes('kp_initial_assessment_adult_la'),
                documentTemplateKPIACPC    : this.electronicDocumentsTypes('kp_initial_assessment_child_pc'),
                documentTemplateCWRIA      : this.electronicDocumentsTypes('cwr_initial_assessment'),
                documentTemplateKPRFRPC    : this.electronicDocumentsTypes('kp_request_for_reauthorization_pc'),
                documentTemplateKPRFRLA    : this.electronicDocumentsTypes('kp_request_for_reauthorization_la'),
                documentTemplateKP1RFRWH   : [
                    this.electronicDocumentsTypes('kp1_request_for_reauthorization_wh'),
                    this.electronicDocumentsTypes('kp2_request_for_reauthorization_wh'),
                    this.electronicDocumentsTypes('kp_patient_discharge_summary_wh'),
                    this.electronicDocumentsTypes('kp_patient_discharge_summary_la'),
                ],
                documentTemplatePDS        : [
                    this.electronicDocumentsTypes('cwr_patient_discharge_summary'),
                    this.electronicDocumentsTypes('kp_patient_discharge_summary'),

                ],
                documentTemplateARFR       : this.electronicDocumentsTypes('axminster_rfr'),
                documentTemplateFRFR       : this.electronicDocumentsTypes('facey_rfr'),
                documentTemplateKPBHPC     : this.electronicDocumentsTypes('kp_behavioral_health_pc'),
                documentTemplateKPMER      : this.electronicDocumentsTypes('kp_medication_evaluation_referral'),
                documentTemplateKPMERPC    : this.electronicDocumentsTypes('kp_medication_evaluation_referral_pc'),
                documentTemplateKPMERLA    : this.electronicDocumentsTypes('kp_medication_evaluation_referral_la'),
                documentTemplateKPBHIOSWH  : this.electronicDocumentsTypes('kp_bhios_wh'),
                documentTemplateKPRFGLA    : this.electronicDocumentsTypes('kp_referral_for_groups_los_angeles'),
                documentTemplateKPHLOCLA   : this.electronicDocumentsTypes('kp_hloc_los_angeles'),

                documentTemplateKPEPCR  : this.electronicDocumentsTypes('kpep_couples_counseling_referral'),
                documentTemplateKPEPGR  : this.electronicDocumentsTypes('kpep_group_referral'),
                documentTemplateKPEPTR  : this.electronicDocumentsTypes('kpep_intensive_treatment_referral'),
                documentTemplateKPEPER  : this.electronicDocumentsTypes('kpep_medication_evaluation_referral'),
                documentTemplateVA      : this.electronicDocumentsTypes('va_request_for_reauthorization'),
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
            }
        },
        methods   : {
            showEmailModal(note) {
                return this.$parent.$parent.showEmailModal(note);
            },

            showFaxModal(note) {
                return this.$parent.$parent.showFaxModal(note);
            },

            confirmDeletionDocument(note) {
                return this.$parent.$parent.confirmDeletionDocument(note);
            },

            storeDocumentComment(note, payload) {
                return this.$parent.$parent.storeDocumentComment(note, payload);

            },
            viewElectronicDocument(note_unique_id, note_id) {
                return this.$parent.$parent.getElectronicDocument(note_unique_id, note_id);
            },
            getFormattedDate(date) {
                return date ? this.$moment(date).format('MM/DD/YYYY') : '';
            },
            allowEditingDocument(note){
                return this.$parent.$parent.allowEditingDocument(note);
            },
            electronicDocumentsTypes(type){
                return this.$parent.$parent.electronicDocumentsTypes[type];
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
    .td-title{
        width: 25%;
    }
</style>
