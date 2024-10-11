<template>
    <div>
        <div class="modal fade" id="assessment-form-modal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">


                    <div class="modal-header">
                        <button type="button" id="save-button" class="btn btn-success"
                                @click.prevent="saveAssessmentForm" v-if="statuses && !statuses.loading" :disabled="statuses.saving">Save
                        </button>
                        <!--<h4 class="modal-title" id="">{{assessmentFormTitle}}</h4>-->
                        <button type="button" class="close" aria-label="Close" @click.prevent="confirmCloseModal" :disabled="statuses.loading || statuses.saving">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="text-center page-loader-wrapper" v-if="statuses && statuses.loading">
                            <pageloader add-classes="saving-loader" image-alt="Creating..."></pageloader>
                        </div>

                        <!--<button type="button" id="" class="btn btn-danger"-->
                                <!--@click.prevent="createAssessmentForm">Create-->
                        <!--</button>-->

                        <div id="frame-container" v-html="frame_content"></div>
                        <div  v-if="assessmentFormLink != ''">

                        </div>

                    </div>
                </div><!--/.modal-content-->
            </div>
        </div>
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-closing-form-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Confirmation</h4>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to close the window? All changes will be lost.
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="confirm-closing" class="btn btn-danger"
                                @click.prevent="closeModal">Yes
                        </button>
                        <button type="button" class="btn btn-secondary" @click.prevent="closeConfirmDialog">No
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [],
        data() {
            return {
                statuses: {
                    data_is_changed: false,
                    saving: false,
                    loading: true,
                    noErrors: true
                },
                document_url: false,
                frame_content: ''
            }
        },
        mounted() {
            window.setTimeout(() => {
                let self = this;
                $('#assessment-form-modal').bind('shown.bs.modal', function () {
                    this.frame_content = "";
                    self.statuses.loading = true;
                });
            }, 1000);
        },
        computed: {
            assessmentForm() {
                return this.$store.state.openAssessmentForm;
            },
            assessmentFormTitle() {
                return this.assessmentForm ?  this.assessmentForm.title : (this.assessmentForm && this.assessmentForm.assessment_form_id ? this.$store.state.assessmentFormsTemplates[this.assessmentForm.assessment_form_id].title: '');
            },
            assessmentFormLink() {
                if(this.assessmentForm && this.assessmentForm.full_file_link) {
                    let callbackUrl = window.origin + '/assessment-forms/' + this.assessmentForm.id + '/save';

                    if(this.assessmentForm.is_existing_form) {
                        callbackUrl = window.origin + '/chart/' + this.patient.id;
                    }

                    this.document_url = this.assessmentForm.full_file_link  + '?iframe&callback=' + callbackUrl;
                    console.log(this.document_url);
                    return this.document_url;
                } else {
                    return null;
                }
            },
            patient() {
                return this.$store.state.currentPatient;
            }
        },
        methods: {
            confirmCloseModal() {
                $('#confirm-closing-form-modal').modal('toggle').css('z-index', 2000);
                $('#assessment-form-modal').css('z-index', 1040);
            },

            closeConfirmDialog(){
                $('#confirm-closing-form-modal').modal('toggle');
                $('#assessment-form-modal').css('z-index', 1050);
            },


            closeModal() {
                $('#confirm-closing-form-modal').modal('hide');
                $('#assessment-form-modal').css('z-index', 1050).modal('hide');
                window.setTimeout(() => {
                    this.frame_content = "";
                    this.document_url = false;
                    this.statuses.saving = false;
                }, 250);
                this.$store.commit('setOpenAssessmentFormToModal', null);
            },
            toogleLoader() {
                this.statuses.loading = !this.statuses.loading;
            },
            createAssessmentForm() {
                this.$store.dispatch('createNewPatientAssessmentForm', {
                    assessment_form_id: this.assessmentForm.id,
                    patient_id:this.$store.state.currentPatient.id
                });
            },
            saveAssessmentForm() {
                if(this.assessmentForm.is_existing_form) {
                    this.updateAssessmentForm();
                } else {
                    this.statuses.saving = true;
                    this.$store.dispatch('savePatientAssessmentForm', this.assessmentForm).then(() => {
                        this.closeModal();
                    });
                }
            },

            updateAssessmentForm() {
                this.statuses.saving = true;
                this.closeModal();
            }
        },
        watch: {
            document_url() {
//                this.statuses.loading = false;
                this.frame_content = '<iframe id="doc" src="' + this.document_url + '" style="width: 100%; min-height: 93vh;"/>';

//                window.open(this.document_url)
            },
            frame_content() {
                let self = this;
                $('#doc').ready(function() {
                    window.setTimeout(() => {
                        self.statuses.loading = false;
                    }, 5000);
                });
            }
        }
    }
</script>

<style scoped>

    .page-loader-wrapper {
        height: 93vh;
    }
    .page-loader-wrapper:before {
        display: inline-block;
        vertical-align: middle;
        content: " ";
        height: 100%;
    }

    .saving-loader {
        max-width:150px;
        max-height:150px;
        /*margin-bottom: 39px;*/
    }

    .modal-dialog-note {
        margin: 0 !important;
        width: 100vw;
        height: 100vh;
    }

    .modal-dialog-note .modal-content {
        height: 100%;
        border-radius: 0;
    }

    .modal-header {
        height: 55px;
    }

    #save-button {
        position:absolute;

    }

    button.close {
        padding: 10px 0;
    }
</style>