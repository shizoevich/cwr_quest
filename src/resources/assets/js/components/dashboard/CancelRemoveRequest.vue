<template>
    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         id="cancel-remove-request-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Decline Removal Request</h5>
                </div>
                <div class="modal-body">
                    <p>You are about to decline removal request. Please confirm.</p>
                    <div id="reason-group">
                        <label for="cancel-reason">Reason</label>
                        <textarea name="reason" class="form-control no-resize" id="cancel-reason" rows="5"
                                  v-model="reason" maxlength="255" autofocus required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="text-red validation-error-msg">{{ validation_message }}</span>
                    <form action="/dashboard/patient-removal-requests/decline" method="post"
                          id="cancel-request-form"
                          class="inline-block">
                        <input type="hidden" name="_method" value="put">
                        <input type="hidden" name="_token" :value="csrf">
                        <input type="hidden" name="request_id" value="">
                        <input type="hidden" name="reason" v-model="reason">
                        <button type="button" class="btn btn-primary" @click.prevent="submitForm()">
                            Confirm
                        </button>
                        <button type="button" class="btn btn-default" @click.prevent="closeModal()">
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

        props: [
            'csrf',
        ],

        mounted() {

        },

        data() {
            return {
                reason: '',
                validation_message: '',
            };
        },

        computed: {

        },

        methods: {
            submitForm() {
                this.reason = this.reason.trim();
                if(!this.reason) {
                    this.validation_message = this.$store.state.validation_messages.required;
                    $('#reason-group').addClass('with-errors');
                    return false;
                }
                this.clearValidation();
                $('#cancel-request-form').submit();
            },

            clearValidation() {
                $('#reason-group').removeClass('with-errors');
                this.validation_message = '';
            },

            closeModal() {
                $('#cancel-remove-request-modal').modal('hide');
                this.reason = '';
                this.clearValidation();
            }
        },
    }
</script>