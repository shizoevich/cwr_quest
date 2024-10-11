<template>
    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         id="send-unlock-editing-request-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Progress Note Unlock Request</h5>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason to unlock progress note editing.</p>
                    <div id="send-unlock-editing-request-reason-group">
                        <label for="cancel-reason">Reason</label>
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
                    $('#send-unlock-editing-request-reason-group').addClass('with-errors');
                    return false;
                }
                this.clearValidation();

                this.loading = true

                this.$store.dispatch('sendUnlockEditingRequest', {reason: this.reason})
                    .then((response) => {
                        this.$store.state.currentNote.unlock_request = response.data.data
                    })
                    .finally(() => {
                        this.loading = false
                        this.closeModal()
                    });
            },

            clearValidation() {
                $('#send-unlock-editing-request-reason-group').removeClass('with-errors');
                this.validation_message = '';
            },

            closeModal() {
                this.clearValidation();
                this.reason = '';
                $('#send-unlock-editing-request-modal').css('display', 'none');
            }
        },
    }
</script>