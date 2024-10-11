<template>
    <el-dialog
        title="Invitation to join in an Upheal Telehealth Session"
        top="30vh"
        :visible.sync="showDialog"
        class="video-session bootstrap-modal"
    >
        <div class="video-session-body">
            <p>
                Once you complete your telehealth session, you can close this window and change the status
                of this appointment in EHR.
            </p>
            <p>
                <b>IMPORTANT:</b> Please make sure that you have a microphone and speakers or headset
                connected to your computer prior to starting the Telehealth session.
            </p>
            <div class="text-center">
                <el-button type="success" @click="openVideoSession">CONTINUE</el-button>
            </div>
        </div>
        <div class="video-session-footer">
            <el-button @click="closeDialog">Close</el-button>
        </div>
    </el-dialog>
</template>

<script>
    export default {
        name: 'StartUphealVideoSession',
        props: {
            isShowDialog: {
                type: Boolean,
                default: false,
            },
            conferenceUri: {
                type: String,
                default: ''
            }
        },
        computed: {
            showDialog: {
                get() {
                    return this.isShowDialog;
                },
                set(value) {
                    if (!value) {
                        this.$emit('closeDialog');
                    }
                }
            }
        },
        methods: {
            openVideoSession() {
                window.open(this.conferenceUri,'_blank');
            },
            closeDialog() {
                this.showDialog = false;
            }
        }
    }
</script>

<style lang="scss">
    .video-session {
        .el-dialog {
            width: 95%;
            max-width: 530px;

            &__body {
                padding-bottom: 20px;
            }
        }

        &-body {
            word-break: break-word;

            p {
                margin-bottom: 15px;
            }
        }

        &-footer {
            padding: 10px 0 0;
            display: flex;
            justify-content: flex-end;
        }
    }
</style>