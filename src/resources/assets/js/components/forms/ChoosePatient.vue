<template>
    <div class="forms-center-container">
        <div class="container">
            <div class="well col-sm-8 col-md-6 center-block">

                <div id="status-alert"></div>

                <div class="form-horizontal">
                    <h2>Search by Patient ID</h2>
                    <div class="form-group form-group-lg">
                        <label class="col-xs-12 control-label" for="id">Enter Patient ID:</label>
                        <div class="col-xs-12">
                            <input id="id" name="id" type="number"
                                   class="form-control input-lg" v-model="patientId" @keyup.enter="selectPatient"
                                   autofocus>
                            <span class="help-block"></span>
                        </div>
                    </div>

                    <div class="form-group form-group-lg">
                        <div class="col-xs-12">
                            <!--<router-link :to="`${'/forms/patient-' + patientId}`"-->
                            <!--class="btn btn-lg btn-success pull-right">OK-->
                            <!--</router-link>-->
                            <button type="button" class="btn btn-success btn-lg pull-right"
                                    @click.prevent="selectPatient" :disabled="disabled.patientId">
                                OK
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center delimiter">- OR -</p>

            <div class="well col-sm-8 col-md-6 center-block">
                <div id="full-patient-alert"></div>

                <div class="form-horizontal">
                    <h2>Search by Patient Name</h2>
                    <div class="form-group form-group-lg">
                        <label class="col-xs-12 control-label" for="first_name">Enter Patient First Name:</label>
                        <div class="col-xs-12">
                            <input id="first_name" name="first_name" type="text"
                                   class="form-control input-lg" v-model="patient.firstName"
                                   @keyup.enter="selectPatientByFullData">
                            <span class="help-block"></span>
                        </div>
                    </div>
                    <div class="form-group form-group-lg">
                        <label class="col-xs-12 control-label" for="last_name">Enter Patient Last Name:</label>
                        <div class="col-xs-12">
                            <input id="last_name" name="last_name" type="text"
                                   class="form-control input-lg" v-model="patient.lastName"
                                   @keyup.enter="selectPatientByFullData">
                            <span class="help-block"></span>
                        </div>
                    </div>

                    <div class="form-group form-group-lg">
                        <label class="col-xs-12 control-label"
                               for="date_of_birth">Enter Patient Date of Birth:</label>
                        <div class="col-xs-12">
                            <el-date-picker class="date-filter date-filter-2 patient-forms-date-filter"
                                            v-model="patient.dateOfBirth"
                                            id="date_of_birth"
                                            name="date_of_birth"
                                            :picker-options="pickerOptions"
                                            :editable="false"
                                            :clearable="false"
                                            value-format="yyyy-MM-dd"
                                            format="MM/dd/yyyy"
                                            @keyup.enter="selectPatientByFullData"
                            />
                            <span class="help-block"></span>
                        </div>
                    </div>

                    <div class="form-group form-group-lg">
                        <div class="col-xs-12">
                            <button type="button" class="btn btn-success btn-lg pull-right"
                                    @click.prevent="selectPatientByFullData" :disabled="disabled.patient">
                                OK
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import Alert from '../../Alert';

    export default {
        data() {
            return {
                patientId: null,
                patient: {
                    firstName: null,
                    lastName: null,
                    dateOfBirth: null,
                },
                disabled: {
                    patientId: false,
                    patient: false,
                },
                pickerOptions: {
                    disabledDate(time) {
                      return time.getTime() > Date.now();
                    },
                }
            }
        },
        methods: {
            selectPatient() {
                if (this.patientId === null || this.patientId === '') {
                    Alert.show('#status-alert', 'Please enter a patient ID.', 'alert-danger');
                    return false;
                }
                this.disabled.patientId = true;
                this.$store.dispatch('isPatientExists', {officeallyId: this.patientId}).then(response => {
                    if (response.exists) {
                        this.$router.push('/forms/patient-' + this.patientId);
                    } else {
                        Alert.show('#status-alert', 'Cannot find a patient with ' + this.patientId + ' ID', 'alert-danger');
                    }
                    this.disabled.patientId = false;
                }).catch(err => {
                    this.disabled.patientId = false;
                });
            },

            selectPatientByFullData() {
                if (!this.patient.firstName || !this.patient.lastName || !this.patient.dateOfBirth) {
                    Alert.show('#full-patient-alert', 'Please enter a patient first name, last name and birth date.', 'alert-danger');
                    return false;
                }
                this.disabled.patient = true;
                this.$store.dispatch('isPatientExistsByData', this.patient).then(response => {
                    if (response.exists && response.data.patient_id) {
                        this.$router.push('/forms/patient-' + response.data.patient_id);
                    } else {
                        Alert.show('#full-patient-alert', 'Cannot find a patient ' + this.patient.firstName + ' ' + this.patient.lastName, 'alert-danger');
                    }
                    this.disabled.patient = false;
                }).catch(err => {
                    this.disabled.patient = false;
                });
            }
        }
    }
</script>

<style scoped>
    label.control-label {
        /*padding-top:5px;*/
        text-align: left !important;
        font-size: 24px !important;
    }

    p.delimiter {
        font-size: 24px !important;
    }

    .well {
        float: none;
    }
</style>
