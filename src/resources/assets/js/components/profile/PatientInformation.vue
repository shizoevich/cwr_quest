<template>
    <div v-if="show">
        <template v-if="patientInfo">
            <div class="patient-contact-info-container">
                <div class="section section-add-note">
                    <div class="brn-group text-right">
                        <pageloader add-classes="save-loader" v-if="loader.print"></pageloader>
                        <button class="btn btn-primary" v-if="show_buttons" @click="printDocument" :disabled="loader.print">Print</button>
                        <button class="btn btn-primary" v-if="show_buttons" @click="downloadDocument" :disabled="loader.print">Download</button>
                        <button class="btn btn-primary" v-if="show_buttons" @click="openSendViaEmailDialog" :disabled="loader.print">Send via Email</button>
                    </div>

                    <form class="form-note form-horizontal patient-contact-info-form"
                          id="form-note" novalidate>
                        <h3 class="text-center header-part header-part-first">
                            Patient Contact Information
                        </h3>
                        <div class="inp-group">
                            <div class="row">
                                <div class="col-xs-7">
                                    <label class="control-label col-md-2 col-lg-1 pf-label">Name:</label>
                                    <div class="col-md-10 col-lg-11 pci-form-group">
                                        <input type="text" class="form-control empty-input"
                                               v-model="patientInfo.name" readonly>
                                    </div>
                                </div>
                                <!--date of birth-->
                                <div class="col-xs-5">
                                    <label class="control-label col-md-4 col-lg-3 pf-label">Date of Birth:</label>
                                    <div class="col-md-8 col-lg-9 pci-form-group">
                                        <input type="text" class="form-control empty-input"
                                               v-model="patientInfo.date_of_birth" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row pci-row">
                                <!--home address-->
                                <div class="col-xs-12">
                                    <label class="control-label col-md-2 pf-label">Home Address:</label>
                                    <div class="col-md-10 pci-form-group">
                                        <input id="home_address" type="text" class="form-control empty-input"
                                               v-model="patientInfo.home_address" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row pci-row">
                                <!--city-->
                                <div class="col-xs-6 col-lg-3 ">
                                    <label class="control-label col-md-3 col-lg-2 pf-label">City:</label>
                                    <div class="col-md-9 col-lg-10 pci-form-group">
                                        <input id="city" type="text" class="form-control empty-input"
                                               v-model="patientInfo.city" readonly>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-lg-2 wo-pl">
                                    <label class="control-label col-md-2 col-lg-3 pf-label">State:</label>
                                    <div class="col-md-10 col-lg-9 pci-form-group">
                                        <input id="state" type="text" class="form-control empty-input"
                                               v-model="patientInfo.state" readonly>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-lg-2">
                                    <label class="control-label col-md-3 pf-label">Zip:</label>
                                    <div class="col-md-9 pci-form-group">
                                        <input id="zip" type="text" class="form-control empty-input"
                                               v-model="patientInfo.zip" readonly>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-lg-5 wo-pl">
                                    <label class="control-label col-md-2 pf-label">Email:</label><!--col-md-3-->
                                    <div class="col-md-10 pci-form-group"><!--col-md-9-->
                                        <input id="email" type="email" class="form-control empty-input"
                                               v-model="patientInfo.email" readonly>
                                    </div>
                                </div>
                            </div>


                            <div class="row pci-row">
                                <div class="col-md-12" id="allow_mailing">
                                    <label class="control-label col-xs-7 col-lg-9">Do we have your permission to e-mail you free educational materials that you can use between sessions to support your treatment?</label>
                                    <div class="col-xs-5 col-lg-3 text-right pci-form-group">
                                        <label class="control-label">
                                            <input type="radio" value="Yes"
                                                   :checked="patientInfo.allow_mailing" disabled>
                                            Yes
                                        </label>
                                        /
                                        <label class="control-label">
                                            <input type="radio" value="No"
                                                   :checked="!patientInfo.allow_mailing" disabled>
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>


                            <div class="row pci-row">
                                <div class="col-md-5">
                                    <label class="control-label col-md-4 col-lg-3 pf-label">Phone (home):</label>
                                    <div class="col-md-8 col-lg-9 pci-form-group">
                                        <input type="text" class="form-control empty-input"
                                               v-model="patientInfo.home_phone" readonly>
                                    </div>
                                </div>
                                <div class="col-md-7" id="allow_home_phone_call">
                                    <label class="control-label col-xs-7 col-lg-9">Okay to call this number and leave
                                        messages?</label>
                                    <div class="col-xs-5 col-lg-3 text-right pci-form-group">
                                        <label class="control-label">
                                            <input type="radio" value="Yes" disabled
                                                   :checked="patientInfo.allow_home_phone_call">
                                            Yes
                                        </label>
                                        /
                                        <label class="control-label">
                                            <input type="radio" value="No" disabled
                                                   :checked="!patientInfo.allow_home_phone_call">
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row pci-row">
                                <div class="col-md-5">
                                    <label class="control-label col-md-4 col-lg-3 pf-label">Phone (mobile):</label>
                                    <div class="col-md-8 col-lg-9 pci-form-group">
                                        <input id="mobile_phone" type="text" class="form-control empty-input"
                                               v-model="patientInfo.mobile_phone" readonly>
                                    </div>
                                </div>
                                <div class="col-md-7" id="allow_mobile_phone_call">
                                    <label class="control-label col-xs-7 col-lg-9">Okay to call this number and leave
                                        messages?</label>
                                    <div class="col-xs-5 col-lg-3  text-right pci-form-group">
                                        <label class="control-label">
                                            <input type="radio" value="Yes"
                                                   :checked="patientInfo.allow_mobile_phone_call" disabled>
                                            Yes
                                        </label>
                                        /
                                        <label class="control-label">
                                            <input type="radio" value="No"
                                                   :checked="!patientInfo.allow_mobile_phone_call" disabled>
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row pci-row" id="allow_mobile_send_messages">
                                <div class="col-xs-8 col-lg-9">
                                    <label class="control-label pf-label">
                                        Okay to send treatment related text messages & appointment reminders to
                                        mobile number?
                                    </label>
                                </div>
                                <div class="col-xs-4 col-lg-3 text-right">
                                    <label class="control-label">
                                        <input type="radio" value="Yes"
                                               :checked="patientInfo.allow_mobile_send_messages" disabled>
                                        Yes
                                    </label>
                                    /
                                    <label class="control-label">
                                        <input type="radio" value="No"
                                               :checked="!patientInfo.allow_mobile_send_messages" disabled>
                                        No
                                    </label>
                                </div>
                            </div>

                            <div class="row pci-row">
                                <div class="col-md-5">
                                    <label class="control-label col-md-4 col-lg-3 pf-label">Phone (work):</label>
                                    <div class="col-md-8 col-lg-9 pci-form-group">
                                        <input type="text" class="form-control empty-input"
                                               v-model="patientInfo.work_phone" readonly>
                                    </div>
                                </div>
                                <div class="col-md-7" id="allow_work_phone_call">
                                    <label class="control-label col-xs-7 col-lg-9">Okay to call this number and leave
                                        messages?</label>
                                    <div class="col-xs-5 col-lg-3 text-right pci-form-group">
                                        <label class="control-label">
                                            <input type="radio" value="Yes" :checked="patientInfo.allow_work_phone_call"
                                                   disabled>
                                            Yes
                                        </label>
                                        /
                                        <label class="control-label">
                                            <input type="radio" value="No" :checked="!patientInfo.allow_work_phone_call"
                                                   disabled>
                                            No
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row pci-row">
                                <div class="col-xs-12 col-lg-6">
                                    <label class="control-label col-md-5 col-lg-4">Emergency Contact:</label>
                                    <div class="col-md-7 col-lg-8 pci-form-group">
                                        <input id="emergency_contact" type="text" class="form-control empty-input"
                                               v-model="patientInfo.emergency_contact" readonly>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-lg-2 pci-form-group wp-left">
                                    <label class="control-label col-md-5 col-lg-4">Phone:</label>
                                    <div class="col-md-7 col-lg-8 pci-form-group">
                                        <input id="emergency_contact_phone" type="text" class="form-control empty-input"
                                               v-model="patientInfo.emergency_contact_phone" readonly>
                                    </div>
                                </div>
                                <div class="col-xs-6 col-lg-4">
                                    <label class="control-label col-md-5 col-lg-4">Relationship:</label>
                                    <div class="col-md-7 col-lg-8 pci-form-group">
                                        <input id="emergency_contact_relationship" type="text"
                                               class="form-control empty-input"
                                               v-model="patientInfo.emergency_contact_relationship" readonly>
                                    </div>
                                </div>
                            </div>
                            <div id="required_only_one">
                                <div class="row pci-row">
                                    <div class="col-xs-12 bold-label">
                                        How did you hear about us?
                                    </div>
                                    <div class="col-xs-12">
                                        <label class="control-label checkbox-label bold-label">
                                        <span class="checkbox-wrapper">
                                            <input type="checkbox"
                                                   class="form-control empty-field checkbox-inline custom-checkbox"
                                                   v-model="patientInfo.yelp" disabled>
                                        </span>
                                            Yelp
                                        </label>
                                        <label class="control-label checkbox-label bold-label">
                                        <span class="checkbox-wrapper">
                                            <input type="checkbox"
                                                   class="form-control empty-field checkbox-inline custom-checkbox"
                                                   v-model="patientInfo.google" disabled>
                                        </span>
                                            Google
                                        </label>
                                        <label class="control-label checkbox-label bold-label">
                                        <span class="checkbox-wrapper">
                                            <input type="checkbox"
                                                   class="form-control empty-field checkbox-inline custom-checkbox"
                                                   v-model="patientInfo.yellow_pages" disabled>
                                        </span>
                                            Yellow Pages
                                        </label>
                                        <label class="control-label checkbox-label bold-label">
                                        <span class="checkbox-wrapper">
                                            <input type="checkbox"
                                                   class="form-control empty-field checkbox-inline custom-checkbox"
                                                   v-model="patientInfo.event_i_attended" disabled>
                                        </span>
                                            Event I attended
                                        </label>
                                        <span id="hear_about_us_other">
                                        <label class="control-label checkbox-label bold-label">
                                            <span class="checkbox-wrapper">
                                                <input type="checkbox"
                                                       class="form-control empty-field checkbox-inline custom-checkbox"
                                                       v-model="patientInfo.hear_about_us_other" disabled>
                                            </span>
                                            Other (specify):
                                        </label>
                                        <input type="text" class="form-control empty-input inline-block max-300"
                                               v-model="patientInfo.hear_about_us_other_specify" readonly>
                                    </span>
                                    </div>
                                </div>

                                <div class="row pci-row">
                                    <div class="col-xs-12 bold-label">
                                        I was referred by:
                                    </div>
                                    <div class="col-xs-12">
                                        <label class="control-label checkbox-label bold-label">
                                        <span class="checkbox-wrapper">
                                            <input type="checkbox"
                                                   class="form-control empty-field checkbox-inline custom-checkbox"
                                                   v-model="patientInfo.friend_or_relative" disabled>
                                        </span>
                                            Friend or relative
                                        </label>
                                        <label class="control-label checkbox-label bold-label">
                                        <span class="checkbox-wrapper">
                                            <input type="checkbox"
                                                   class="form-control empty-field checkbox-inline custom-checkbox"
                                                   v-model="patientInfo.another_professional" disabled>
                                        </span>
                                            Another professional
                                        </label>
                                        <label class="control-label checkbox-label bold-label">
                                        <span class="checkbox-wrapper">
                                            <input type="checkbox"
                                                   class="form-control empty-field checkbox-inline custom-checkbox"
                                                   v-model="patientInfo.kaiser" disabled>
                                        </span>
                                            Kaiser
                                        </label>
                                        <span id="referred_by_other_insurance">
                                        <label class="control-label checkbox-label bold-label">
                                            <span class="checkbox-wrapper">
                                                <input type="checkbox"
                                                       class="form-control empty-field checkbox-inline custom-checkbox"
                                                       v-model="patientInfo.referred_by_other_insurance" disabled>
                                            </span>
                                            Other insurance
                                        </label>
                                        <input type="text" class="form-control empty-input inline-block max-300"
                                               v-model="patientInfo.referred_by_other_insurance_specify" readonly>
                                    </span>


                                    </div>
                                </div>
                            </div>
                        </div><!--/.inp-group-->

                        <div v-if="show_payment_form">
                        <h3 class="text-center header-part">
                            PAYMENT FOR SERVICE AND FEE ARRANGEMENTS
                        </h3>
                        <div class="inp-group">
                            <div class="row">
                                <label class="col-xs-8 col-sm-9 co-pay-label">
                                    Co-pay and/or co-insurance for session:
                                </label>
                                <div class="col-xs-4 col-sm-3 text-right">
                                    $<input type="text" class="payment-input form-control inline-block"
                                            v-model="patientInfo.co_pay" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-xs-8 col-sm-9 co-pay-label">
                                    Payment for session not covered due to deductible:
                                </label>
                                <div class="col-xs-4 col-sm-3 text-right">
                                    $<input type="text" class="payment-input form-control inline-block"
                                            v-model="patientInfo.payment_for_session_not_converted" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-xs-8 col-sm-9 co-pay-label">
                                    Self-pay for session when paid out-of-pocket:
                                </label>
                                <div class="col-xs-4 col-sm-3 text-right">
                                    $<input type="text" class="payment-input form-control inline-block"
                                            v-model="patientInfo.self_pay" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-xs-8 col-sm-9 co-pay-label">
                                    Charge for cancellation without 24 hours&#039; notice:
                                </label>
                                <div class="col-xs-4 col-sm-3 text-right">
                                    $<input type="text" class="payment-input form-control inline-block"
                                            v-model="patientInfo.charge_for_cancellation" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-xs-8 col-sm-9 co-pay-label">
                                    Other charges [specify]: <input type="text"
                                                                    class="payment-input other-payment-input form-control inline-block"
                                                                    v-model="patientInfo.other_charges" disabled>
                                </label>
                                <div class="col-xs-4 col-sm-3 text-right">
                                    $<input type="text" class="payment-input form-control inline-block"
                                            v-model="patientInfo.other_charges_price" readonly>
                                </div>
                            </div>

                            <div class="empty-space-40"></div>
                            <hr class="block-separator">
                            <div class="row">
                                <div class="col-xs-12">
                                    <label class="control-label checkbox-label">
                                    <span class="checkbox-wrapper">
                                        <input type="checkbox"
                                               class="form-control empty-field checkbox-inline custom-checkbox"
                                               v-model="patientInfo.store_credit_card" disabled>
                                    </span>
                                        <b>Patient store his card on file with Change Within Reach, Inc.</b>
                                    </label>
                                </div>
                            </div>
                        </div>
                        </div>
                        <signature-preview
                                v-if="show_signature"
                                :relationship="patientInfo.relationship"
                                :signature18="patientInfo.signature18"
                                :signature="patientInfo.signature"
                                :guardian_name="patientInfo.guardian_name"
                        />
                    </form>
                </div>
            </div>
        </template>
        <template v-else>
            <h2 class="text-center">Patient did not fill out the information form</h2>
        </template>



        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="send-patient-information-via-email" role="dialog">
            <div class="modal-dialog">
