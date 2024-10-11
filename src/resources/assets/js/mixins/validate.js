export default {
    methods: {
        validate() {
            let promises = [
                this.$validator.validateAll()
            ];
            for (let child in this.$children) {
                promises.push(this.$children[child].$validator.validateAll());
            }

            return Promise.all(promises).then(
                        values => _.every(values)
                    );
        },
        resetValidation(){
            for (let element in this.$children) {

                if(this.$children[element].name !== undefined){
                    this.$children[element].$validator.flag(this.$children[element].name, {
                        pristine: true
                    });
                }
            }
        },

        getCustomValidationDiagnoses() {
            let has_errors = false;
            let fields = [
                {
                    name: 'selected_diagnoses',
                    id: 'diagnoseMultipleSelect',
                },
                {
                    name: 'selected_diagnoses_1',
                    id: 'diagnoseMultipleSelect1',
                },
                // {
                //     name: 'selected_diagnoses_2',
                //     id: 'diagnoseMultipleSelect2',
                // },
                // {
                //     name: 'selected_diagnoses_3',
                //     id: 'diagnoseMultipleSelect3',
                // },
            ];

            for(let i in fields) {
                if(this.form_data[fields[i]['name']]) {
                    if(this.form_data[[fields[i]['name']]].length === 0) {
                        $('#'+this.document_name).find('#' + fields[i]['id']).parents('.form-group').addClass('error-focus');

                        has_errors = true;
                    }
                }
            }

            return has_errors;
        },
    },
}