<template>
    <div>
        <div class="form-group" id="email-to">
            <label class="control-label col-sm-3">To</label>
            <div class="col-sm-9">
                <select class="form-control" v-model="selected_email" :disabled="sending">
                    <option v-for="item in document_default_emails" :value="item.email">{{item.title}}</option>
                    <option value="-1">Enter another email address</option>
                </select>
            </div>
        </div>

        <div v-if="selected_email && selected_email != -1">
            <div class="form-group">
                <label class="control-label col-sm-3">Email: </label>
                <div class="col-sm-9" style="padding-top:7px;">
                    {{document_email}}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3">Password: </label>
                <div class="col-sm-9" style="padding-top:7px;">
                    {{document_password}}
                </div>
            </div>
        </div>

        <div v-else-if="selected_email">
            <div class="form-group" id="document-email">
                <label class="control-label col-sm-3">Email</label>
                <div class="col-sm-9">
                    <input type="email" class="form-control" v-model="document_email" :disabled="sending">
                </div>
            </div>
            <div class="form-group" id="document-password">
                <label class="control-label col-sm-3">Password</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" v-model="document_password" :disabled="sending">
                </div>
            </div>
        </div>
        <div class="text-center">
            <button type="button" class="btn btn-primary btn-lg document-send-btn"
                    @click.prevent="sendDocument()"
                    :disabled="!document_email || !document_password"
                    v-if="!sending">
                Send
            </button>
            <pageloader add-classes="save-loader" v-show="sending" />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'send-by-email',

        props: ['documentToSend'],

        data() {
            return {
                selected_email: '',
                document_email: '',
                document_password: '',
                sending: false,
            };
        },

        methods: {

            validateSendByEmailForm() {
                let hasErrors = false;
                if(!this.selected_email) {
                    $('#email-to').addClass('with-errors');
                    hasErrors = true;
                }

                if(this.selected_email == -1) {
                    this.document_email = this.document_email.trim();
                    if(!this.document_email.length) {
                        $('#document-email').addClass('with-errors');
                        hasErrors = true;
                    }

                    this.document_password = this.document_password.trim();
                    if(!this.document_password.length) {
                        $('#document-password').addClass('with-errors');
                        hasErrors = true;
                    }
                }

                return !hasErrors;
            },

            sendDocument() {
                if (!this.validateSendByEmailForm()) {
                    return false;
                }
                this.sending = true;
                this.$parent.setSendingStatus(true);
                this.$store.dispatch('sendDocumentByEmail', {
                    patient_documents_id: this.documentToSend.document_id,
                    recipient: this.document_email,
                    shared_link_password: this.document_password,
                    document_model: this.documentToSend.document_model,
                    method: 'email',
                }).then((response) => {
                        if (response.status !== 200) {
                            window.alert('Email could not have been sent due to connection problems. Please try again later.')
                        }
                        this.$parent.refreshData((function() {
                            this.$parent.closeSendDialog()
                        }).bind(this));
                    })
                    .catch((error) => {
                        this.hideEmailModal();
                        this.sending = false;
                        window.alert('Email could not have been sent due to connection problems. Please try again later.');
                        console.log(error);
                    });
            },

        },

        computed: {
            document_default_emails() {
                return this.$store.state.document_default_emails;
            },
        },

        created() {
            if(this.documentToSend.default_address && this.documentToSend.default_address.email) {
                for(let i in this.document_default_emails) {
                    if(this.document_default_emails[i]['email'] === this.documentToSend.default_address.email) {
                        this.selected_email = this.documentToSend.default_address.email;
                        console.log(this.selected_email);
                    }
                }
            }
        },

        watch: {

            selected_email() {
                if (this.selected_email) {
                    $('#email-to').removeClass('with-errors');
                }
                if (this.selected_email == -1) {
                    this.document_email = "";
                    this.document_password = "";
                } else {
                    $('#document-email').removeClass('with-errors');
                    $('#document-password').removeClass('with-errors');
                    for (let i in this.document_default_emails) {
                        if (this.document_default_emails[i]['email'] === this.selected_email) {
                            this.document_email = this.document_default_emails[i]['email'];
                            this.document_password = this.document_default_emails[i]['password'];
                        }
                    }

                }
            },

            document_email() {
                if(this.document_email.length) {
                    $('#document-email').removeClass('with-errors');
                }
            },
            document_password() {
                if(this.document_password.length) {
                    $('#document-password').removeClass('with-errors');
                }
            },
        }
    }
</script>

<style scoped>
    .save-loader {
        max-width: 36px;
        max-height: 36px;
    }
</style>