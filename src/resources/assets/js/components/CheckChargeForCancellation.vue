<template>
    <div class="d-flex flex-column align-items-center justify-content-center" style="margin-top: 50px">
        <div class="options form-group d-flex flex-column" style="width: fit-content">
            <label for="patient_status">Patient Status</label>
            <select class="form-control" id="patient_status" v-model="selectedPatientStatus" :disabled="fileIsLoading">
                <option v-for="status in patientStatuses" :key="status.id" :value="status.id">{{ status.status }}</option>
            </select>
            <div class="text-center" v-if="patients"><b>{{ showIndex + 1 }} of {{ patients.length }}</b></div>
            <div>
                <div>Go to patient with number</div>
                <div class="d-flex align-items-center">
                    <input class="form-control" v-model="patient_number" :disabled="fileIsLoading" />
                    <button v-if="showGoToPatientNumberButton" class="btn btn-primary btn-sm go-button"
                        @click="goToPatientNumberClick">Go</button>
                </div>
            </div>
            <div>
                <div>Go to patient with id</div>
                <div class="d-flex align-items-center">
                    <input class="form-control" v-model="patient_id" :disabled="fileIsLoading" />
                    <button v-if="showToPatientIdButton" class="btn btn-primary btn-sm go-button"
                        @click="goToPatientIdClick">Go</button>
                </div>
            </div>
        </div>
        <div v-if="patients && patients.length > 0">
            <embed :style="`opacity: ${fileIsLoading ? 0 : 1}; position: ${fileIsLoading ? 'absolute' : 'relative'}`"
                :src="getUrlFromDocumentName(patients[showIndex].aws_document_name)" width="500px" height="400px"
                @load="fileDownloaded" />
            <div v-if="fileIsLoading" class="loader-wrapper d-flex justify-content-center align-items-center">
                <pageloader class="loader"></pageloader>
            </div>
        </div>
        <div>
            <div class="text-center">Patient id: <b>{{ patients && patients[showIndex].patient_id }}</b></div>
            <div class="text-center">Patient document id: <b>{{ patients && patients[showIndex].patient_document_id }}</b></div>
            <div>
                <div class="text-center">Cancellation fee</div>
                <input class="form-control" v-model="newChargeCancellationFee" :disabled="fileIsLoading" />
            </div>
        </div>
        <div v-if="showPrevNextButton" class="buttons">
            <button class="btn btn-secondary btn-sm" @click="prevClick" :disabled="fileIsLoading">Prev</button>
            <button class="btn btn-secondary btn-sm" @click="nextClick" :disabled="fileIsLoading">Next</button>
        </div>
        <div v-else class="buttons">
            <button class="btn btn-danger btn-sm" @click="cancelClick">Cancel</button>
            <button class="btn btn-success btn-sm" @click="saveClick">Save</button>
        </div>
    </div>
</template>
  
<script>
import { Notification } from "element-ui";

export default {
    computed: {
        patientStatuses() {
            const newArray = [];
            this.$store.state.patient_statuses.forEach(status => {
                const newObj = { id: status.id, status: status.status };
                newArray.push(newObj);
            });
            newArray.unshift({ id: 'all', status: 'All' });
            return newArray;
        },

        showGoToPatientNumberButton() {
            return this.patient_number && this.patient_number !== this.showIndex + 1;
        },

        showToPatientIdButton() {
            if (!this.patients) {
                return false;
            }
            
            return this.patient_id && this.patient_id !== String(this.patients[this.showIndex].patient_id);
        },

        showPrevNextButton() {
            if (!this.patients) {
                return false;
            }

            return String(this.newChargeCancellationFee) === String(this.patients[this.showIndex].charge_for_cancellation_appointment);
        },
    },

    data() {
        return {
            selectedPatientStatus: 'all',
            patients: null,
            showIndex: 0,
            patient_number: null,
            patient_id: null,
            newChargeCancellationFee: null,
            fileIsLoading: false
        };
    },

    mounted() {
        this.$store.dispatch('getPatientStatuses');
        this.getDocumentsToCheck();
    },

    methods: {
        getDocumentsToCheck() {
            this.fileIsLoading = true;
            let url = `api/check-charge-for-cancellation`;
            if (this.selectedPatientStatus !== 'all') {
                url += `?statusId=${this.selectedPatientStatus}`;
            }
            axios.get(url)
                .then(res => {
                    this.patients = res.data;
                    this.initialChargeCancellationFee();
                })
                .catch((error) => {
                    if (error.response && error.response.status === 403) {
                        this.$router.push({name: '404'});
                    }
                });
        },

        fileDownloaded() {
            this.fileIsLoading = false;
        },

        nextClick() {
            this.showIndex++;
        },

        prevClick() {
            this.showIndex--;
        },

        getUrlFromDocumentName(name) {
            return `/patient/preview-document/${name}#page=3#toolbar=0`;
        },

        initialChargeCancellationFee() {
            this.newChargeCancellationFee = this.patients[this.showIndex].charge_for_cancellation_appointment;
        },

        cancelClick() {
            this.newChargeCancellationFee = this.patients[this.showIndex].charge_for_cancellation_appointment;
        },

        saveClick() {
            const payload = {
                cancellationFee: this.newChargeCancellationFee
            }
            const patientId = this.patients[this.showIndex].patient_id;
            axios.put(`api/check-charge-for-cancellation/${patientId}`, payload).then(res => {
                this.patients[this.showIndex].charge_for_cancellation_appointment = this.newChargeCancellationFee;
                this.nextClick();
                Notification.success({
                    title: "Success",
                    message: 'Cancellation fee updated successfully',
                    type: "success",
                })
            }).catch((err) => {
                if (err.response.status === 422) {
                    const message = err.response.data.cancellationFee[0];
                    Notification.error({
                        title: "Error",
                        message: message,
                        type: "error",
                    })
                } else {
                    Notification.error({
                        title: "Error",
                        message: 'Something went wrong',
                        type: "error",
                    })
                }
            });
        },

        goToPatientNumberClick() {
            if (Number(this.patient_number) > this.patients.length) {
                return Notification.error({
                    title: "Error",
                    message: `User with number ${this.patient_number} not found`,
                    type: "error",
                })
            }
            this.showIndex = Number(this.patient_number) - 1;
            this.patient_id = null;
            this.patient_number = null;
        },

        goToPatientIdClick() {
            const index = this.patients.findIndex(patient => String(patient.patient_id) === this.patient_id);

            if (index === -1) {
                return Notification.error({
                    title: "Error",
                    message: `User with id ${this.patient_id} not found`,
                    type: "error",
                })
            }

            this.showIndex = index;
            this.patient_id = null;
            this.patient_number = null;
        }
    },

    watch: {
        selectedPatientStatus() {
            this.getDocumentsToCheck();
            this.showIndex = 0;
        },

        showIndex() {
            this.fileIsLoading = true;
            this.initialChargeCancellationFee();
        }
    }

}
</script>
  
<style scoped lang="scss">
.options {
    position: absolute;
    top: 50px;
    left: 50px
}

.loader-wrapper {
    width: 500px;
    height: 400px;
}

.loader {
    border: none;
}

.buttons {
    margin-top: 10px;
}

.go-button {
    position: absolute;
    right: -40px;
}
</style>
  