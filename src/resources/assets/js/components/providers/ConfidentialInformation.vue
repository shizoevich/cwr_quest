<template>
    <div>
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
                                           v-model="confidential_form.name" readonly>
                                </div>
                            </div>
                            <div class="col-xs-5">
                                <label class="control-label col-md-4 col-lg-3 pf-label">Date of birth:</label>
                                <div class="col-md-8 col-lg-9 pci-form-group">
                                    <input type="text" class="form-control empty-input"
                                           v-model="confidential_form.date_of_birth" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <p class="form-text">
                                    I understand that my records contain information about my therapy sessions and my
                                    mental health. I understand that all my records are protected by state and federal
                                    laws that require they are kept confidential and require my written consent to
                                    disclose.
                                </p>
                                <p class="form-text">
                                    I, <input type="text" class="form-control empty-input charges-input"
                                              v-model="confidential_form.name" readonly>
                                    , hereby authorize Change Within Reach, Inc. staff to exchange information with
                                    <input id="hereby_information_with" type="text"
                                           class="form-control empty-input xs-6-width"
                                           v-model="confidential_form.hereby_information_with"
                                           autocomplete="new-password"
                                           :readonly="readonly">
                                    <span class="form-text">
                                        for the sole purpose of care coordination and provision of the highest quality of care.
                                    </span>

                                </p>
                                <p class="form-text">
                                    I understand that I have the right to revoke this release at any time.
                                </p>
                                <p class="form-text">
                                    I have been informed and understand this authorization to release records and
                                    information, the nature of listed content that I am willing to release, and the
                                    implications of their release. This request is voluntary.
                                </p>
                                <p class="form-text">
                                    I witnessed that the person understood the content of this authorization and freely
                                    gave his or her consent, but was physically unable to provide a signature.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!--signature-->
                    <signature-preview
                            :relationship="confidential_form.relationship"
                            :signature18="confidential_form.signature18"
                            :signature="confidential_form.signature"
                            :guardian_name="confidential_form.guardian_name"
                    />
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "ConfidentialInformation",
        props: {
            readonly: {
                type: Boolean,
                required: false,
                default: true,
            },
            confidential_form: {
                type: Object,
                required: false,
                default: () => {
                    return {
                        name: '',
                        date_of_birth: '',
                        hereby_information_with: '',
                        guardian_name: '',
                        relationship: '',
                        signature18: null,
                        signature: null,
                    }
                },
            }
        },

    }
</script>

<style scoped>
    .container {
        margin-top: 80px;
    }

    p.form-text {
        margin-top: 11px;
    }

    span.form-text {
        display: inline-block;
        margin-top: 6px;
    }

    .xs-6-width {
        max-width: 700px;
        margin-top: 6px;
    }

    .patient-contact-info-form label.control-label {
        font-weight: normal;
    }

    .patient-contact-info-container .section-add-note {
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .patient-contact-info-container .form-note-button-block button {
        margin-right: 15px;
    }

    .patient-contact-info-container h4 {
        margin: 0;
        display: inline-block;
        line-height: 46px;
    }

    @media screen and (max-width: 684px) {
        .patient-contact-info-container h4 {
            line-height: normal;
            padding-bottom: 10px;
        }

        .patient-contact-info-container button {
            margin-top: 12px;
        }
    }

    .patient-contact-info-form h3.header-part {
        font-weight: 600;
        margin-top: 50px;
        font-size: 24px;
    }

    .patient-contact-info-form h3.header-part.header-part-first {
        margin-top: 0;
    }

    .patient-contact-info-form .checkbox-wrapper {
        border: 2px solid #e8bdbd;
        background-color: #e8bdbd;
    }

    .patient-contact-info-form .checkbox-wrapper {
        padding-left: 1px;
        padding-bottom: 1px;
        border-radius: 4px;
    }

    .patient-contact-info-form input {
        margin: inherit;
    }


    .patient-contact-info-form .inp-group {
        outline: 2px solid #4AC0FB;
        padding: 20px;
    }

    .patient-contact-info-form .pci-form-group {
        padding: 0 !important;
    }

    .patient-contact-info-form label.control-label {
        padding: 0 !important;
        text-align: left !important;
        font-weight: 600;
    }

    .patient-contact-info-form label.control-label.checkbox-label {
        font-weight: normal;
    }


    .patient-contact-info-form label.control-label.radio-btn-label input[type=radio] {
        vertical-align: middle;
    }


    .patient-contact-info-form .custom-checkbox {
        width: 20px;
        height: 20px;
        background-color: #f2dede;
        color: #f2dede;
    }

    .patient-contact-info-form input[type=text].form-control {
        font-size: 12px;
        padding: 0 6px;
        height: 25px;
    }

    .patient-contact-info-form input[type=text].form-control.charges-input {
        display: inline-block;
        max-width: 200px;
    }

    .patient-contact-info-form input[type=text].form-control.empty-input {
        background-color: #f2dede;
    }

    .patient-contact-info-form .view-block h4 {
        margin: 25px 0;
    }

</style>