<template>
    <div
        id="consent-info-modal"
        class="modal modal-vertical-center fade"
        data-backdrop="static"
        data-keyboard="false"
        role="dialog"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-head">Patient agreements</h4>
                </div>
                <div class="modal-body">
                    <ul>
                        <li>
                            Do we have patient's permission to e-mail them educational materials, treatment related information and appointment reminders: 
                            <b>{{ getConsentValue('allow_mailing') }}</b>
                        </li>
                        <li>
                            Okay to call home phone number and leave messages: 
                            <b>{{ getConsentValue('allow_home_phone_call') }}</b>
                        </li>
                        <li>
                            Okay to call mobile phone number and leave messages: 
                            <b>{{ getConsentValue('allow_mobile_phone_call') }}</b>
                        </li>
                        <li>
                            Okay to send treatment related text messages & appointment reminders to mobile number: 
                            <b>{{ getConsentValue('allow_mobile_send_messages') }}</b>
                        </li>
                        <li>
                            Okay to call work phone number and leave messages: 
                            <b>{{ getConsentValue('allow_work_phone_call') }}</b>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        consentInfo: {
            type: Object,
        },
    },
    methods: {
        getConsentValue(key) {
            if (!this.consentInfo || this.consentInfo[key] === null) {
                return 'N/A';
            }

            return this.consentInfo[key] ? 'Yes' : 'No';
        }
    }
};
</script>

<style scoped></style>
  