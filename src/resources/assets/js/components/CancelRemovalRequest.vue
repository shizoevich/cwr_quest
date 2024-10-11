<template>
    <section class="inline-block">
        <button class="btn btn-warning btn-xs" data-target="#send-remove-request-modal" data-toggle="modal"
            v-if="showButton">
            Cancel Removal Request
        </button>

        <!--Modals-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             v-if="showButton" id="send-remove-request-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content" v-loading="loading">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Removal Request</h5>
                    </div>
                    <div class="modal-body">
                        <p>The patient removal request will be canceled. Do you not want to remove the patient from your list anymore? Please specify the reason why.</p>
                        <div id="reason-group">
                            <label for="reason">Reason</label>
                            <textarea class="form-control no-resize" id="reason" rows="5" v-model="reason"
                                      maxlength="255" autofocus></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span class="text-red validation-error-msg">{{ validation_message }}</span>
                        <button type="button" class="btn btn-primary" @click.prevent="cancelRemovalRequest()">
                            Submit
                        </button>
                        <button type="button" class="btn btn-default" @click.prevent="closeModal()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    import {Notification} from "element-ui";

    export default {

        props: {
            showButton: {
                type: Boolean,
                required: true
            }
        },

        data() {
            return {
                reason: '',
                validation_message: '',
                loading: false,
            };
        },

        computed: {
            patient() {
                return this.$store.state.currentPatient;
            },

            is_admin() {
                return this.$store.state.isUserAdmin;
            },
        },

        methods: {
            cancelRemovalRequest() {
                let self = this;
                this.reason = this.reason.trim();
                if(!this.reason) {
                    this.validation_message = this.$store.state.validation_messages.required;
                    $('#reason-group').addClass('with-errors');
                    return false;
                }
                this.loading = true;
                this.clearValidation();
                this.$store.dispatch('providerCancelRemovalRequest', {
                    patient_id: this.patient.id,
                    reason: this.reason,
                }).then(response => {
                    this.$emit('patientChangedEvent');
                    if(response.status === 204) {
                        this.closeModal();
                        self.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: self.patient.id});
                        Notification.success({
                          title: 'Success',
                          message: 'Patient removal request has been canceled.',
                          type: 'success'
                        });
                    } else if(response.status === 403) {
                        this.validation_message = response.data.message;
                    } else {
                        this.validation_message = 'You cannot cancel removal request.';
                    }
                }).finally(() => {
                    this.loading = false;
                });
            },

            clearValidation() {
                $('#reason-group').removeClass('with-errors');
                this.validation_message = '';
            },

            closeModal() {
                $('#send-remove-request-modal').modal('hide');
                this.reason = '';
                this.clearValidation();
            }
        },
    }
</script>

<style scoped>
    section {
        font-size: 14px !important;
    }
    section :not(label) {
        font-weight: normal;
    }
</style>