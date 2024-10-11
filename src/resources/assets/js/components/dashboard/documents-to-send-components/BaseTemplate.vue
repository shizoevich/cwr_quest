<template>
    <div>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="page-loader-wrapper text-center" v-if="loading">
                    <pageloader add-classes="page-loader"/>
                </div>
                <div v-else>
                    <div class="filter-block">
                        <label>Therapist</label>
                        <select class="form-control provider-select" v-model="selected_provider_id">
                            <option value="-1">All</option>
                            <option v-for="provider in provider_list" :value="provider.id">{{provider.provider_name}}
                            </option>
                        </select>
                    </div>
                    <div class="filter-block">
                        <label>Date</label>
                        <ElDatePicker class="date-filter" v-model="selected_date" format="MM/dd/yyyy"
                                      value-format="yyyy-MM-dd" :editable="false"/>
                    </div>
                    <div class="filter-block filter-checkbox-block">
                        <label>
                            <input type="checkbox" v-model="filter_to_send">
                            To Send
                        </label>
                        <label>
                            <input type="checkbox" v-model="filter_sent">
                            Sent
                        </label>
                        <label>
                            <input type="checkbox" v-model="filter_approved">
                            Approved
                        </label>
                    </div>

                    <div class="table-responsive" v-for="item in dataset.data">
                        <h3 class="text-center">{{item.date}}</h3>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Therapist Name</th>
                                <th>Document</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="info in item.dataset"
                                :class="{'row-red': (!info.sent && !info.custom_sent), 'row-green': ((info.sent || info.custom_sent) && info.approved)}">
                                <td class="patient-name" :title="info.patient_name">
                                    <a :href="`${'/chart/' + info.patient_id}`"
                                       :title="info.patient_name">{{info.patient_name}}</a>
                                </td>
                                <td class="patient-name" :title="info.provider_name">{{info.provider_name}}</td>
                                <td>
                                    <a href="javascript:void(0)" @click.prevent="downloadDocument(info)"
                                       title="Download Document">
                                        {{info.document_name}}
                                    </a>
                                </td>
                                <td style="width:100px;">
                                    <button class="btn btn-primary" @click.prevent="showSendDialog(info)"
                                            v-if="!info.sent && !info.custom_sent" style="width:100%;">Send
                                    </button>
                                    <button class="btn btn-success" @click.prevent="showApproveSentDocument(info)"
                                            v-else-if="!info.approved" :disabled="approving" style="width:100%;">Approve
                                    </button>
                                    <span v-else-if="info.approved">Approved At {{info.approved}}</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <el-pagination
                            v-if="dataset"
                            background
                            :current-page="dataset['current_page']"
                            layout="prev, pager, next"
                            :page-count="dataset.last_page"
                            @current-change="changePage">
                    </el-pagination>
                </div>
            </div>
        </div>


        <!--Modals-->
        <div class="modal modal-vertical-center fade base-template-send-document" data-backdrop="static"
             data-keyboard="false"
             :id="modalId" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Send document</h4>
                    </div>
                    <div class="modal-body">
                        <div class="send-document-container">
                            <div class="send-document-col sent-document-col">
                                <button type="button" class="btn btn-success btn-lg"
                                        :disabled="sending"
                                        v-if="!marking_document_as_sent"
                                        @click.prevent="markDocumentAsSent()">
                                    Mark as Sent
                                </button>
                                <pageloader add-classes="save-loader" v-show="marking_document_as_sent"/>
                            </div>
                            <div class="send-document-col" style="padding-left: 15px;">
                                <form class="form-horizontal" @submit.prevent="" style="width:100%;">
                                    <div class="form-group">
                                        <label class="control-label col-sm-4 col-md-3">Send By</label>
                                        <div class="col-sm-8 col-md-9">
                                            <select class="form-control" v-model="send_method"
                                                    :disabled="sending || already_sent">
                                                <option value="1">Email</option>
                                                <option value="2">Fax</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="button" class="btn btn-primary btn-lg document-send-btn"
                                                @click.prevent=""
                                                v-if="!send_method" disabled>
                                            Send
                                        </button>
                                    </div>

                                    <send-by-email v-if="send_method == 1"
                                                   :document-to-send="document_to_send"/>
                                    <send-by-fax v-else-if="send_method == 2"
                                                 :document-to-send="document_to_send"/>
                                </form>


                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" @click.prevent="closeSendDialog()" :disabled="sending">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!---------------------------------->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="authorization-modal" role="dialog" v-if="modalId === 'send-reauthorization-requests-modal'">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Please enter Authorization No.</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group" id="authorization-no">
                            <label>Authorization No.</label>
                            <input type="text" class="form-control" v-model="authorization_no">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" @click.prevent="validateReauthorizationForm()"
                                v-if="!approving">Submit
                        </button>
                        <pageloader add-classes="save-loader" v-show="approving"/>
                        <button class="btn btn-default" @click.prevent="closeApproveSentDocument()"
                                :disabled="approving">Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!---------------------------------->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             :id="`${modalId + '-confirm-approve-modal'}`" role="dialog"
             v-if="modalId !== 'send-reauthorization-requests-modal'">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <p>Are you sure you want to approve this document?</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" @click.prevent="approveSentDocument(document_to_approve)"
                                v-if="!approving">Yes
                        </button>
                        <pageloader add-classes="save-loader" v-show="approving"/>
                        <button class="btn btn-default" @click.prevent="closeConfirmApprove()" :disabled="approving">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SendByEmail from './SendByEmail.vue';
    import SendByFax from './SendByFax.vue';

    export default {
        props: ['dataset', 'modalId'],

        components: {
            SendByEmail,
            SendByFax,
        },

        data() {
            return {
                loading: false,
                send_method: null,
                selected_provider_id: -1,
                selected_date: '',
                document_to_send: null,
                document_to_approve: null,
                sending: false,
                marking_document_as_sent: false,
                approving: false,
                already_sent: false,
                authorization_no: '',

                filter_to_send: true,
                filter_sent: true,
                filter_approved: false,
                current_page: 1,
            };
        },

        methods: {
            changePage(page) {
                this.refreshData(function () {
                }, page);
            },

            setSendingStatus(status) {
                this.sending = status;
            },

            closeSendDialog() {
                $('#' + this.modalId).modal('hide');
                this.document_to_send = null;
                this.send_method = null;
                this.sending = false;
                this.already_sent = false;
            },
            showSendDialog(document) {
                this.document_to_send = document;
                $('#' + this.modalId).modal('show');
            },

            downloadDocument(document) {
                if (document.document_model === 'PatientElectronicDocument') {
                    window.open('/patient/electronic-document/' + document.document_id + '/download', '_blank');
                } else {
                    window.open('/patient/download-document/' + document.aws_document_name, '_blank');
                }
            },

            markDocumentAsSent() {
                this.sending = true;
                this.marking_document_as_sent = true;
                this.send_method = null;
                let payload = {
                    is_sent: true,
                    document_id: this.document_to_send.document_id,
                    document_model: this.document_to_send.document_model,
                };
                this.$store.dispatch('markDocumentAsSent', payload).then(response => {
                    if (response.status === 201) {
                        this.refreshData((function () {
                            this.sending = false;
                            this.marking_document_as_sent = false;
                            this.closeSendDialog();
                        }).bind(this));
                    }
                });
            },

            refreshData(callback, page) {
                this.loading = true;
                let payload = {
                    to_send: this.filter_to_send ? 1 : 0,
                    sent: this.filter_sent ? 1 : 0,
                    approved: this.filter_approved ? 1 : 0,
                };
                if (this.selected_date) {
                    payload.date = this.selected_date;
                }
                if (this.selected_provider_id && this.selected_provider_id > 0) {
                    payload.provider_id = this.selected_provider_id;
                }
                if (page && page > 0) {
                    payload.page = page;
                }
                switch (this.modalId) {
                    case 'send-reauthorization-requests-modal':
                        this.$store.dispatch('getDocumentsToSendReauthorizationRequests', payload).then(() => {
                            callback();
                            this.loading = false;
                        });
                        break;
                    case 'send-discharge-summary-modal':
                        this.$store.dispatch('getDocumentsToSendDischargeSummary', payload).then(() => {
                            callback();
                            this.loading = false;
                        });
                        break;
                    case 'send-initial-assessment-modal':
                        this.$store.dispatch('getDocumentsToSendInitialAssessment', payload).then(() => {
                            callback();
                            this.loading = false;
                        });
                        break;
                    default:
                        callback();
                        this.loading = false;
                }
            },

            showApproveSentDocument(document) {
                if (this.modalId === 'send-reauthorization-requests-modal') {
                    this.document_to_send = document;
                    $('#authorization-modal').modal('show');
                } else {
                    this.document_to_approve = document;
                    $('#' + this.modalId + '-confirm-approve-modal').modal('show');
                }
            },

            closeConfirmApprove() {
                this.document_to_approve = null;
                $('#' + this.modalId + '-confirm-approve-modal').modal('hide');
            },

            closeApproveSentDocument() {
                $('#authorization-modal').modal('hide');
                $('#authorization-no').removeClass('with-errors');
                this.document_to_send = null;
                this.authorization_no = '';
            },

            approveSentDocument(document) {
                this.approving = true;
                let payload = {
                    is_approved: true,
                    document_id: document.document_id,
                    document_model: document.document_model,
                };
                if (this.modalId === 'send-reauthorization-requests-modal') {
                    payload.authorization_no = this.authorization_no;
                }
                this.$store.dispatch('approveSentDocument', payload).then(response => {
                    if (response.status === 201) {
                        this.refreshData((function () {
                            this.approving = false;
                            this.closeConfirmApprove();
                            this.closeApproveSentDocument();
                        }).bind(this));
                    } else {
                        this.approving = false;
                    }
                });
            },

            validateReauthorizationForm() {
                if (this.authorization_no.trim()) {
                    this.approveSentDocument(this.document_to_send);
                } else {
                    $('#authorization-no').addClass('with-errors');
                }
            },
        },

        computed: {
            provider_list() {
                return this.$store.state.provider_list;
            }
        },

        watch: {
            filter_to_send() {
                this.refreshData(function () {
                }, 1);
            },
            filter_sent() {
                this.refreshData(function () {
                }, 1);
            },
            filter_approved() {
                this.refreshData(function () {
                }, 1);
            },
            selected_date() {
                this.refreshData(function () {
                }, 1);
            },

            already_sent() {
                if (this.already_sent) {
                    this.send_method = null;
                }
            },

            authorization_no() {
                if (this.authorization_no.trim()) {
                    $('#authorization-no').removeClass('with-errors');
                }
            },

            selected_provider_id() {
                this.refreshData(function () {
                }, 1);
            }
        },
    }
