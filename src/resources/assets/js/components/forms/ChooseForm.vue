<template>
    <div style="height: 100%;">
        <div class="container" style="margin-bottom: 80px;">
            <router-link to="/forms" class="btn btn-lg btn-success btn-back">New Search</router-link>
        </div>
        <div class="forms-center-container">
            <div class="container">

                <div id="status-alert"></div>
                <h2 class="text-center">{{ patientFullName() }}</h2>
                <hr class="forms-separator">
                <div class="buttons-container">
                    <router-link class="btn btn-default btn-lg big-btn" :to="`${this.$route.path + '/form1'}`">
                        <b>New Patient</b>
                        Patient Information /<br>Informed Consent /<br>Privacy Notice
                    </router-link>
                    <router-link class="btn btn-default btn-lg big-btn" :to="`${this.$route.path + '/form2'}`">
                        Authorization to Release<br>Confidential Information
                    </router-link>
                    <a v-show="isValidSubscriberId()" class="btn btn-default btn-lg big-btn" target="_blank"
                                 :href="patientThirdUrl">
                        Kaiser TPI<br>
                    </a>
                    <div class="btn btn-default btn-lg big-btn photo-btn" :class="{disabled: is_upload_btn_disabled}">
                        <span>
                            Attach supporting documents
                            <br/>
                            ID Card or Drivers license,
                            <br/>
                            Insurance Card or Other
                            <br/>
                            Documents
                        </span>
                        <!--DONT TOUCH INLINE STYLES FOR INPUT!-->
                        <input name="patient_photo" id="patient-photo" type="file" title="" accept="image/*"
                               style="opacity: 0 !important;height: 100%;width: 100%;">
                    </div>

                    <router-link v-if="patient && !patient.is_payment_forbidden" class="btn btn-default btn-lg big-btn" :to="`${this.$route.path + '/add-credit-card'}`">
                        <span>Add Credit Card on file</span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Alert from "./../../Alert";

    export default {
        data() {
            return {
                error: null,
                patient_photo: '',
                is_upload_btn_disabled: false
            }
        },
        mounted() {
            this.fetchData();
        },

        computed: {
            patient() {
                return this.$store.state.currentPatient;
            },
            patientThirdUrl() {
                if (this.isValidSubscriberId()) {
                    return `https://epchangewithinreach-tpi.polestarapp.com/register/patient_details?check_mrn=true&mrn=${parseInt(this.patient.subscriber_id)}`;
                }

                return null;
            },
        },

        methods: {

            fetchData() {
                let id = this.$route.params.id;
                this.$store.dispatch('getPatient', {id: id}).then(error => {
                    if (error) {
                        if (error.status === 403 || error.status === 404) {
                            this.$router.push({path: '/forms/404'});
                        }
                    }
                    this.initFileUploader();
                });
            },

            isValidSubscriberId() {
                return this.patient && this.patient.subscriber_id && /^\d+$/.test(this.patient.subscriber_id);
            },

            initFileUploader() {
                let self = this;
                window.setTimeout(() => {
                    $('#patient-photo').fileinput({
                        browseClass: "fileupload-btn",
                        showCaption: false,
                        showRemove: false,
                        showUpload: false,
                        browseLabel: '',
                        browseIcon: '',
                        showPreview: false,
                        showCancel: false,
                        uploadUrl: "/forms/patient/upload-photo",
                        uploadExtraData: {patient_id: self.patient.id},
//                            allowedFileExtensions: ['jpg', 'png', 'gif'],
                    }).on('fileselect', function () {
                        self.is_upload_btn_disabled = true;
                        $(this).fileinput('upload');
                    }).on('fileuploaded', function (e, data) {
                        self.showPhotoUploadingResponse(data);
                    }).on('fileuploaderror', function (e, data, msg) {
                        data.response.message = 'The supporting document must be a file of type: gif, jpeg, png.';
                        data.response.success = false;
                        self.showPhotoUploadingResponse(data);
                        $(this).fileinput('reset');
                    });
                }, 50);
            },

            showPhotoUploadingResponse(data) {
                console.log(data);
                $('.kv-upload-progress').css('display', 'none');
                this.is_upload_btn_disabled = false;
                if (data.response) {
                    let cl = 'alert-';
                    if (data.response.success) {
                        cl += 'success';
                    } else {
                        cl += 'danger';
                    }
                    Alert.show('#status-alert', data.response.message, cl);
                }
            },

            patientFullName() {
                if (this.patient !== null) {
                    return this.patient.first_name + ' ' + this.patient.last_name + ' ' + this.patient.middle_initial;
                }
            },

            openForm(num) {
                switch (num) {
                    case 1:
                        this.$router.push(this.$route.path + '/form1');
                        break;
                }
            }
        }
    }
</script>

<style scoped>
    .forms-separator {
        margin-bottom: 50px;
        margin-top: 39px;
        background-color: #3e4855;
        height: 1px;
    }

    .btn-back {
        position: absolute;
        top: 21px;
    }
</style>
