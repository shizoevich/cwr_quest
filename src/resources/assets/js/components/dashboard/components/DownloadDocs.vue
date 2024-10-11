<template>
    <div class="document-dialog-wrapper">
        <el-dialog
            title="Download Documents In Zip Archive"
            :visible.sync="showDialog"
            :close-on-click-modal="false"
            v-loading.fullscreen.lock="isLoading"
            class="document-dialog bootstrap-modal"
        >
            <el-form>
                <div class="form-group">
                    <div class="form-group__title">Choose Document Type</div>
                    <div class="form-row">
                        <div v-for="documentType in documentTypes" :key="documentType.type" class="form-col form-col-12">
                            <el-checkbox v-model="checkboxDocumentType[documentType.type]">
                                {{ documentType.label }}
                            </el-checkbox>
                        </div>
                    </div>
                </div>
                <div class="form-footer">
                    <div class="form-footer-control">
                        <el-button type="primary" :disabled="isDownloadButtonDisabled" @click="generateDocuments">Download</el-button>
                        <el-button @click="closeDialog">Cancel</el-button>
                    </div>
                </div>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
import {
    InitialAssessment,
    PatientNote,
    DischargeSummary,
} from "../../../settings/zip-doc-archive-const";

import { Notification } from "element-ui";

export default {
    name: "DownloadDocs",
    props: {
        isShowDialog: {
            type: Boolean,
            default: false,
        },
        isCreated: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            isLoading: false,
            checkboxDocumentType: {},
            initialAssessment: InitialAssessment,
            patientNote: PatientNote,
            dischargeSummary: DischargeSummary,
        };
    },
    computed: {
        showDialog: {
            get() {
                return this.isShowDialog;
            },
            set(value) {
                if (!value) {
                    this.$emit("closeDialog");
                }
            },
        },

        patient() {
            return this.$store.state.currentPatient;
        },

        documentTypes() {
            return [
                { type: this.initialAssessment, label: "Initial Assessments" },
                { type: this.patientNote, label: "Progress Notes" },
                { type: this.dischargeSummary, label: "Discharge Summary" },
            ];
        },

        isDownloadButtonDisabled() {
            return Object.values(this.checkboxDocumentType).every((value) => !value);
        },
    },

    methods: {
        generateDocuments() {
            const data = {
                documentType: this.checkboxDocumentType,
            };

            this.$store.dispatch("generatePatientZipDocuments", {
                patientId: this.patient.id,
                data,
            })
                .then(() => {
                    Notification.success({
                        title: "Success",
                        message: "The process has started, download will begin automatically as soon as archive will be prepared",
                        type: "success",
                    });

                    this.closeDialog();
                })
                .catch(() => {
                    Notification.error({
                        title: "Error",
                        message: 'Something went wrong',
                        type: "error",
                    });
                });
        },

        closeDialog() {
            this.showDialog = false;
        },
    },
};
</script>

<style lang="scss" scoped>
.circle-icon {
    height: 20px;
    width: 20px;
    margin-top: 3px;
}

i.fa {
    border-radius: 100px;
    width: 35px;
    height: 35px;
    font-size: 20px;
    line-height: 35px;
    text-align: center;
}

.document-dialog-wrapper {
    min-width: 200px !important;
    max-width: 800px !important;
}

.error-message {
    color: #f44336;
    font-size: 12px;
    margin-top: 5px;
}

.document-dialog {
    .el-dialog {
        width: 95%;
    }

    .el-form-item {
        margin-bottom: 15px;

        &__label {
            width: 100%;
            line-height: 24px;
            font-size: 13px;
            text-align: left;
        }

        .el-select {
            width: 100%;

            .el-input__suffix {
                i.el-input__icon {
                line-height: 40px;
                }
            }
        }
    }

    .form-group {
        position: relative;
        padding: 0 15px 10px;
        z-index: 15;

        &--diagnosis {
            z-index: 20;
        }

        &__title {
            position: relative;
            font-size: 16px;
            padding: 0 15px;
            margin-bottom: 15px;
            z-index: 5;
            display: inline-block;
            background-color: #ffffff;
        }

        &::before {
            content: "";
            position: absolute;
            top: 13px;
            left: 0;
            right: 0;
            bottom: 0;
            border: 1px solid #ebeef5;
            border-radius: 5px;
            z-index: -5;
        }
    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin: 0 -15px;

        &--table {
            padding-bottom: 15px;
        }
    }

    .form-col {
        width: auto;
        flex: 1 0 auto;
        padding: 0 15px;

        &-4 {
            width: 33.333%;
        }

        &-6 {
            width: 50%;
        }

        &-12 {
            width: 100%;
        }

        @media (max-width: 930px) {
            width: 100%;
        }
    }

    .form-footer {
        padding-top: 15px;

        &-control {
            display: flex;
            justify-content: flex-end;
        }
    }
}
</style>
