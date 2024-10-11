export default {
    data(){
        return{
            date_of_service_fastselect: null,
            patient: this.$store.state.currentPatient,
            appointment: null,
        }
    },
    computed: {
        date_of_service_fastselect_url() {
            return '/patient/' + this.patient.id + '/appointment-document-dates';
        },
    },
    methods: {
        initDateOfService(){
            //date of service fast select
            let initial_values = null;
            let self = this;

            if(self.form_data.date_of_service) {
                initial_values = {
                    text: self.form_data.date_of_service,
                    value: self.form_data.date_of_service
                };
            }

            self.date_of_service_fastselect = $('#'+self.document_name).find('#date-of-service-fastselect').fastselect({
                placeholder: "",
                url: self.date_of_service_fastselect_url,
                userOptionAllowed: true,
                maxItems: 1,
                noResultsText: "No Appointments",
                userOptionPrefix: 'Set ',
                initialValue: initial_values,
                onItemSelect: function($item, itemModel) {
                    self.form_data.appointment_id = itemModel.appointment_id;
                    $('#'+self.document_name).find('#date-of-service-fastselect-container .fstQueryInput').css('display', 'none');
                    $('#'+self.document_name).find('#date-of-service-fastselect').parents('.form-group').removeClass('error-focus');
                    if(itemModel.diagnoses && itemModel.diagnoses.length > 0) {
                        if('selected_diagnoses' in self.form_data) {
                            self.form_data.selected_diagnoses = itemModel.diagnoses;
                        } else {
                            self.form_data.selected_diagnoses_1 = itemModel.diagnoses;
                            self.form_data.selected_diagnoses_2 = [];
                            self.form_data.selected_diagnoses_3 = [];
                        }
                        self.form_data.diagnoses_editable = false;
                    } else {
                        if('selected_diagnoses' in self.form_data) {
                            self.form_data.selected_diagnoses = self.$store.state.currentPatient.diagnoses || [];
                        } else {
                            self.form_data.selected_diagnoses_1 = self.$store.state.currentPatient.diagnoses || [];
                            self.form_data.selected_diagnoses_2 = [];
                            self.form_data.selected_diagnoses_3 = [];
                        }
                        self.form_data.diagnoses_editable = true;
                    }

                    self.setHasValue();
                    let reg = self.validateDateOfService(self.getDateOfService());
                    self.statuses.noErrors = (reg !== null);
                    self.statuses.invalid_date_of_service = (reg === null);
                    if(self.statuses.noErrors) {
                        $('#'+self.document_name).find('#date-of-service-fastselect').parents('.form-group').removeClass('error-focus');

                        if(self.form_data.start_time !== undefined){
                            let payload = {
                                patient_id: self.patient.id,
                                date: self.getDateOfService()
                            };
                            self.$store.dispatch('getAppointmentTimeByAppointmentDate', payload).then(response => {
                                console.log(response);
                                if(response.status === 200 && response.data) {
                                    self.form_data.start_time = moment(response.data, 'HH:mm a').format('LT');
                                    self.form_data.end_time = moment(response.data, 'HH:mm a').format('LT');
                                }
                            });
                        }
                    } else {
                        $('#'+self.document_name).find('#date-of-service-fastselect').parents('.form-group').addClass('error-focus');
                    }
                },
            }).data('fastselect');

            if(initial_values) {
                $('#'+self.document_name).find('#date-of-service-fastselect-container .fstQueryInput').css('display', 'none');
            }

            $('#'+self.document_name).find('#date-of-service-fastselect-container .fstQueryInput').mask('00/00/0000');
            //date of service fast select
        },
        validateDateOfService(date) {
            if(date) {
                return date.match(/^((0[1-9])|(1[0-2]))\/((0[1-9])|([1-2][0-9])|(3[0-1]))\/([1-9][0-9]{3})$/ig);
            }

            return this.date_of_service.match(/^((0[1-9])|(1[0-2]))\/((0[1-9])|([1-2][0-9])|(3[0-1]))\/([1-9][0-9]{3})$/ig);
        },
        getDateOfService() {
            let values = this.date_of_service_fastselect.optionsCollection.selectedValues;
            for(let i in values) {
                return i;
            }
            return "";
        },

        getCustomValidateionDateOfService(){
            this.form_data.date_of_service = this.getDateOfService();
            let error = false;

            if(this.form_data.date_of_service) {
                let reg = this.validateDateOfService(this.form_data.date_of_service);
                if(this.form_data.date_of_service === '' || !reg) {
                    $('#'+this.document_name).find('#date-of-service-fastselect').parents('.form-group').addClass('error-focus');
                    error = true;

                    if(!reg){

                        this.validation_message = 'Invalid date format. (Example MM/DD/YYYY)';
                    }
                }

            } else {
                $('#'+this.document_name).find('#date-of-service-fastselect').parents('.form-group').addClass('error-focus');
                error = true;
            }

            if(!error){
                this.form_data.date_of_service = moment(this.form_data.date_of_service, 'MM/DD/YYYY').format(this.momentDateFormat)
            }

            return error;
        },
    }
}