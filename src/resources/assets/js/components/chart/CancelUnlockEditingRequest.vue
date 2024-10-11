<template>
    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         id="cancel-unlock-editing-request-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Progress Note Unlock Request</h5>
                </div>
                <div class="modal-body">
                    <p>The progress note unlock request will be canceled. Do you not want to edit the progress note anymore? Please specify the reason why.</p>
                    <div id="cancel-unlock-editing-request-reason-group">
                        <label for="cancel-unlock-editing-request-reason-group">Reason</label>
                        <textarea name="reason" class="form-control no-resize" rows="5"
                                  v-model="reason" maxlength="255" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-red validation-error-msg">{{ validation_message }}</span>
                    <div class="inline-block">
                      <el-button
                          type="primary"
                          :loading="loading"
                          :disabled="loading"
                          class="btn btn-primary"
                          @click.prevent="submit()">
                        Confirm
                      </el-button>
                        <button type="button" class="btn btn-default" @click.prevent="closeModal()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                reason: '',
                validation_message: '',
                loading: false,
            };
        },

        methods: {
            submit() {
                this.reason = this.reason.trim();
                if(!this.reason) {
                    this.validation_message = this.$store.state.validation_messages.required;
                    $('#cancel-unlock-editing-request-reason-group').addClass('with-errors');
                    return false;
                }
                this.clearValidation();

                this.loading = true

                this.$store.dispatch('cancelUnlockEditingRequest', {reason: this.reason})
                    .then(() => {
                        this.$store.state.currentNote.unlock_request = null;
                    })
                    .finally(() => {
                        this.loading = false
                        this.closeModal()
                    });
            },

            clearValidation() {
                $('#cancel-unlock-editing-request-reason-group').removeClass('with-errors');
                this.validation_message = '';
            },

            closeModal() {
                this.reason = '';
                this.clearValidation();
                $('#cancel-unlock-editing-request-modal').css('display', 'none');
            }
        },
    }
</script>