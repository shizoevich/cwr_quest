export default {
    methods: {
        setDiagnoses(diagnoses) {
            this.selected_diagnoses = diagnoses;
        },

        setElectronicDocumentsDiagnoses(diagnoses) {
            this.form_data.selected_diagnoses = diagnoses;
        },

        setElectronicDocumentsDiagnoses1(diagnoses) {
            this.form_data.selected_diagnoses_1 = diagnoses;
        },

        setElectronicDocumentsDiagnoses2(diagnoses) {
            this.form_data.selected_diagnoses_2 = diagnoses;
        },

        setElectronicDocumentsDiagnoses3(diagnoses) {
            this.form_data.selected_diagnoses_3 = diagnoses;
        },
    },

    computed: {
        c_diagnoses_editing_disabled() {
            return this.statuses.editingDisabled || !!this.$store.state.currentDocumentData;
        }
    },

    watch: {
        selected_diagnoses() {
            if(this.selected_diagnoses && this.selected_diagnoses.length > 0) {
                $('#diagnoseMultipleSelect').parents('.form-group').removeClass('error-focus');
            }
            if(this.statuses) {
                this.statuses.confirm_diagnoses = false;
            }
        },

        'form_data.selected_diagnoses'() {
            if(this.form_data && this.form_data.selected_diagnoses && this.form_data.selected_diagnoses.length > 0) {
                $('#diagnoseMultipleSelect').parents('.form-group').removeClass('error-focus');
            }
            if(this.statuses) {
                this.statuses.confirm_diagnoses = false;
            }
        },

        'form_data.selected_diagnoses_1'() {
            if(this.form_data && this.form_data.selected_diagnoses_1 && this.form_data.selected_diagnoses_1.length > 0) {
                $('#diagnoseMultipleSelect1').parents('.form-group').removeClass('error-focus');
            }
            if(this.statuses) {
                this.statuses.confirm_diagnoses = false;
            }
        },

        'form_data.selected_diagnoses_2'() {
            if(this.form_data && this.form_data.selected_diagnoses_2 && this.form_data.selected_diagnoses_2.length > 0) {
                $('#diagnoseMultipleSelect2').parents('.form-group').removeClass('error-focus');
            }
            if(this.statuses) {
                this.statuses.confirm_diagnoses = false;
            }
        },

        'form_data.selected_diagnoses_3'() {
            if(this.form_data && this.form_data.selected_diagnoses_3 && this.form_data.selected_diagnoses_3.length > 0) {
                $('#diagnoseMultipleSelect3').parents('.form-group').removeClass('error-focus');
            }
            if(this.statuses) {
                this.statuses.confirm_diagnoses = false;
            }
        },
        'statuses.confirm_diagnoses'() {
            if(this.statuses && this.statuses.confirm_diagnoses) {
                $('#ia-confirm-diagnoses label').removeClass('text-red');
            }
        },
    },
}