<!--                <div class="modal-loader-container" v-if="loader.sending">-->
<!--                    <pageloader add-classes="note-loader"></pageloader>-->
<!--                </div>-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                @click.prevent="closeSendViaEmailDialog()"
                                :disabled="loader.sending">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Send via email</h4>
                    </div>
                    <form class="form-horizontal" @submit.prevent>
                        <div class="modal-body">
                            <div class="form-group" id="document-email">
                                <label class="control-label col-sm-2">Email</label>
                                <div class="col-sm-10">
                                    <input type="email" class="form-control" v-model="patient_email">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
<!--                            <span class="text-red validation-error-msg">{{ validation_message }}</span>-->
                            <pageloader add-classes="save-loader" v-show="loader.sending"></pageloader>
                            <button type="button" class="btn btn-primary"
                                    @click.prevent="sendViaEmail()"
                                    :disabled="loader.sending">
                                Send
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    import print from 'print-js';
    export default {
        name: 'PatientInformation',
        props: {
            patient: {
                type: Object,
                require: true,
            },
            show: {
                type: Boolean,
                require: true,
                default: false,
            },
            show_buttons:{
                type: Boolean,
                require: false,
                default: true,
            },
            show_payment_form:{
                type: Boolean,
                require: false,
                default: true,
            },
            show_signature:{
                type: Boolean,
                require: false,
                default: false,
            }
        },
        data() {
            return {
                loader: {
                    print: false,
                    sending: false
                },
                patient_email: null,
            }
        },
        computed: {
            patientInfo() {
                return this.$props.patient.information_form;
            },

            patientEmail() {
                if(this.patient_email) {
                    return this.patient_email;
                }
                if(this.patientInfo && this.patientInfo.email) {
                    return this.patientInfo.email;
                }

                return null;
            }
        },

        mounted() {

        },

        methods: {
            printDocument() {
                this.loader.print = true;
                this.$store.dispatch('getPatientInfoDocumentBase64', this.patient.id).then(response => {
                    print({printable: response.data.document, type: 'pdf', base64: true});
                    this.loader.print = false;
                }).catch(response => {
                    this.loader.print = false;
                });
            },

            openSendViaEmailDialog() {
                this.patient_email = this.patientEmail;
                $('#send-patient-information-via-email').modal('show');
            },

            closeSendViaEmailDialog() {
                $('#send-patient-information-via-email').modal('hide');
                this.patient_email = null;
            },

            sendViaEmail() {
                this.loader.sending = true;
                this.$store.dispatch('sendPatientInfoDocumentViaEmail', {patient_id: this.patient.id, email: this.patient_email}).then(response => {
                    this.closeSendViaEmailDialog();
                    this.loader.sending = false;
                }).catch(response => {
                    this.closeSendViaEmailDialog();
                    this.loader.sending = false;
                    window.alert('Email could not have been sent due to connection problems. Please try again later.')
                });
            },

            downloadDocument() {
                window.open('/patient/download-document/' + this.patient.information_form_document_name, '_blank');
            },
        }
    }
