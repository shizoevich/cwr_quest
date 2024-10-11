<template>
    <div>

        <div class="form-group" id="fax-to">
            <label class="control-label col-sm-4 col-md-3">To</label>
            <div class="col-sm-8 col-md-9">
                <select class="form-control" v-model="selected_fax" :disabled="sending">
                    <option v-for="item in document_default_faxes" :value="item.fax">{{item.title}}</option>
                    <option value="-1">Enter another fax number</option>
                </select>
            </div>
        </div>

        <div v-if="selected_fax && selected_fax != -1">
            <div class="form-group">
                <label class="control-label col-sm-4 col-md-3">Fax: </label>
                <div class="col-sm-8 col-md-9" style="padding-top:7px;">
                    {{documentFax}}
                </div>
            </div>
        </div>
        <div v-else-if="selected_fax">
            <div class="form-group">
                <label class="control-label col-sm-4 col-md-3" :class="{'error': documentFax.length !== 10}" for="document-fax">Fax</label>
                <div class="col-sm-8 col-md-9">
                    <the-mask id="document-fax" name="fax"
                              ref="fax"
                              type="tel"
                              :class="{'input': true, 'error': documentFax.length !== 10 }"
                              @keydown.enter.prevent
                              class="form-control" v-validate="'numeric|digits:10'"
                              mask="+1 (###) ###-####"
                              raw="true"
                              v-model="documentFax" :disabled="sending"></the-mask>
                    <span v-show="documentFax.length !== 10"
                          class="help error">Fax must have 10 digits</span>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button type="button" class="btn btn-primary btn-lg document-send-btn"
                    @click.prevent="sendDocument()"
                    :disabled="!documentFax"
                    v-if="!sending">
                Send
            </button>
            <pageloader add-classes="save-loader" v-show="sending"/>
        </div>
    </div>

</template>

<script>
    export default {
        name: 'send-by-fax',

        props: ['documentToSend'],

        data() {
            return {
                selected_fax: '',
                sending: false,
                documentFax: '',
            };
        },

        created() {
            this.selected_fax = this.documentToSend.default_address.fax ? this.documentToSend.default_address.fax : '';
        },

        methods: {

            validateSendByFaxForm() {
                let hasErrors = false;
                if (!this.selected_fax) {
                    $('#fax-to').addClass('with-errors');
                    hasErrors = true;
                }

                if (this.selected_fax == -1) {
                    if (this.documentFax.length < 10) {
                        hasErrors = true;
                    }
                }

                return !hasErrors;
            },

            sendDocument() {
                if (!this.validateSendByFaxForm()) {
                    return false;
                }
                this.sending = true;
                this.$parent.setSendingStatus(true);
                this.$store.dispatch('sendDocumentByFax', {
                    patient_documents_id: this.documentToSend.document_id,
                    recipient: `+1${this.documentFax}`,
                    document_model: this.documentToSend.document_model,
                    method: 'fax',
                }).then((response) => {
                    if (response.status === 403) {
                        window.alert(response.data);
                    } else if (response.status !== 200) {
                        window.alert('Fax could not have been sent due to connection problems. Please try again later.')
                    }
                    this.$parent.refreshData((function() {
                        this.$parent.closeSendDialog()
                    }).bind(this));

                }).catch((error) => {
                    if (error.response.status === 403) {
                        window.alert(error.response.data);
                    } else {
                        window.alert('Fax could not have been sent due to connection problems. Please try again later.');
                    }
                    console.log(error);
                    this.$parent.closeSendDialog();
                });
            },

        },

        computed: {
            document_default_faxes() {
                return this.$store.state.document_default_faxes;
            }
        },

        watch: {
            selected_fax() {
                if (this.selected_fax) {
                    $('#fax-to').removeClass('with-errors');
                }
                if (this.selected_fax == -1) {
                    this.documentFax = "";
                } else {
                    for (let i in this.document_default_faxes) {
                        if (this.document_default_faxes[i]['fax'] === this.selected_fax) {
                            this.documentFax = this.document_default_faxes[i]['fax'];
                        }
                    }

                }
                console.log(this.documentFax);
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