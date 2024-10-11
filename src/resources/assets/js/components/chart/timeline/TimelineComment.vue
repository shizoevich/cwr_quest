<template>
    <div
        class="timeline-item"
        :class="{ 'only-for-admin': note.only_for_admin == 1 }">
        <div class="btn-group pull-right" v-if="is_admin">
            <button
                type="button"
                class="btn btn-default btn-sm dropdown-toggle btn-d-menu"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false">
                <span class="glyphicon glyphicon-option-horizontal"></span>
            </button>
            <ul class="dropdown-menu pull-right custom-dropdown-menu">
                <li>
                    <a
                        href="javascript:void(0)"
                        @click.prevent="showDeleteCommentModal()"
                        >Delete</a
                    >
                </li>
            </ul>
        </div>

        <span class="time">{{ getFormattedTime(note.created_at) }}</span>

        <h3 class="timeline-header">
            <span class="label-blue">{{ getProviderName(note) }}</span>
            <span style="font-weight: 600" v-html="commentAction"></span>
        </h3>

        <component :is="commentComponent" :comment="note" :getStarIcon="getStarIcon" />
    </div>
</template>

<script>
    import DatetimeFormated from "../../../mixins/datetime-formated";
    import PhoneFormated from "../../../mixins/phone-format";
    import ProviderInfo from "../../../mixins/provider-info";
    import {
        CANCELLATION_COMMENT_TYPE,
        CHANGE_VISIT_FREQUENCY_COMMENT_TYPE,
        CREATION_COMMENT_TYPE,
        INITIAL_SURVEY_COMMENT_TYPE,
        ONBOARDING_COMPLETE_COMMENT_TYPE,
        RESCHEDULE_COMMENT_TYPE,
        SECOND_SURVEY_COMMENT_TYPE,
        START_FILLING_REFERRAL_FORM_COMMENT_TYPE
    } from "../../../settings";
    import StarRegular from "../../../../images/icons/star-regular.svg";
    import StarSolid from "../../../../images/icons/star-solid.svg";
    import InitialSurveyCompleteComment from "./comments/InitialSurveyCompleteComment.vue";
    import SecondSurveyCompleteComment from "./comments/SecondSurveyCompleteComment.vue";
    import DefaultComment from "./comments/DefaultComment.vue";

    export default {
        name: "timeline-comment",

        components: {
            InitialSurveyCompleteComment,
            SecondSurveyCompleteComment,
            DefaultComment,
        },

        mixins: [DatetimeFormated, PhoneFormated, ProviderInfo],

        props: {
            note: Object,
        },

        data() {
            return {};
        },

        computed: {
            is_admin() {
                return this.$store.state.isUserAdmin;
            },

            patient() {
                return this.$store.state.currentPatient;
            },
            commentAction() {
                let text = "";
                let appointmentDate = null;
                let visitReason = null;

                if (this.note.appointment) {
                    appointmentDate = moment
                        .unix(this.note.appointment.time)
                        .format("MM/DD/YYYY h:mm a");

                    visitReason = this.note.comment_metadata ? this.note.comment_metadata.visit_reason : null;
                }

                switch (this.note.comment_type) {
                    case CANCELLATION_COMMENT_TYPE:
                        text = "cancelled appointment";
                        if (appointmentDate) {
                            if(visitReason){
                                text += ` with reason "${visitReason}" <br><span style="line-height: 1.5;">(Appt. date: ${appointmentDate})</span>`
                            } else {
                                text += ` (Appt. date: ${appointmentDate})`;
                            }
                        }
                        break;
                    case RESCHEDULE_COMMENT_TYPE:
                        text = "rescheduled appointment";
                        let newAppointmentDate = null;

                        if (this.note.new_appointment) {
                            newAppointmentDate = moment
                                .unix(this.note.new_appointment.time)
                                .format("MM/DD/YYYY h:mm a");
                        } else if (
                            this.note.comment_metadata &&
                            this.note.comment_metadata.old_time &&
                            this.note.comment_metadata.new_time
                        ) {
                            appointmentDate = moment
                                .unix(this.note.comment_metadata.old_time)
                                .format("MM/DD/YYYY h:mm a");
                            newAppointmentDate = moment
                                .unix(this.note.comment_metadata.new_time)
                                .format("MM/DD/YYYY h:mm a");
                        }

                        if (appointmentDate && newAppointmentDate) {
                            if(visitReason){
                                text += ` with reason "${visitReason}" <br><span style="line-height: 1.5;">(Old appt. date: ${appointmentDate}, new appt. date: ${newAppointmentDate})</span>`
                            } else {
                                text += ` (Old appt. date: ${appointmentDate}, new appt. date: ${newAppointmentDate})`;
                            }
                        }

                        break;
                    case CREATION_COMMENT_TYPE:
                        text = "scheduled appointment";
                        if (appointmentDate) {
                            if(visitReason){
                                text += ` with reason "${visitReason}" <br><span style="line-height: 1.5;">(Appt. date: ${appointmentDate})</span>`
                            } else {
                                text += ` (Appt. date: ${appointmentDate})`;
                            }
                        }
                        break;
                    case INITIAL_SURVEY_COMMENT_TYPE:
                        return "";
                        break;
                    case ONBOARDING_COMPLETE_COMMENT_TYPE:
                        const phone = this.getUsFormat(
                            String(this.note.comment_metadata.phone),
                        );
                        text += `completed onboarding (Phone: ${phone})`;
                        break;
                    case CHANGE_VISIT_FREQUENCY_COMMENT_TYPE:
                        text = "changed patient Frequency of Treatment";
                        const noteMetadata = this.note.comment_metadata;
                        if (noteMetadata) {
                            text += ` (${
                                noteMetadata.old_value
                                    ? `Old value: ${noteMetadata.old_value}; `
                                    : ""
                            }New value: ${noteMetadata.new_value})`;
                        }
                        break;
                    case START_FILLING_REFERRAL_FORM_COMMENT_TYPE:
                        text = `was redirected to an externally hosted site managed by Kaiser to initiate ${this.note.comment_metadata && this.note.comment_metadata.document_to_fill_name} referral form`
                        break;
                    default:
                        text = "commented";
                        break;
                }
                return text;
            },

            commentComponent() {
                switch (this.note.comment_type) {
                    case INITIAL_SURVEY_COMMENT_TYPE:
                        return "initial-survey-complete-comment";
                    case SECOND_SURVEY_COMMENT_TYPE:
                        return "second-survey-complete-comment";
                    default:
                        return "default-comment";
                }
            },
        },

        methods: {
            showDeleteCommentModal() {
                this.$emit("deleteCommentConfirmation", this.note);
            },

            getStarIcon(index, fieldName) {
                return index <= this.note.comment_metadata[fieldName]
                    ? StarSolid
                    : StarRegular;
            },
        },
    };
</script>

<style scoped>
    .btn-d-menu {
        border-radius: 30px;
        width: 30px;
        height: 30px;
        padding: 6px;
        margin: 4px;
    }

    .star {
        height: 18px;
    }
</style>
