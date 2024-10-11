export default {
    data() {
        return {
            datePickerBirthDateOptions: {
                disabledDate (date) {
                    return date > new Date()
                }
            },
            datePickerFormat: 'MM/dd/yyyy',
            dateTimePickerFormat: 'MM/dd/yyyy h:mm A',
            datePickerValueFormat: 'yyyy-MM-dd',
            dateTimePickerValueFormat: 'yyyy-MM-dd HH:mm:ss',
            momentDateTimeFormat: 'YYYY-MM-DD HH:mm:ss',
            momentDateFormat: 'YYYY-MM-DD',
            validation_message: null,
            form_data: this.$store.state.currentDocumentData,
            statuses: {
                noErrors: true,
                hasValue: false,
                customError: false,
                saving: false,
                editingDisabled: false,
            },
            currentProvider: this.$store.state.currentProvider,
            currentDocumentCommentUniqueId: this.$store.state.currentDocumentCommentUniqueId,
        }
    },
    computed: {
        isUserAdmin() {
            return Boolean(this.$store.state.isUserAdmin);
        },
        isSupervisorMode() {
            return this.$store.state.is_read_only_mode && this.$store.state.is_supervisor_mode;
        }
    },
    mounted() {
        this.statuses.editingDisabled = !this.isEditingAllowed();
    },
    methods: {
        isEditingAllowed() {
            let currentDocumentEditingStatus = this.$store.state.currentDocumentEditingStatus;

            if (this.isUserAdmin || this.isSupervisorMode) {
                return false;
            }

            if (this.is_read_only_mode && this.is_supervisor_mode) {
                return false;
            }

            if (currentDocumentEditingStatus === null) {
                return true;
            }

            return currentDocumentEditingStatus.allowed;
        },

        computed_modal_title() {
            let currentDocumentEditingStatus = this.$store.state.currentDocumentEditingStatus;
            let documentWarningTitle = this.document_title;
            let documentTitle = "<span>" + this.document_title +"</span>";

            if (!this.isUserAdmin && !this.isSupervisorMode) {
                if (this.statuses.editingDisabled) {
                    return  "<span class='pn-modal-head'>" +documentWarningTitle+ "&nbsp;can no longer be edited. Email admin@cwr.care if you need to modify this document.</span>";
                }
                else if (currentDocumentEditingStatus) {
                    return "<span class='pn-modal-head'>You have " + currentDocumentEditingStatus.hours + " hour(s) to edit&nbsp;" + documentWarningTitle +".</span>";
                }
                else {
                    return documentTitle;
                }
            }
            else {
                return documentTitle;
            }
        },

        closeDocument() {
            $('#'+this.document_name).modal('hide');

            if (this.statuses.hasValue) {
                $('#confirm-document-closing-modal').modal('show').css('display', 'inline-block');
            }
            else {
                this.$store.state.currentDocument = null;
                this.$store.state.currentDocumentEditingStatus = null;
                this.$store.state.currentDocumentSignature = null;
            }
        },
        pickerFocus(name) {
            $('#'+this.document_name).find('[name='+name+']').closest('.input-container').addClass('focus');
        },
        pickerBlur(name) {
            $('#'+this.document_name).find('[name='+name+']').closest('.input-container').removeClass('focus');
        },
        saveDocument() {
            this.validation_message = null;
            this.statuses.customError = this.getCustomValidation();
            this.saveDocumentIfValid(this.statuses.customError);
        },
        setDateError(name) {
            // $('#'+this.document_name).find('[name=session_date]').closest('.input-container').addClass('has-error');
            $('#'+this.document_name).find('[name='+name+']').closest('.input-container').addClass('has-error')
        },
        removeDateError(name) {
            $('#'+this.document_name).find('[name='+name+']').closest('.input-container').removeClass('has-error')
        },
        checkCheckBoxes(checkBoxes) {
            for (let checkBox in checkBoxes) {
                if (checkBoxes[checkBox]) {
                    return true;
                }
            }
            return false;
        },
        setHasValue() {
            this.statuses.hasValue = true;
        },
        resetDateError(name) {
            this.removeDateError(name);
            this.setHasValue();
        },
        getFormattedDate() {
            return moment(this.form_data.date).format('MM/DD/YYYY');
        },
        formatDate(date, format) {
            return moment(date).format(format);
        },
        getSignature() {
            if (this.$store.state.currentDocumentSignature){

                if (this.$store.state.currentDocumentSignature.exist){

                    this.signature = this.$store.state.currentDocumentSignature.image;
                }
            }
            else {

                this.signature = this.getProviderSignature();
            }
        },
        getProviderSignature() {
            this.$store.dispatch('getProviderSignature')
                .then(response => {
                    if (response.status == 200) {
                        this.signature = response.data.signature;
                    }
                });
        },
        getValidationMessage() {
            return;
        },
        getFormName(selector) {
            return this.$parent.electronicDocumentsInfo[selector].slug;
        },
        getFormTitle(selector) {
            return this.$parent.electronicDocumentsInfo[selector].title;
        },
        validatePhoneNumber(phone) {
            if (phone) {
                return phone.match(/^([\d]{3}-){2}[\d]{4}$/);
            }
        },
        parseProviderName(provider) {
            let name = provider.split(',')[0];
            return name ? name.trim() : null;
        },
        parseProviderTitle(provider) {
            let title = provider.split(',')[1];
            return title ? title.trim() : null;
        },
    },
}