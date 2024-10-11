<!--Form Name: "Authorization to Release Confidential Information"-->
<template>
    <div class="container">
        <div class="patient-contact-info-container">
            <div class="section section-add-note">
                <form class="form-note form-horizontal patient-contact-info-form"
                      autocomplete="off" id="form-note" novalidate>
                    <h3 class="text-center header-part header-part-first">
                        Authorization to Release Confidential Information to Professionals
                    </h3>
                    <div class="inp-group">
                        <div class="row">
                            <div class="col-xs-7">
                                <label class="control-label col-md-3 pf-label">Name of patient:</label>
                                <div class="col-md-9 pci-form-group">
                                    <input type="text" class="form-control empty-input"
                                           v-model="form_data.name" readonly>
                                </div>
                            </div>
                            <div class="col-xs-5">
                                <label class="control-label col-md-4 col-lg-3 pf-label">Date of birth:</label>
                                <div class="col-md-8 col-lg-9 pci-form-group">
                                    <input type="text" class="form-control empty-input"
                                           v-model="form_data.date_of_birth" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <p class="form-text">
                                    I understand that my records contain information about my therapy sessions and my mental health. I understand that all my records are protected by state and federal laws that require they are kept confidential and require my written consent to disclose.
                                </p>
                                <p class="form-text">
                                    I, <input type="text" class="form-control empty-input charges-input"
                                              v-model="form_data.name" readonly>
                                    , hereby authorize Change Within Reach, Inc. staff to exchange information with
                                    <input id="hereby_information_with" type="text" class="form-control empty-input xs-6-width" v-model="form_data.hereby_information_with" autocomplete="new-password" required>
                                    <span class="form-text">
                                        for the sole purpose of care coordination and provision of the highest quality of care.
                                    </span>

                                </p>
                                <p class="form-text">
                                    I understand that I have the right to revoke this release at any time.
                                </p>
                                <p class="form-text">
                                    I have been informed and understand this authorization to release records and information, the nature of listed content that I am willing to release, and the implications of their release. This request is voluntary.
                                </p>
                                <p class="form-text">
                                    I witnessed that the person understood the content of this authorization and freely gave his or her consent, but was physically unable to provide a signature.
                                </p>
                            </div>
                        </div>
                    </div>  <!--/.inp-group-->

                    <!--signature-->
                    <h3 class="text-center header-part">
                        ELECTRONIC SIGNATURE
                    </h3>
                    <div class="inp-group">
                        <div class="row pci-row">
                            <div class="col-xs-12" data-signature="patient-signature">
                                <div class="signature-title" style="margin-top:0;">
                                    <div class="row">
                                        <div class="col-xs-10">
                                            <h4 id="signature-title">Signature of Patient</h4>
                                        </div>
                                        <div class="col-xs-2">
                                            <button type="button" class="btn btn-danger pull-right" @click.prevent="clearSignature('patient-signature')">Clear</button>
                                        </div>
                                    </div>

                                </div>
                                <div id="patient-signature"></div>
                                <hr class="signature-line">
                            </div>
                        </div>
                        <!--<div class="empty-space-50"></div>-->
                        <div class="row" style="margin-bottom:20px; margin-top:75px;">
                            <div class="col-xs-8">
                                <label class="control-label col-md-6 col-lg-5 pf-label">Name of parent/guardian/representative:</label>
                                <div class="col-md-6 col-lg-7 pci-form-group">
                                    <input id="guardian_name" type="text" class="form-control empty-input"
                                           autocomplete="new-password" v-model="form_data.guardian_name">
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <label class="control-label col-md-4 pf-label">Relationship:</label>
                                <div class="col-md-8 pci-form-group">
                                    <input id="relationship" type="text" class="form-control empty-input"
                                           autocomplete="new-password" v-model="form_data.relationship">
                                </div>
                            </div>
                        </div>
                        <div class="row pci-row">
                            <div class="col-xs-12" style="margin-bottom:50px;" data-signature="patient-signature18">
                                <div class="signature-title" style="margin-top:0;">
                                    <div class="row">
                                        <div class="col-xs-10">
                                            <h4 id="signature18-title">Signature of parent/guardian/representative</h4>
                                        </div>
                                        <div class="col-xs-2">
                                            <button type="button" class="btn btn-danger pull-right" @click.prevent="clearSignature('patient-signature18')">Clear</button>
                                        </div>
                                    </div>

                                </div>
                                <div id="patient-signature18"></div>
                                <hr class="signature-line">
                            </div>
                        </div>
                    </div>

                    <div class="form-note-button-block text-right">
                        <div class="row form-note-row">
                            <span class="text-red validation-error-msg">{{ validation_messages.current }}</span>
                            <button type="submit" class="btn btn-primary"
                                    @click.prevent="validateForm" v-if="!statuses.saving">Save
                            </button>
                            <pageloader add-classes="save-loader" v-show="statuses.saving"></pageloader>
                        </div>
                    </div>

                </form>
            </div>
        </div>


        <div class="modal modal-vertical-center fade" id="enter-password-modal" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">
                            Please hand the tablet to staff member.
                        </h4>
                    </div>
                    <div class="modal-body">
                        <label for="password" class="control-label required">
                            Enter PIN
                        </label>
                        <input type="password" id="password" maxlength="255" class="form-control" required v-model="doctor_password"
                               @keyup.enter="saveForm" autofocus>
                    </div>
                    <div class="modal-footer">
                        <span class="text-red validation-error-msg">{{ validation_messages.current }}</span>
                        <button type="button" class="btn btn-primary" @click.prevent="saveForm" v-if="!statuses.saving">Save</button>
                        <pageloader add-classes="save-loader" v-show="statuses.saving"></pageloader>
                        <button type="button" class="btn btn-default" :disabled="statuses.saving" @click="closeConfirmDialog">Close</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</template>

