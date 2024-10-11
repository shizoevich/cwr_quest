<template>
    <div>
        <el-dialog :close-on-click-modal="false" :visible="isVisible" @close="closeDialog" class="custom-dialog">
            <loading-overlay v-if="loading" />

            <div slot="title">
                Call Patient
            </div>

            <div class="break-word">
                <div v-if="!isAdmin" style="margin-bottom: 20px">
                    <p><b>IMPORTANT!</b></p>
                    <p><b>The "Call Patient" button should not be used to conduct therapy sessions. Calls made using this feature are non-billable.</b></p>
                </div>

                <el-form :rules="formRule" :model="formData" ref="callForm">
                    <div class="phone-fields">
                        <div class="phone-fields-left">
                            <el-form-item label="From:" prop="phone_from" class="hide-required-icon phone-fields-input">
                                <el-input type="tel" v-model="formData.phone_from" v-mask="'(###)-###-####'" :masked="true" />
                            </el-form-item>
                            <el-form-item label="To:" prop="phone_to" class="hide-required-icon phone-fields-input">
                                <el-input type="tel" v-model="formData.phone_to" v-mask="'(###)-###-####'" :masked="true" />
                            </el-form-item>
                        </div>
                        
                        <el-form-item>
                            <el-button type="primary" @click="call">Call</el-button>
                        </el-form-item>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-12">
                            <el-checkbox v-model="formData.play_prompt">
                                Prompt me to press 1 before connecting the call
                            </el-checkbox>
                        </div>
                    </div>

                    <hr />

                    <call-logs-table :logs="callLogs" />
                </el-form>
            </div>

            <div slot="footer">
                <el-button type="secondary" @click="closeDialog">Cancel</el-button>
            </div>
        </el-dialog>

        <call-result-dialog :isVisible="callResultDialogIsVisible" :handleClose="closeCallResultDialog" />
    </div>
</template>

<script>
export default {
    props: {
        isVisible: {
            type: Boolean,
            required: true,
        },

        setIsVisible: {
            type: Function,
            required: true,
        },

        isAdmin: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        patient() {
            return this.$store.state.currentPatient;
        },
        provider() {
            return this.$store.state.currentProvider;
        }
    },

    watch: {
        isVisible(value) {
            if (value) {
                this.updateDialogData();
            }
        }
    },

    data() {
        return {
            formData: {
                patient_id: null,
                patient_type: "patient",
                phone_from: null,
                phone_to: null,
                play_prompt: true,
            },
            formRule: {
                phone_from: [
                    { required: true, message: "The from field is required", trigger: "change" },
                    { min: 14, message: "The from field must be at least 14 characters", trigger: "blur" },
                ],
                phone_to: [
                    { required: true, message: "The to field is required", trigger: "change" },
                    { min: 14, message: "The to field must be at least 14 characters", trigger: "blur" },
                ],
            },
            callLogs: null,
            loading: false,
            callResultDialogIsVisible: false,
        }
    },

    methods: {
        call() {
            this.loading = true;

            this.$refs.callForm.validate(valid => {
                if (!valid) {
                    this.loading = false;

                    return;
                }
                this.makeCall();
            });
        },

        makeCall() {
            this.$store.dispatch('patientRingOutCall', this.formData)
                .then(() => {
                    this.openCallResultDialog();
                })
                .catch((e) => {
                    this.$message({
                        type: 'error',
                        message: 'Oops, something went wrong!',
                        duration: 10000,
                    });
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        closeDialog() {
            this.setIsVisible(false);
            this.resetData();
        },

        resetData() {
            this.formData = {
                patient_id: null,
                patient_type: "patient",
                phone_from: null,
                phone_to: null,
                play_prompt: true,
            };

            this.callLogs = null;
            this.$refs.callForm.resetFields();
        },

        updateDialogData() {
            const { cell_phone, home_phone, work_phone } = this.patient;
            const phoneTo = cell_phone || home_phone || work_phone;

            this.formData.patient_id = this.patient.id;
            this.formData.phone_from = this.provider ? this.provider.phone : '';
            this.formData.phone_to = phoneTo;

            if (this.$refs && this.$refs.callForm) {
                this.$nextTick(() => {
                    this.$refs.callForm.clearValidate();
                });
            }

            const payload = {
                patient_id: this.formData.patient_id,
                patient_type: this.formData.patient_type,
            }

            this.$store.dispatch("getRingCentralPatientCallDetails", payload).then(({ data }) => {
                this.callLogs = data.ringcentral_call_logs;
            });
        },

        openCallResultDialog() {
            this.setIsVisible(false);
            this.callLogs = null;
            this.callResultDialogIsVisible = true;
        },

        closeCallResultDialog() {
            this.callResultDialogIsVisible = false;
            this.setIsVisible(true);
        }
    }
}
</script>

<style scoped>
.phone-fields {
    display: flex;
    align-items: end;
}
.phone-fields-left {
display: flex;
flex-grow: 1;
}
.phone-fields-input {
    flex-grow: 1;
    margin-right: 24px;
}
</style>