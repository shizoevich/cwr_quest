<template>
    <el-dialog
        title="Patient email in blacklist"
        :visible.sync="dialogVisible"
        :modal-append-to-body="false"
        class="unsub-email-dialog bootstrap-modal"
        @close="close"
    >
        <div class="unsub-email-dialog__body">
            <p>This patient has previously blocked emails from our system. When this happens, the email address is added to the blacklist by our email service provider to ensure no further emails will be sent out to this address. Therefore, unless we remove them from our blacklist the system will not be able to send any type of communications to this particular email address.</p>
            <h4>Options to resolve this problem:</h4>
            <p>1. Patient can provide a new email address</p>
            <p>Option one is the easiest and requires a different email provided by a patient and adding this email to patient chart by administrator or Therapist.</p>
            <p>2. The email can be removed from our blacklist</p>
            
            <template v-if="isAdmin">
                <p>
                    Option number two is a bit more complex. Before any unblocking action is taken, communicate with the patient and ensure they have followed the instructions to remove our address from their spam sender's list. Unblocking should only be performed once the patient has confirmed that they've taken these steps. First, you must contact the patient and explain why they have been added to the list and what the process of removal from blacklist entails. Make sure to inform the patient that we can unblock their email only once. If they block us again, they will permanently lose access to vital telehealth session links and other important communications. If they block us again, they may be required to provide a different email address for further communications from Change Within Reach. Assist them if necessary and provide the instructions for most popular email service providers. 
                    <button class="unsub-email-dialog__view-more" @click="showInstructions = !showInstructions">CLICK HERE TO VIEW INSTRUCTIONS</button>
                </p>
                
                <transition name="fade">
                    <div v-if="showInstructions">
                        <p>This process might differ depending on the email service provider, so the patient might need to check the specific instructions for their provider if they use something else. After, ask them to send an email to the following address <a href="mailto:no-reply@changewithinreach.care">no-reply@changewithinreach.care</a> stating that they have taken these steps (any wording is fine) and in the subject ask them to put: "Blacklist Removal Request".</p>
                        <ul style="padding-left: 20px;">
                            <li style="margin-bottom: 10px;">
                                Instructions for Gmail users:
                                <ol>
                                    <li>Open the Spam folder.</li>
                                    <li>Find the email from us and open it.</li>
                                    <li>Click on "Report not spam" button.</li>
                                </ol>
                            </li>
                            <li style="margin-bottom: 10px;">
                                Instructions for Yahoo users:
                                <ol>
                                    <li>Go to the Spam folder.</li>
                                    <li>Open the email from us.</li>
                                    <li>Click "Not Spam" at the top of the page.</li>
                                </ol>
                            </li>
                            <li style="margin-bottom: 10px;">
                                Instructions for Outlook users:
                                <ol>
                                    <li>Go to the Junk Email folder.</li>
                                    <li>Right-click the email from us and select "Not junk".</li>
                                </ol>
                            </li>
                        </ul>
                    </div>
                </transition>
            </template>
            <template v-else>
                <p>Please contact one of the secretaries for further instructions.</p>
            </template>
        </div>

        <div v-if="isAdmin" slot="footer" class="unsub-email-dialog__footer">
            <div class="form-group checkbox-form-group">
                <label class="control-label">
                    <input type="checkbox" v-model="confirmCommunication">
                    I have communicated with the patient and conveyed information stated above.
                </label>
            </div>
            <div class="form-group checkbox-form-group">
                <label class="control-label">
                    <input type="checkbox" v-model="confirmSteps">
                    The patient has confirmed he have taken the necessary steps to unblock our emails and sent email to CWR requesting removal from Email Blacklist.
                </label>
            </div>
            <div class="form-group checkbox-form-group">
                <label class="control-label">
                    <input type="checkbox" v-model="confirmConsequences">
                    The patient understands the consequences of blocking our emails again.
                </label>
            </div>

            <div class="d-flex justify-content-center">
                <el-button
                    type="success"
                    :disabled="!confirmCommunication || !confirmSteps || !confirmConsequences"
                    @click="restoreEmail"
                >
                    Remove Email from Blacklist
                </el-button>
            </div>
        </div>
    </el-dialog>
</template>
  
<script>
import { Notification } from 'element-ui';

export default {
    props: {
        patientId: {
            type: [String, Number]
        },
        email: {
            type: String
        },
        isAdmin: {
            type: Boolean,
        },
        show: {
            type: Boolean
        },
        close: {
            type: Function
        }
    },
    data() {
        return {
            dialogVisible: false,
            showInstructions: false,
            confirmCommunication: false,
            confirmSteps: false,
            confirmConsequences: false,
        }
    },
    watch: {
        show() {
            this.dialogVisible = this.show;
        }
    },
    mounted() {
        this.dialogVisible = this.show;
    },
    methods: {
        restoreEmail() {
            this.$store.dispatch('removeEmailFromRejectList', { patientId: this.patientId, email: this.email })
                .then(() => {
                    this.$emit('emailRemovedFromRejectList');
                    
                    Notification.success({
                        title: "Success",
                        message: `Email ${this.email} was successfully removed from the blacklist`,
                        type: "success",
                    });
                })
                .catch(() => {
                    Notification.error({
                        title: "Error",
                        message: `An error occured. Email ${this.email} wasn't removed from the blacklist`,
                        type: "error",
                    });
                })
                .finally(() => {
                    this.close();
                });
        },
    },
};
</script>

<style lang="scss" scoped>
.unsub-email-dialog {
    p:last-child {
        margin-bottom: 0;
    }

    .checkbox-form-group {
        width: 100%;
        max-width: 530px;
        
        label {
            font-weight: normal;
        }
    }
}

.unsub-email-dialog__body {
    word-break: break-word;
}

.unsub-email-dialog__footer {
    text-align: left;
}

.unsub-email-dialog__view-more {
    border: none;
    background: transparent;

    color: #337ab7;
    text-decoration: none;

    &:hover {
        color: #22527b;
        text-decoration: underline;
    }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

</style>

<style lang="scss">
.unsub-email-dialog {
    .el-dialog__header {
        display: block !important;
        visibility: visible !important;
    }

    .el-dialog__body {
        padding: 20px !important;
    }

    .el-dialog__footer {
        padding-top: 20px;
        border-top: 1px solid #e5e5e5;
    }
}
</style>
  