<script>
    export default{
        data(){
            return {

                form_data: {
                    name: '',
                    date_of_birth: '',
                    hereby_information_with: '',
                    signature: '',
                    signature18: '',
                    relationship: '',
                    guardian_name: '',
                    years_old: ''
                },
                doctor_password: '',
                signature_is_empty: true,
                signature18_is_empty: true,
                statuses: {
                    saving: false
                },
                validation_messages: {
                    current: '',
                    required: "Please make sure you have filled all the required fields.",
                    incorrect_password: "You have entered incorrect PIN code.",
                    try_again: "Error! Please try again."
                },

                required_fields: [
                    'hereby_information_with'
                ]
            }
        },

        computed: {
            patient(){
                return this.$store.state.currentPatient;
            }
        },

        methods: {

            validateForm() {
                let has_errors = false;

                if(this.form_data.years_old >= 15) {
                    if(this.signature_is_empty) {
                        has_errors = true;
                        $('#signature-title').addClass('label-error');
                        $('#patient-signature').addClass('input-error');
                        $('div[data-signature="patient-signature"] hr.signature-line').addClass('signature-line-error');
                    }
                } else {
                    if(this.signature18_is_empty) {
                        has_errors = true;
                        $('#signature18-title').addClass('label-error');
                        $('#patient-signature18').addClass('input-error');
                        $('div[data-signature="patient-signature18"] hr.signature-line').addClass('signature-line-error');
                    }
                    this.required_fields.push('relationship');
                    this.required_fields.push('guardian_name');
                }

                for(let i = 0; i < this.required_fields.length; i++) {
                    let val = this.form_data[this.required_fields[i]].trim();
                    if(val === '') {
                        $('#' + this.required_fields[i]).addClass('input-error').parents('div').prev('label').addClass('label-error');
                        has_errors = true;
                    }
                }

                if(has_errors) {
                    this.validation_messages.current = this.validation_messages.required;
                } else {
                    this.validation_messages.current = '';
                    this.showModal('enter-password-modal')
                }
            },

            fetchData () {
                let id = this.$route.params.id;
                this.$store.dispatch('getPatient', {id: id}).then(error => {
                    if (error) {
                        if (error.status === 403 || error.status === 404) {
                            this.$router.push({ path: '/forms/404'});
                        }
                    }
                    let first_name = this.patient.first_name;
                    let last_name = this.patient.last_name;
                    this.form_data.name = first_name + " " + last_name;
                    this.form_data.date_of_birth = this.patient.date_of_birth;
                    this.form_data.patient_id = this.patient.id;
                    let diff = new Date(Date.now() - new Date(this.form_data.date_of_birth).getTime());
                    this.form_data.years_old = Math.abs(diff.getUTCFullYear() - 1970);
                });
            },

            clearSignature(name) {
                $('#'+name).jSignature('clear');
                if(name === 'patient-signature') {
                    this.signature_is_empty = true;
                } else if(name === 'patient-signature18') {
                    this.signature18_is_empty = true;
                }

            },

            showModal(name) {
                $('#' + name).modal('show');
            },

            closeConfirmDialog() {
                $('#enter-password-modal').modal('hide');
                this.doctor_password = '';
                this.validation_messages.current = '';
            },

            saveForm(){
                this.validation_messages.current = '';
                this.doctor_password = this.doctor_password.trim();
                if(this.doctor_password === '') {
                    this.validation_messages.current = this.validation_messages.required;
                    return false;
                }
                this.statuses.saving = true;
                this.$store.dispatch('isDoctorPasswordValid', {password: this.doctor_password}).then(response => {
                    if(response.valid === true) {
                        if(!this.signature_is_empty) {
                            let datapair = $('#patient-signature').jSignature("getData", "image");
                            this.form_data.signature = "data:" + datapair[0] + "," + datapair[1];
                        }
                        if(!this.signature18_is_empty) {
                            let datapair1 = $('#patient-signature18').jSignature("getData", "image");
                            this.form_data.signature18 = "data:" + datapair1[0] + "," + datapair1[1];
                        }

                        this.$store.dispatch('storeSecondForm', this.form_data).then(response => {
                            this.statuses.saving = false;
                            if(response.status === 200 || response.status === 201) {
                                console.log(response.data);
                                $('#enter-password-modal').modal('hide');
                                this.$router.push('/forms/patient-' + this.patient.patient_id);
                            } else {
                                this.validation_messages.current = this.validation_messages.try_again;
                            }
                        });
                    } else {
                        this.statuses.saving = false;
                        this.validation_messages.current = this.validation_messages.incorrect_password;
                    }
                });
            }


        },

        watch: {

            patient(){
                if (this.patient !== null) {
                    this.first_name = this.patient.first_name;
                    this.last_name = this.patient.last_name;
                    this.id = this.patient.id;
                }
            },

            'form_data.hereby_information_with': function() {
                if(this.form_data.hereby_information_with.trim() !== '') {
                    $('#hereby_information_with').removeClass('input-error').parents('div').prev('label').removeClass('label-error');
                }
            },

            'form_data.guardian_name': function() {
                if(this.form_data.guardian_name.trim() !== '') {
                    $('#guardian_name').removeClass('input-error').parents('div').prev('label').removeClass('label-error');
                }
            },

            'form_data.relationship': function() {
                if(this.form_data.relationship.trim() !== '') {
                    $('#relationship').removeClass('input-error').parents('div').prev('label').removeClass('label-error');
                }
            }
        },

        mounted() {
            this.fetchData();
            window.setTimeout(() => {
                var tmp = this;
                $('#patient-signature').jSignature().bind('change', function (e) {
                    tmp.signature_is_empty = false;
                    $('#signature-title').removeClass('label-error');
                    $('#patient-signature').removeClass('input-error');
                    $('div[data-signature="patient-signature"] hr.signature-line').removeClass('signature-line-error');
                });
                $('#patient-signature18').jSignature().bind('change', function (e) {
                    tmp.signature18_is_empty = false;
                    $('#signature18-title').removeClass('label-error');
                    $('#patient-signature18').removeClass('input-error');
                    $('div[data-signature="patient-signature18"] hr.signature-line').removeClass('signature-line-error');
                });
                $('#enter-password-modal').bind('shown.bs.modal', function() {
                    $('#password').focus();
                });
            }, 500);
        }
    }
</script>

<style scoped>
    p.form-text {
        margin-top: 11px;
    }

    span.form-text {
        display:inline-block;
        margin-top: 6px;
    }

    .xs-6-width {
        max-width: 700px;
        margin-top: 6px;
    }
    .patient-contact-info-form label.control-label {
        font-weight:normal;
    }

    .save-loader {
        max-width: 36px;
        max-height:36px;
        margin-right: 15px;
    }

    .validation-error-msg {
        padding-right: 20px;
    }

    .empty-space-50 {
        height: 50px;
    }

    #patient-signature,
    #patient-signature18 {
        margin-top: 10px;
    }


</style>