</script>

<style scoped>
    table tbody td {
        vertical-align: middle !important;
    }

    .row-red {
        background: #f8d7dabd !important;
    }

    .row-green {
        background: #d3efc7bd !important;
    }

    .patient-name {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        width: 170px;
    }

    .filter-block {
        display: inline-block;
        margin-right: 20px;
        width: 190px;
    }

    .filter-checkbox-block {
        width: auto;
    }

    .filter-checkbox-block label {
        margin-right: 10px;
    }

    .send-document-container {
        display: flex;
        width: 100%;
        height: 100%;
    }

    .send-document-container .send-document-col {
        width: 50%;
        justify-content: center;
        align-items: center;
        display: inline-flex;
    }

    .send-document-container .send-document-col.sent-document-col {
        flex-direction: column;
        padding-left: 15px;
        padding-right: 20px;
        border-right: 1px solid #e5e5e5;
        position: relative;
        text-align: center;
    }

    .send-document-container .send-document-col.sent-document-col:after {
        content: 'OR';
        color: #b7b7b7;
        position: absolute;
        right: -11px;
        font-size: 16px;
    }

    .save-loader {
        max-width: 36px;
        max-height: 36px;
    }

    .page-loader-wrapper {
        height: 80vh;
    }

    .page-loader-wrapper:before {
        display: inline-block;
        vertical-align: middle;
        content: " ";
        height: 100%;
    }

    .page-loader {
        max-width: 200px;
        max-height: 200px;
    }

</style>