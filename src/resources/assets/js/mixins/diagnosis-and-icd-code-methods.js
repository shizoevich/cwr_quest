export default {
    data(){
        return{
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
        initDiagnosisAndIcdCode(fastselect, selectId, select){
            let self = this;

            self[fastselect] = $('#'+self.document_name).find(`#${selectId}`).fastselect({
                placeholder: "",
                url: self.fastselect_url,
                onItemSelect: function() {
                    $('#'+self.document_name).find(`#${selectId}`).parents('.form-group').removeClass('has-error');
                    self.setHasValue();
                },
                initialValue: self.getDiagnosisDataset(select)
            }).data('fastselect');
        },
        getDiagnosisIcdCode(flag, fastselect) {
            let res = "";
            let values = this[fastselect].optionsCollection.selectedValues;
            for(let i in values) {
                res += "\"" + i + "\",";
            }
            if(flag) {
                this[fastselect] = null;
            }
            if(res.length > 1) {
                return res.substring(0, res.length - 1);
            }
            console.log(res);
            return res;
        },
        getDiagnosisDataset(select) {
            let data = this.form_data[select];
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
        setDiagnosisIcdCode(fastselect, select){
            this.form_data[select] = this.getDiagnosisIcdCode(false, fastselect);

            if(this.form_data[select]){
                this.form_data[select] = this.form_data[select].trim();

                if(this.form_data[select] === '') {
                    this.form_data[select] = null;
                }
            }
        }
    }
}