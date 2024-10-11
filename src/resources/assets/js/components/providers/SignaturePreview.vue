<template>
    <div>
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
                    </div>

                </div>
                <div id="patient-signature" style="height:313px;text-align:center;">
                    <img :src="signature" style="max-height:261px;">
                </div>
                <hr class="signature-line">
            </div>
        </div>
        <!--<div class="empty-space-50"></div>-->
        <div class="row" style="margin-bottom:20px; margin-top:75px;">
            <div class="col-xs-8">
                <label class="control-label col-md-6 col-lg-5 pf-label">Name of
                    parent/guardian/representative:</label>
                <div class="col-md-6 col-lg-7 pci-form-group">
                    <input id="guardian_name" type="text" class="form-control empty-input"
                           autocomplete="new-password" v-model="guardian_name"
                           :readonly="true">
                </div>
            </div>
            <div class="col-xs-4">
                <label class="control-label col-md-4 pf-label">Relationship:</label>
                <div class="col-md-8 pci-form-group">
                    <input id="relationship" type="text" class="form-control empty-input"
                           :readonly="true"
                           autocomplete="new-password" v-model="relationship">
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
                    </div>

                </div>
                <div id="patient-signature18" style="height:313px;text-align:center;">
                    <img :src="signature18" style="max-height:261px;">
                </div>
                <hr class="signature-line">
            </div>
        </div>
    </div>
    </div>
</template>

<script>
    export default {
        name: "SignaturePreview",
        props: {
            guardian_name: {
                type: String,
                required: false,
            },
            relationship: {
                type: String,
                required: false,
            },
            signature18: {
                type: String,
                required: false,
            },
            signature: {
                type: String,
                required: false,
            },
        },
    }
</script>

<style scoped>


     h4 {
        margin: 0;
        display: inline-block;
        line-height: 46px;
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
        margin-top: -2px;
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