</script>

<style scoped lang="scss">
    @import "../../../sass/forms_styles";

    .save-loader {
        max-width: 36px;
        max-height: 36px;
        margin-right: 15px;
    }

    .bold-label {
        font-weight: 600 !important;
    }

    .validation-error-msg {
        padding-right: 20px;
    }

    .max-300 {
        max-width: 300px;
    }

    .sub-title {
        font-weight: 600;
    }

    .block-separator {
        margin-top: 0;
        background-color: #3e4855;
        height: 1px;
    }

    .empty-space-40 {
        height: 40px;
    }

    .empty-space-50 {
        height: 50px;
    }

    .empty-space-10 {
        height: 10px;
    }

    .co-pay-label {
        font-weight: 600;
    }

    .co-pay-row {
        margin-top: 10px;
    }

    .checkbox-label2 input {
        height: 20px;
        width: 25px;
    }

    .checkbox-label2 {
        font-weight: normal;
    }

    #patient-signature,
    #patient-signature-under-18 {
        margin-top: 10px;
    }

    .pci-form-group.wp-left{
        @media (max-width: 1200px) {
            padding-left:15px!important;
        }
    }

    @media (max-width: 1200px) {
        .pci-row {
            .wo-pl {
                margin-bottom: 15px;
            }
        }
        #emergency_contact {
            margin-bottom: 15px;
        }
        #required_only_one{
            >div{
                >div{
                    >span{
                        >input{
                            margin-top:15px;
                            float: right;
                        }

                    }
                }
            }
        }
    }
    .patient-contact-info-container > .section{
        border: 1px solid #e7e7e7;
        border-radius: 4px;
    }
</style>
