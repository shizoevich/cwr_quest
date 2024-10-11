<template>
    <div class="container">
        <div class="row">
            <div class="vue-wrapper">

                <ul class="nav nav-tabs">
                    <li class="active">
                        <a data-toggle="tab" href="#reauthorization-requests-tab">
                            Reauthorization Requests {{sent_reauthorization_requests_count}}/{{reauthorization_requests_count}}
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#discharge-summary-tab">
                            Discharge Summary {{sent_discharge_summary_count}}/{{discharge_summary_count}}
                        </a>
                    </li>
                    <li>
                        <a data-toggle="tab" href="#initial-assessment-tab">
                            Initial Assessment {{sent_initial_assessment_count}}/{{initial_assessment_count}}
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div id="reauthorization-requests-tab" class="tab-pane fade in active">
                        <documents-to-send-base-template :dataset="reauthorization_requests" modal-id="send-reauthorization-requests-modal"/>
                    </div>

                    <div id="discharge-summary-tab" class="tab-pane fade">
                        <documents-to-send-base-template :dataset="discharge_summary" modal-id="send-discharge-summary-modal" />
                    </div>

                    <div id="initial-assessment-tab" class="tab-pane fade">
                        <documents-to-send-base-template :dataset="initial_assessment" modal-id="send-initial-assessment-modal" />
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    export default {

        data() {
            return {};
        },

        mounted() {
            this.$store.dispatch('getProviderList');
            this.$store.dispatch('getDocumentDefaultEmails');
            this.$store.dispatch('getDocumentDefaultFaxes');
            let payload = {
                to_send: 1,
                sent: 1,
                approved: 0,
            };
            this.$store.dispatch('getDocumentsToSendReauthorizationRequests', payload);
            this.$store.dispatch('getDocumentsToSendDischargeSummary', payload);
            this.$store.dispatch('getDocumentsToSendInitialAssessment', payload);
        },

        computed: {
            reauthorization_requests() {
                return this.$store.state.documents_to_send_reauthorization_requests;
            },

            discharge_summary() {
                return this.$store.state.documents_to_send_discharge_summary;
            },

            initial_assessment() {
                return this.$store.state.documents_to_send_initial_assessment;
            },

            reauthorization_requests_count() {
                if (this.reauthorization_requests.total) {
                    return this.reauthorization_requests.total;
                }

                return 0;
            },

            sent_reauthorization_requests_count() {
                if (this.reauthorization_requests.sent_count) {
                    return this.reauthorization_requests.sent_count;
                }

                return 0;
            },

            discharge_summary_count() {
                if(this.discharge_summary.total) {
                    return this.discharge_summary.total;
                }

                return 0;
            },

            sent_discharge_summary_count() {
                if(this.discharge_summary.sent_count) {
                    return this.discharge_summary.sent_count;
                }

                return 0;
            },

            initial_assessment_count() {
                if(this.initial_assessment.total) {
                    return this.initial_assessment.total;
                }

                return 0;
            },

            sent_initial_assessment_count() {
                if(this.initial_assessment.sent_count) {
                    return this.initial_assessment.sent_count;
                }

                return 0;
            },

        },

    }
</script>