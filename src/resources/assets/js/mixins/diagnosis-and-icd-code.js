export default {
    data(){
        return{
            diagnosis_icd_code_fastselect: null,
            patient: this.$store.state.currentPatient,
            just_icd_code: false,
        }
    },
    computed:{
        fastselect_url() {

            if(this.just_icd_code){

                return '/patient/' + this.patient.patient_id + '/diagnoses-codes';
            }
            return '/patient/' + this.patient.patient_id + '/diagnoses';
        },
    },
    methods: {
        initDiagnosisAndIcdCode(){
            let self = this;

            self.diagnosis_icd_code_fastselect = $('#'+self.document_name).find('#diagnoseMultipleSelect').fastselect({
                placeholder: "",
                url: self.fastselect_url,
                onItemSelect: function() {
                    $('#'+self.document_name).find('#diagnoseMultipleSelect').parents('.form-group').removeClass('has-error');
                    self.setHasValue();
                },
                initialValue: self.getDiagnosisDataset()
            }).data('fastselect');
        },
        getDiagnosisIcdCode(flag) {
            let res = "";
            let values = this.diagnosis_icd_code_fastselect.optionsCollection.selectedValues;
            for(let i in values) {
                res += "\"" + i + "\",";
            }
            if(flag) {
                this.diagnosis_icd_code_fastselect = null;
            }
            if(res.length > 1) {
                return res.substring(0, res.length - 1);
            }
            console.log(res);
            return res;
        },
        getDiagnosisDataset() {
            let data = this.form_data.diagnosis_icd_code;
            let res = [];
            if(data) {
                data = data.split('","');
                for(let i in data) {
                    data[i] = data[i].replace(/"/g, '');
                    res.push({
                        text: data[i],
                        value: data[i]
                    });
                }
            }
            return res;
        },
        setDiagnosisIcdCode(){
            this.form_data.diagnosis_icd_code = this.getDiagnosisIcdCode(false);

            if(this.form_data.diagnosis_icd_code){
                this.form_data.diagnosis_icd_code = this.form_data.diagnosis_icd_code.trim();

                if(this.form_data.diagnosis_icd_code === '') {
                    this.form_data.diagnosis_icd_code = null;
                }
            }
        }
    }
}