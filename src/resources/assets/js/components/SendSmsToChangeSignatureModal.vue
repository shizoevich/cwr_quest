<template>
    <div
        class="modal modal-vertical-center fade"
        tabindex="-1"
        role="dialog"
        aria-labelledby="modalLabel"
        aria-hidden="true"
        data-backdrop="static"
        data-keyboard="false"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Send SMS to update signature
                        <button type="button" class="close" aria-label="Close" @click="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </h5>
                </div>
                <div class="modal-body">
                    <el-form ref="patientPhoneForm" :rules="formRule" :model="formData">
                        <el-form-item label="Send to" prop="provider_phone">
                            <el-input
                                type="tel"
                                v-model="formData.provider_phone"
                                v-mask="'(###)-###-####'"
                                :masked="true"
                            />
                        </el-form-item>
                    </el-form>
                </div>
                <div class="modal-footer">
                    <pageloader add-classes="save-loader" v-if="sending" style="max-width: 30px; margin-right: 5px"/>
                    <button type="button" class="btn btn-primary" @click.prevent="sendClick" :disabled="sending">Send</button>
                    <button type="button" class="btn btn-secondary" @click.prevent="closeModal" :disabled="sending">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Notification } from "element-ui";

export default {
    props: {
        phone: {
            type: [Number, String, null],
        },
        userId: {
            type: Number
        }
    },
    data() {
        return {
            sending: false,
            formData: {
                provider_phone: '',
            },
            formRule: {
                provider_phone: [{
                    required: true,
                    len: 14,
                    message: 'The phone number is invalid',
                    trigger: 'blur'
                }],
            },
        }
    },
    mounted() {
        this.initPhone();
    },
    methods: {
        initPhone() {
            this.formData.provider_phone = this.formatPhone(this.phone);
        },
        formatPhone(prPhone) {
            if (!prPhone) {
                return '';
            }

            const phone = prPhone.toString();
            return `(${phone.slice(0, 3)})-${phone.slice(3, 6)}-${phone.slice(6, 10)}`;
        },
        sendClick() {
            this.$refs.patientPhoneForm.validate((valid) => {
                if (!valid) {
                    return;
                }

                this.sending = true;
                this.$store.dispatch('sendSmsToUpdateSignature', {
                    user_id: this.userId,
                    phone: this.formData.provider_phone
                })
                    .then(() => {
                        this.closeModal();
                        Notification.success({
                            title: 'Success',
                            message: 'SMS was successfully sent',
                            type: 'success'
                        });
                    })
                    .catch((err) => {
                        Notification.error({
                            title: 'Error',
                            message: 'Something went wrong',
                            type: 'error'
                        });
                    })
                    .finally(() => {
                        this.sending = false;
                    });
            });
        },
        closeModal() {
            $('#sendSmsToChangeSignatureDialog').modal('hide');
            this.initPhone();
            this.$refs.patientPhoneForm.clearValidate();
        },
    },
}
</script>