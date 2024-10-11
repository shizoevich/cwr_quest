export default {
    methods: {
        saveDocumentIfValid(customError) {
            this.validate().then(valid => {
                this.getValidationMessage();

                if (valid && !customError) {
                    this.statuses.saving = true;
                    let url = '';

                    if (this.$store.state.currentDocumentData) {
                        url = 'updateElectronicDocument';
                    } else {
                        url = 'saveElectronicDocument';
                    }

                    const patientId = this.$store.state.currentPatient.id;
                    this.$store.dispatch(url,
                        {
                            document_slug: this.document_name,
                            patient_id: this.patient_id || patientId,
                            provider_id: this.$store.state.currentProvider.id,
                            document_data: JSON.stringify(this.form_data)
                        }
                    ).then(savingResponse => {
                        if (savingResponse.status >= 200 && savingResponse.status < 300) {
                            this.statuses.noErrors = true;
                            this.$store.state.currentDocument = null;
                            this.$store.state.currentDocumentData = null;
                            this.$store.dispatch('getPatientNotesWithDocumentsPaginated', {id: patientId});
                            this.$store.dispatch('getPatientVisitCreatedAppointments', patientId);
                            $('#'+this.document_name).modal('hide');
                        }

                        if (savingResponse.status == 500) {
                            this.statuses.noErrors = true;
                            this.validation_message = 'Saving ' + this.document_title + ' failed. Please try again.';
                        }

                        this.$store.state.currentDocumentEditingStatus = null;
                        this.statuses.saving = false;
                    });
                } else {
                    this.resetValidation();
                    this.statuses.noErrors = false;
                }
            });
        }
    },
}