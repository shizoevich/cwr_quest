import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        develop_mode:false,
        currentPatient: null,
        currentNote: null,
        isNoteBlank: true,
        signatureIsEmpty: true,
        provider_list: [],
        alertMessage: {},
        patientForms: [],
        currentProvider: null,
    },

    mutations: {
        setCurrentPatient(state, response){
            state.currentPatient = response;
        },

        setCurrentNote(state, response){
            state.currentNote = response;
            state.currentNote.statuses = {
                data_is_changed: false,
                saving: false,
                noErrors: true
            }
        },

        setNoteBlankStatus(state, response){
            state.isNoteBlank = response;
        },

        clearCurrentNote(state){
            state.currentNote = {}
        },

        resetStatuses(state) {
            if(!state.currentNote.statuses) {
                state.currentNote.statuses = {};
            }
            state.currentNote.statuses.data_is_changed = false;
            state.currentNote.statuses.saving = false;
        },

        setSignatureStatus (state, response) {
            state.signatureIsEmpty = response;
        },
        setProviderList(state, response) {
            state.provider_list = response;
        },

        appendAlertMessage(state, payload){
            state.alertMessage[payload.key] = payload.data;
        },

        setPatientFormsData(state, payload) {
            state.patientForms = payload;
        },

        setCurrentProvider(state, response) {
            state.currentProvider = response;
        },
    },

    actions: {
        getPatient({commit, dispatch}, payload){
            let url = '/forms/patient/' + payload.id;
            let params = {};
            if(typeof payload.with !== 'undefined') {
                params.with = payload.with;
            }

            return axios({
                method: 'post',
                url: url,
                params:params
            }).then(response => {
                commit('setCurrentPatient', response.data);
                return response;
            }).catch(error => {
                return error;
            })
        },

        getProviderList({commit, dispatch}){
            return axios({
                method: 'get',
                url: '/provider/all'
            }).then(response => {
                commit('setProviderList', response.data);
            }).catch(error => {
                console.log(error);
                dispatch('getProviderList');
            })
        },

        refreshCurrentNote({commit}, payload){
            return new Promise((resolve, reject) => {
                commit('setCurrentNote', payload)
                resolve();
            })
        },

        savePatientNote({dispatch},payload){
            return  axios({
                method: 'post',
                url: '/forms/save-first',
                data: payload,
            }).then(response => {
                return response.data;
            }).catch(error => {
                console.log(error);
            })
        },

        storeFirstForm({}, payload) {
            return axios({
                method: 'post',
                url: '/forms/save-first-form',
                data: payload,
            }).then(response => {
                return response;
            }).catch(error => {
                console.error(error);
                return error.response;
            })
        },

        storeSecondForm({}, payload) {
            return axios({
                method: 'post',
                url: '/forms/save-second-form',
                data: payload,
            }).then(response => {
                return response;
            }).catch(error => {
                console.error(error);
                return error.response;
            })
        },

        isPatientExists({}, payload) {
            return axios({
                method: 'post',
                url: '/forms/patient/check-exists-by-officeally-id',
                data: payload
            }).then(response => {
                return response.data;
            }).catch(error => {
                console.error(error);
            })
        },
        isPatientExistsByData({}, payload) {
            return axios({
                method: 'post',
                url: '/forms/patient/check-exists-by-data',
                data: payload
            }).then(response => {
                return response.data;
            }).catch(error => {
                console.error(error);
            })
        },

        isDoctorPasswordValid({}, payload) {
            return axios({
                method: 'post',
                url: '/forms/is-doctor-password-valid',
                data: payload
            }).then(response => {
                return response.data;
            }).catch(error => {
                console.error(error);
            })
        },
        addPatientCreditCard({}, payload) {
            return axios({
                method: 'post',
                url: '/forms/patient/'+payload.patient_id+'/add-credit-card',
                data: payload
            }).then(response => {
                console.log(response)

                return response;
            }).catch(error => {
                console.log(error)
                return error.response;
            });
        },

        getPatientById({commit, dispatch}, payload){
            let url = '/patient-forms/patient/' + payload.id;
            let params = {};
            if(typeof payload.with !== 'undefined') {
                params.with = payload.with;
            }

            return axios({
                method: 'post',
                url: url,
                params:params
            }).then(response => {
                commit('setCurrentPatient', response.data);
                return response;
            }).catch(error => {
                return error;
            })
        },

        getPatientFormsData({ commit }, payload) {
            return axios.get(`/api/public/patients/document-requests/${payload.hash}`)
                .then((response) => {
                    commit('setCurrentPatient', response.data.document_request.patient);
                    commit('setPatientFormsData', response.data.document_request.forms);
                    return response;
                }).catch(error => {
                    return error
                })
        },

        safeStorePatientForms({}, payload) {
            let url = `/api/safe/patients/${payload.patient_id}/forms/${payload.hash}`;

            return axios.post(url, payload.data, { headers: { 'Content-Type': 'multipart/form-data' }});
        },

        safeSendPatientFormsToEmail({}, payload) {
            let url = `/api/safe/patients/${payload.patient_id}/forms/${payload.hash}/send-to-email`;

            return axios.post(url, payload.data);
        },

        safeStoreCreditCard({}, payload) {
            let url = `/api/safe/patients/${payload.patient_id}/forms/${payload.hash}/credit-card`;

            return axios.post(url, payload.data);
        },

        checkSharedHash({}, payload) {
            return axios.get('/api/public/patients/shared-documents/' + payload.hash);
        },

        getSecuredPatientFormsData({ commit }, payload) {
            return axios.post(`/api/public/patients/shared-documents/${payload.hash}`, {password: payload.password})
                .then((response) => {
                    commit('setCurrentPatient', response.data.document_request.patient);
                    commit('setPatientFormsData', response.data.document_request.forms);
                    return response;
                })
        },

        getCurrentProvider({ commit }) {
            return axios({
                method: 'get',
                url: '/provider'
            }).then(response => {
                commit('setCurrentProvider', response.data);

                return response;
            }).catch(error => {
                console.log(error);

                return error.response;
            })
        },

        sendEmptyRequest() {
            return axios.post("/empty-request");
        },

        captureFrontendMessage({}, payload) {
            return axios.post(`/api/frontent-logs/capture-message`, payload);
        },
    },

    getters: {
        getAlertMessage: state => {
            return state.alertMessage
        }
    }
})