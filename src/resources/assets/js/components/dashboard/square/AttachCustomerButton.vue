<template>
    <div>
        <button class="btn btn-primary btn-attach" data-toggle="modal"
                :data-target="`${'#attach-to-patient-' + customerId}`">
            Attach to Patient
        </button>

        <!-- Modal -->
        <div :id="`${'attach-to-patient-' + customerId}`" class="modal modal-vertical-center fade"
             data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Attach Square Customer <b>{{customerName}}</b> to Patient</h4>
                    </div>
                    <div class="modal-body">
                        <label>Patient Name or ID</label>
                        <div class="row">
                            <el-select v-model="selected_patient" :filterable="true" :remote="true"
                                       placeholder="Start Typing..."
                                       class="col-xs-12" :clearable="true" no-data-text="No Patients"
                                       :loading="patient_loading" :remote-method="getPatients">
                                <el-option
                                        v-for="patient in patients"
                                        :key="patient.value"
                                        :label="patient.label"
                                        :value="patient.value">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <form :action="`${'/dashboard/square/customers/' + customerId}`" method="post">
                            <input type="hidden" name="_method" value="put">
                            <input type="hidden" name="_token" :value="csrfToken">
                            <input type="hidden" name="patient_id" :value="selected_patient">
                            <button type="submit" class="btn btn-primary">Attach</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            customerId: {
                type: Number,
                required: true,
            },
            customerName: {
                type: String,
                required: true,
            },
            csrfToken: {
                type: String,
                required: true,
            },
        },

        data() {
            return {
                patients: [],
                selected_patient: null,
                patient_loading: false,
            };
        },

        methods: {
            getPatients(query) {
                this.loading = true;
                this.$store.dispatch('getPatientsForSquarePage', {
                    limit: 10,
                    q: query,
                }).then(response => {
                    if (response.status === 200) {
                        this.patients = response.data.patients;
                    } else {
                        this.patients = [];
                    }
                    this.loading = false;
                });
            },
        },
    }
</script>

<style scoped>
    button.btn-attach {
        margin-top: 22px;
        margin-bottom: 11px;
    }
</style>