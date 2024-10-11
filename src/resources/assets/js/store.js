/**
 * Created by braginec_dv on 29.06.2017.
 */
import Vue from "vue";
import Vuex from "vuex";
import alertsList from "./components/alerts/alert-list.js";
import handleObjectToFormData from "./helpers/handleObjectToFormData";
import chart from "./store/chart/store";
import axios from "axios";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        chart: chart,
    },
    state: {
        develop_mode: false,
        appointments: {},
        offices: {},
        officesRooms: [],
        patients: [],
        providerPatients: [],
        importantPatients: [],
        providerMessages: [],
        providerTodayPatients: [],
        appointment_cancel_statuses: [],
        appointment_reschedule_statuses: [],
        appointment_reschedule_sub_statuses: [],
        statusesForTodayPatients: [],
        selectPatient: null,
        currentPatient: null,
        currentPatientNotes: null,
        currentPatientDocuments: null,
        patientFormsCount: null,
        patientCardTransactionCount: null,
        patientChartDataCount: null,
        currentNote: null,
        progressNoteCount: null,
        draftProgressNoteCount: null,
        missingProgressNoteCount: null,
        initialAssessmentCount: null,
        appointmentCount: null,
        appointmentVisitCreatedCount: null,
        appointmentCompletedCount: null,
        googleMeetingAppointmentCount: null,
        ringCentralAppointmentCount: null,
        cancelledAppointments: null,
        visitAverageDuration: null,
        paperNoteCount: null,
        currentProvider: null,
        searchedPatients: false,
        isNoteBlank: true,
        isNoteLoading: false,
        sidebar_loading_today_block: false,
        loading_appointments_tab: false,
        loading_transactions_tab: false,
        loading_patient_forms_tab: false,
        add_note_is_blocked: true,
        userRoleRequestPromise: null,
        isUserSupervisor: null,
        isUserAdmin: null,
        isUserSecretary: null,
        isUserPatientRelationManager: null,
        openAssessmentForm: null,
        assessmentFormsTemplates: {},
        document_types: [],
        validation_messages: {
            required: "Please make sure you have filled all the required fields.",
            incorrect_password: "You have entered incorrect PIN code.",
            try_again: "Error! Please try again.",
        },
        patient_statuses: [],
        patient_visit_frequencies: [],
        provider_list: [],
        patients_statistic_for_diagrams: {},
        availabilityCalendar: {
            appointments: [],
            workHours: [],
            weeks: [],
        },
        providers_score: [],
        patient_appointments: [],
        patient_transactions: [],
        patient_visit_created_appointments: [],
        patient_notifications: [],
        patient_notifications_count: '0',
        patient_forms: [],
        patient_forms_modal: [],
        alerts: alertsList,
        documents_preview: {},
        providers_dataset_for_tribute: [],
        is_read_only_mode: true,
        is_audit_mode: true,
        is_supervisor_mode: true,
        complete_appointment_data: [],
        complete_appointment_action: '',
        document_default_emails: [],
        document_default_faxes: [],
        is_password_outdated: false,
        documents_to_send: [],
        documents_to_send_reauthorization_requests: {},
        documents_to_send_discharge_summary: {},
        documents_to_send_initial_assessment: {},
        currentDocument: null,
        currentDocumentId: null,
        currentDocumentData: null,
        currentDocumentAppointmentId: null,
        currentDocumentEditingStatus: null,
        currentDocumentCommentUniqueId: null,
        currentDocumentSignature: null,

        patient_square_customers: [],
        attentionModalCopy: {},
        copiedWeekStatus: false,
        copiedWeekSuccess: false,
        confirmationStatus: true,
        confirmationLoad: false,
        maxTimeEvent: "22:00:00",
        loadingMaxTimeEvent: false,
        kaiserAppointments: [],
        kaiserAppointmentDetail: {
            call_logs: [],
            provider: {
                provider_name: "",
            },
            patient: {
                cell_phone: "",
            },
        },
        ehrAppointments: [],
        callLog: {},
        ringCentralNumbers: [],
        kaiserSites: [],
        tridiuumAppointmentsData: {
            isRestartingTridiuumParsers: true,
        },
        paginationMessages: null,
        paginationChart: null,
        paginationSearched: null,
        cancelToken: axios.CancelToken,
        searchProcessing: null,
        fetchingAppointmentData: null,
        patientForms: [],
        timelineFilters: {
            PatientComment: true,
            PatientPrivateComment: true,
            PatientAlert: true,
            InitialAssessment: true,
            PatientDocument: true,
            PatientNote: true,
            PatientPrivateDocument: true,
            CallLog: true,
            TelehealthSession: true,
        },
        allTimelineFiltersSelected: true,
        videoSessionAppointment: null,
        collectPaymentAppointment: null,
        collectPaymentDataLoading: false,
        timesheets: {
            billing_period_id: null,
            status: null,
        },
        availabilityTypes: [],
        availabilitySubtypes: [],
        treatmentModalities: [],
        transactionPurposes: [],
    },

    mutations: {
        setTimelineFilters(state, payload) {
            state.timelineFilters = payload;
        },
        setTimelineFiltersSelectedAll(state, payload) {
            state.allTimelineFiltersSelected = payload;
        },

        setSearchProcessing(state, payload) {
            state.searchProcessing = payload;
        },

        setFetchingAppointmentData(state, payload) {
            state.fetchingAppointmentData = payload;
        },

        appendArrayVal(state, payload) {
            for (let i = 0; i < payload.data.length; i++) {
                state[payload.key].push(payload.data[i]);
            }
        },

        setVal(state, data) {
            state[data.key] = data.val;
        },

        setPatientStatusesList(state, data) {
            state.patient_statuses = data;
        },

        setUserRoles(state, data) {
            state.isUserAdmin = data.isAdmin;
            state.isUserSupervisor = data.isSupervisorOrAdmin;
            state.isUserSecretary = data.isSecretary;
            state.isUserPatientRelationManager = data.isPatientRelationManager;
            state.is_audit_mode = data.isAudit;
        },

        setLoadingStatus(state, val) {
            state.isNoteLoading = val;
        },

        setAppointments(state, response) {
            state.appointments = response;
        },

        setProviderAvailabilityCalendarAppointments(state, response) {
            state.availabilityCalendar.appointments = response;
        },
        setProviderAvailabilityCalendarWorkHours(state, response) {
            state.availabilityCalendar.workHours = response;
        },
        addProviderAvailabilityCalendarWorkHours(state, response) {
            state.availabilityCalendar.workHours.push(response);
        },
        deleteProviderAvailabilityCalendarWorkHours(state, response) {
            state.availabilityCalendar.workHours =
                state.availabilityCalendar.workHours.filter((item) => {
                    return item.id != response.workTimeId;
                });
        },
        updateProviderAvailabilityCalendarWorkHours(state, response) {
            state.availabilityCalendar.workHours =
                state.availabilityCalendar.workHours.map((item) => {
                    if (item.id != response.id) {
                        return item;
                    } else {
                        return response;
                    }
                });
        },

        setProviderAvailabilityCalendarWeeks(state, response) {
            let data = [];
            $.each(response, (i, item) => {
                if (typeof data[item.week] === "undefined") {
                    data[item.week] = 1;
                }
            });
            state.availabilityCalendar.weeks = data;
        },
        setProviderAvailabilityWeeksCompleted(state, response) {
            state.alerts.week_completed.status = response.status;
        },

        setProviderMissingNotes(state, response) {
            state.alerts.provider_missing_notes.status =
                response.missing_note_count < 5;
        },

        setOffices(state, response) {
            state.offices = response;
        },

        setOfficesRooms(state, response) {
            state.officesRooms = response;
        },

        setCurrentPatient(state, response) {
            state.currentPatient = response;
        },

        setPatientFormsCount(state, response) {
            state.patientFormsCount = response;
        },

        setPatientCardTransactionCount(state, response) {
            state.patientCardTransactionCount = response;
        },

        setPatientChartDataCount(state, response) {
            state.patientChartDataCount = response;
        },

        setSelectPatient(state, response) {
            state.selectPatient = response;
        },

        setProviderPatients(state, response) {
            state.providerPatients = response;
        },
        setProviderTodayPatients(state, response) {
            state.providerTodayPatients = response;
        },

        setCurrentPatientNotes(state, response) {
            state.currentPatientNotes = response;
        },

        setCurrentPatientDocuments(state, response) {
            state.currentPatientDocuments = response;
        },

        setPatientProgressNoteCount(state, response) {
            state.progressNoteCount = response;
        },

        resetStatuses(state) {
            if (!state.currentNote.statuses) {
                state.currentNote.statuses = {};
            }
            state.currentNote.statuses.data_is_changed = false;
            state.currentNote.statuses.saving = false;
            state.currentNote.statuses.end_time = {};
            state.currentNote.statuses.confirm_diagnoses = false;
            state.currentNote.statuses.diagnoses_editable = true;
        },

        setCurrentNote(state, response) {
            let start_time = response.start_time;
            let tmp1 = start_time.split(":");
            let tmp2 = tmp1[1].split(" ");
            response.statuses = {
                data_is_changed: false,
                saving: false,
                confirm_diagnoses: false,
                diagnoses_editable: true,
                noErrors: true,
                end_time: {
                    h: parseInt(tmp1[0]),
                    m: parseInt(tmp1[0]),
                    meridian: tmp2[1],
                    origin: {
                        h: parseInt(tmp1[0]),
                        m: parseInt(tmp2[0]),
                        meridian: tmp2[1],
                    },
                },
            };
            state.currentNote = response;
        },

        setNoteBlankStatus(state, response) {
            state.isNoteBlank = response;
        },

        clearCurrentNote(state) {
            state.currentNote = {};
        },

        setCurrentProvider(state, response) {
            state.currentProvider = response;
            state.add_note_is_blocked = false;
        },

        setPatients(state, response) {
            state.patients = response;
        },

        setImportantPatients(state, response) {
            state.importantPatients = response;
        },

        setSearchedPatients(state, response) {
            if (!state.searchedPatients) {
                state.searchedPatients = response;
            } else {
                let statuses = Object.keys(response.patients);

                for (let i = 0; i < statuses.length; i++) {
                    let patientsByStatus = response.patients[statuses[i]];

                    if (statuses[i] in state.searchedPatients.patients) {
                        for (let j = 0; j < patientsByStatus.length; j++) {
                            state.searchedPatients.patients[statuses[i]].push(
                                patientsByStatus[j],
                            );
                        }
                    } else {
                        Vue.set(
                            state.searchedPatients.patients,
                            statuses[i],
                            patientsByStatus,
                        );
                    }
                }
            }
        },

        clearSearchedPatients(state) {
            if (state.searchedPatients.patients) {
                Vue.set(state.searchedPatients, "patients", {});
            }
        },

        setOpenAssessmentFormToModal(state, response) {
            state.openAssessmentForm = response;
        },

        setAssessmentFormsTemplates(state, response) {
            state.assessmentFormsTemplates = response;
        },

        setDocumentTypesTree(state, response) {
            state.document_types = response;
        },

        setProviderList(state, response) {
            state.provider_list = response;
        },
        setProvidersScore(state, response) {
            state.providers_score = response;
        },

        setProviderTherapistSurveyStatus(state, response) {
            state.alerts.therapist_survey.status = response.status;
        },

        setCurrentDocumentData(state, data) {
            state.currentDocumentData = data;
        },

        setCurrentDocumentEditingStatus(state, data) {
            state.currentDocumentEditingStatus = data;
        },

        setCurrentDocumentCommentUniqueId(state, data) {
            state.currentDocumentCommentUniqueId = data;
        },

        setCurrentDocumentId(state, id) {
            state.currentDocumentId = id;
        },

        setCurrentDocumentName(state, name) {
            state.currentDocument = name;
        },

        setCurrentDocumentSignature(state, signature) {
            state.currentDocumentSignature = signature;
        },

        setProviderOverlappedAppointments(state, response) {
            state.alerts.provider_overlapped_appointments.status =
                response.status;
            state.alerts.provider_overlapped_appointments.message =
                response.messages;
        },

        setProviderOverlappedAppointmentsCount(state, response) {
            state.alerts.provider_overlapped_appointments_count.status =
                response.status;
            state.alerts.provider_overlapped_appointments_count.message =
                response.messages;
        },

        setInvalidTridiuumCredentials(state, response) {
            state.alerts.invalid_tridiuum_credentials.status = response.status;
            state.alerts.invalid_tridiuum_credentials.message =
                response.messages;
        },

        setConfirmationStatus(state, status) {
            state.confirmationStatus = status;
        },

        setConfirmationLoad(state, load) {
            state.confirmationLoad = load;
        },

        setKaizerAppointments(state, appointments) {
            state.kaiserAppointments = appointments;
        },

        setKaiserAppointmentDetail(state, payload) {
            state.kaiserAppointmentDetail = payload;
        },

        setEHRAppointments(state, appointments) {
            state.ehrAppointments = appointments;
        },

        setCallLog(state, payload) {
            state.callLog = payload;
        },

        setRingCentralNumbers(state, payload) {
            state.ringCentralNumbers = payload;
        },

        setKaiserSites(state, payload) {
            state.kaiserSites = payload;
        },

        setTridiuumAppointmentsData(state, payload) {
            state.tridiuumAppointmentsData.isRestartingTridiuumParsers = payload.is_restarting_tridiuum_parsers;
        },

        setPatientForms(state, payload) {
            state.patientForms = payload;
        },

        setVideoSessionAppointment(state, payload) {
            state.videoSessionAppointment = payload;
        },

        setCollectPaymentAppointment(state, payload) {
            state.collectPaymentAppointment = payload;
        },

        setCollectPaymentDataLoading(state, payload) {
            state.collectPaymentDataLoading = payload;
        },

        setAvailabilityTypes(state, payload) {
            state.availabilityTypes = payload;
        },

        setAvailabilitySubtypes(state, payload) {
            state.availabilitySubtypes = payload;
        },
    },

    actions: {
        getProvidersDatasetForTribute({ commit }) {
            return axios({
                method: "get",
                url: "/provider/dataset-for-tribute",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "providers_dataset_for_tribute",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getUserRoles({ commit }) {
            if (this.state.userRoleRequestPromise) {
                return this.state.userRoleRequestPromise;
            }

            this.state.userRoleRequestPromise = axios({
                method: "get",
                url: "/user/role",
            })
                .then((response) => {
                    commit("setUserRoles", response.data);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    this.state.userRoleRequestPromise = null;
                    return error.response;
                });

            return this.state.userRoleRequestPromise;
        },

        getAppointments({ commit }) {
            return axios({
                method: "get",
                url: "/provider/appointments",
            })
                .then((response) => {
                    commit("setAppointments", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getChartCalendar({ commit }, payload) {
            return axios({
                method: "get",
                url: "/provider/availability-calendar/work-hours",
                params: payload,
            })
                .then((response) => {
                    // commit('setOffices', response.data);
                    return response;
                })
                .catch((error) => {
                    return error.response;
                });
        },

        getDoctorCalendarEventSource({ commit }, payload) {
            return axios({
                method: "post",
                url: "/api/providers/availability",
                data: payload,
            })
                .then((response) => {
                    // commit('setOffices', response.data);
                    return response;
                })
                .catch((error) => {
                    return error.response;
                });
        },

        getProviderInsurances({ commit }, payload) {
            return axios({
                method: "get",
                url: "/api/providers/availability/insurances",
                params: payload, 
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    return error.response;
                });
        },

        getInsuranceProviders({ commit }, payload) {
            return axios({
                method: "get",
                url: "/api/providers/availability/insurances/providers",
                params: payload, 
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    return error.response;
                });
        },

        getOffices({ commit }) {
            return axios({
                method: "get",
                url: "/offices",
            })
                .then((response) => {
                    commit("setOffices", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getOfficesRooms({ commit }) {
            return axios({
                method: "get",
                url: "/offices-rooms",
            })
                .then((response) => {
                    commit("setOfficesRooms", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatient({ commit }, payload) {
            commit("setCurrentPatient", null); // reset patient data

            return axios({
                method: "get",
                url:
                    "/patient/" +
                    payload.patientId +
                    "?with_tab_counts=1&with_missing_forms=1",
            })
                .then((response) => {
                    commit("setSelectPatient", response.data);
                    if (!payload.isSelected) {
                        commit("setCurrentPatient", response.data);
                    }
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientFormsCount({ commit }, payload) {
            return axios({
                method: "get",
                url: "/api/patients/" + payload.patientId + "/forms/count",
            })
                .then((response) => {
                    commit("setPatientFormsCount", response.data);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getPatientPreprocessedTransactionsCount({ commit }, payload) {
            return axios({
                method: "get",
                url:
                    "/patient/" +
                    payload.patientId +
                    "/preprocessed-transactions/count",
            })
                .then((response) => {
                    commit("setPatientCardTransactionCount", response.data);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getPatientChartDataCount({ commit }, payload) {
            return axios({
                method: "get",
                url:
                    "/patient/" +
                    payload.patientId +
                    "/patient-notes-with-documents/count",
            })
                .then((response) => {
                    commit("setPatientChartDataCount", response.data);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getPatientNotes({ commit }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/patient-notes",
            })
                .then((response) => {
                    commit("setCurrentPatientNotes", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientNote({ commit, store }, payload) {
            return axios({
                method: "get",
                url: "/patient/notes/" + payload,
            })
                .then((response) => {
                    commit("setCurrentNote", response.data);
                    commit("setCurrentDocumentCommentUniqueId", response.data.uniqueId);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getUnfinalizedNote({ }, payload) {
            return axios({
                method: "get",
                url: "/patient/unfinalized-note/" + payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientTimeline(
            { commit, state },
            { id, page = 1 },
        ){
            if (page === 1) {
                commit("setLoadingStatus", true);
            } else {
                commit("setChartLoadingStatus", true);
            }

            let url =
                "/patient/" + id + "/patient-notes-with-documents?page=" + page;
            for (let key in state.timelineFilters) {
                if (state.timelineFilters[key]) {
                    url += "&types[]=" + key;
                }
            }
            return axios({
                method: "get",
                url: url,
            })
                .then((response) => {
                    if (page === 1) {
                        commit("setCurrentPatientNotes", response.data.data);
                        commit("setLoadingStatus", false);
                    } else {
                        commit("appendArrayVal", {
                            key: "currentPatientNotes",
                            data: response.data.data,
                        });
                        commit("setChartLoadingStatus", false);
                    }
                    commit("setVal", {
                        key: "paginationChart",
                        val: response.data.meta.pagination,
                    });
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    commit("setLoadingStatus", false);

                    return error.response;
                });
        },

        getPatientNotesWithDocumentsPaginated(
            { commit, dispatch, state },
            { id, page = 1 },
        ) {
            if (page === 1) {
                commit("setLoadingStatus", true);
            } else {
                commit("setChartLoadingStatus", true);
            }

            let url =
                "/patient/" + id + "/patient-notes-with-documents?page=" + page;
            for (let key in state.timelineFilters) {
                if (state.timelineFilters[key]) {
                    url += "&types[]=" + key;
                }
            }
            return axios({
                method: "get",
                url: url,
            })
                .then((response) => {
                    if (page === 1) {
                        commit("setCurrentPatientNotes", response.data.data);
                        commit("setLoadingStatus", false);
                    } else {
                        commit("appendArrayVal", {
                            key: "currentPatientNotes",
                            data: response.data.data,
                        });
                        commit("setChartLoadingStatus", false);
                    }
                    commit("setVal", {
                        key: "paginationChart",
                        val: response.data.meta.pagination,
                    });
                    dispatch("getPatientNoteAndAppointmentCount", id);
                    if (!state.is_read_only_mode) {
                        dispatch("getDocumentsThumbnail", { patient_id: id });
                    }
                    let tribute = new Tribute({
                        values: state.providers_dataset_for_tribute,
                        selectTemplate: function (item) {
                            return (
                                '<span class="comment-mention" data-id="' +
                                item.original.id +
                                '" contenteditable="false">@' +
                                item.original.value +
                                "</span>"
                            );
                        },
                    });
                    window.setTimeout(() => {
                        tribute.attach(
                            document.querySelectorAll(
                                ".document-comment-input",
                            ),
                        );
                    }, 500);

                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    commit("setLoadingStatus", false);

                    return error.response;
                });
        },

        getPatientNotesWithDocuments({ commit, dispatch, state }, payload) {
            commit("setLoadingStatus", true);
            return axios({
                method: "get",
                url: "/patient/" + payload + "/patient-notes-with-documents",
            })
                .then((response) => {
                    commit("setCurrentPatientNotes", response.data.data);
                    commit("setLoadingStatus", false);
                    dispatch("getPatientNoteAndAppointmentCount", payload);
                    if (!state.is_read_only_mode) {
                        dispatch("getDocumentsThumbnail", {
                            patient_id: payload,
                        });
                    }
                    let tribute = new Tribute({
                        values: state.providers_dataset_for_tribute,
                        selectTemplate: function (item) {
                            return (
                                '<span class="comment-mention" data-id="' +
                                item.original.id +
                                '" contenteditable="false">@' +
                                item.original.value +
                                "</span>"
                            );
                        },
                    });
                    window.setTimeout(() => {
                        tribute.attach(
                            document.querySelectorAll(
                                ".document-comment-input",
                            ),
                        );
                    }, 500);

                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    commit("setLoadingStatus", false);

                    return error.response;
                });
        },

        getDocumentsThumbnail({ commit }, payload) {
            return axios({
                method: "post",
                url: "/documents/thumbnail",
                data: payload,
            })
                .then((response) => {
                    commit("setVal", {
                        key: "documents_preview",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientDocuments({ commit }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/patient-documents",
            })
                .then((response) => {
                    commit("setCurrentPatientDocuments", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getProviderPatients({ commit }) {
            return axios({
                method: "get",
                url: "/provider/patients",
            })
                .then((response) => {
                    commit("setProviderPatients", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getProviderMessages({ state, commit }, page = 1) {
            return axios({
                method: "get",
                url: `/provider/messages?page=${page}`,
            })
                .then((response) => {
                    if (response.data.meta.pagination) {
                        const pagination = response.data.meta.pagination;

                        commit("setVal", {
                            key: "paginationMessages",
                            val: pagination,
                        });

                        if (pagination.current_page > 1) {
                            commit("appendArrayVal", {
                                key: "providerMessages",
                                data: response.data.data,
                            });
                        } else {
                            commit("setVal", {
                                key: "providerMessages",
                                val: response.data.data,
                            });
                        }
                    }
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        setReadProviderMessage({ dispatch }, payload) {
            return axios({
                method: "post",
                url: "/provider/messages/set-read",
                data: payload,
            })
                .then((response) => {
                    dispatch("getProviderMessages");

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getProviderTodayPatients({ commit }) {
            commit("setVal", { key: "sidebar_loading_today_block", val: true });
            return axios({
                method: "get",
                url: "/provider/today-patients",
            })
                .then((response) => {
                    commit("setProviderTodayPatients", response.data.patients);
                    commit("setVal", {
                        key: "statusesForTodayPatients",
                        val: response.data.statuses,
                    });
                    commit("setVal", {
                        key: "sidebar_loading_today_block",
                        val: false,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    commit("setVal", {
                        key: "sidebar_loading_today_block",
                        val: false,
                    });
                    return error.response;
                });
        },

        refreshCurrentNote({ commit }, payload) {
            return new Promise((resolve, reject) => {
                commit("setCurrentNote", payload);
                resolve();
            });
        },

        getSearchedPatients(
            { commit, state },
            { query, page = 1, statuses = null },
        ) {
            if (state.searchProcessing != null) {
                state.searchProcessing();
            }

            if (page === 1) {
                commit("clearSearchedPatients");
            }

            let statusParam = "";
            if (statuses != null) {
                statusParam = "&statuses[]=" + statuses.join("&statuses[]=");
            }

            return axios({
                method: "get",
                url:
                    "/provider/patients-search?q=" +
                    query +
                    "&page=" +
                    page +
                    statusParam,
                cancelToken: new state.cancelToken(function executor(c) {
                    commit("setSearchProcessing", c);
                }),
            })
                .then((response) => {
                    commit("setSearchedPatients", response.data.data);
                    commit("setVal", {
                        key: "paginationSearched",
                        val: response.data.meta.pagination,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error;
                });
        },

        getCurrentProvider({ commit }) {
            return axios({
                method: "get",
                url: "/provider",
            })
                .then((response) => {
                    commit("setCurrentProvider", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientProgressNoteCount({ commit }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/patient-note-count",
            })
                .then((response) => {
                    commit("setPatientProgressNoteCount", response.data);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientNoteAndAppointmentCount({ commit }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/patient-note-and-appointment-count",
            })
                .then((response) => {
                    commit(
                        "setPatientProgressNoteCount",
                        response.data.patient_note_count,
                    );
                    commit("setVal", {
                        key: "paperNoteCount",
                        val: response.data.on_paper_count,
                    });
                    commit("setVal", {
                        key: "draftProgressNoteCount",
                        val: response.data.draft_patient_note_count,
                    });
                    commit("setVal", {
                        key: "missingProgressNoteCount",
                        val: response.data.missing_patient_note_count,
                    });
                    commit("setVal", {
                        key: "initialAssessmentCount",
                        val: response.data.initial_assessment_count,
                    });
                    commit("setVal", {
                        key: "appointmentCount",
                        val: response.data.appointment_count,
                    });
                    commit("setVal", {
                        key: "appointmentVisitCreatedCount",
                        val: response.data.appointment_visit_created_count,
                    });
                    commit("setVal", {
                        key: "appointmentCompletedCount",
                        val: response.data.appointment_completed_count,
                    });
                    commit("setVal", {
                        key: "googleMeetingAppointmentCount",
                        val: response.data.google_meeting_appointment_count,
                    });
                    commit("setVal", {
                        key: "ringCentralAppointmentCount",
                        val: response.data.ring_central_appointment_count,
                    });
                    commit("setVal", {
                        key: "cancelledAppointments",
                        val: response.data.cancelled_appointments,
                    });
                    commit("setVal", {
                        key: "visitAverageDuration",
                        val: response.data.visit_average_duration,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        savePatientNote({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/save-note",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        saveElectronicDocument({ state, dispatch }, payload) { 
            return axios({
                method: "post",
                url: "/patient/electronic-document", 
                data: {
                    id: state.currentDocumentId,
                    data: payload,
                },
            })
                .then((response) => {
                    dispatch("getPatient", {
                        patientId: state.currentPatient.id,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        updateElectronicDocument(store, payload) {
            return axios({
                method: "put",
                url:
                    "/patient/electronic-document/" +
                    store.state.currentDocumentId,
                data: {
                    data: payload,
                    comment_unique_id:
                        store.state.currentDocumentCommentUniqueId,
                },
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        deleteElectronicDocument({ state, dispatch }, payload) {
            return axios({
                method: "delete",
                url: "/patient/electronic-document/" + payload,
            })
                .then((response) => {
                    if (state.currentPatient.is_documents_uploading_allowed) {
                        dispatch("getPatient", {
                            patientId: state.currentPatient.id,
                        });
                    }

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        quickSavePatientNote({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/quick-save-note",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        updatePatientNote(store, payload) {
            console.log(payload);
            payload["comment_unique_id"] =
                store.state.currentDocumentCommentUniqueId;
            console.log(payload);

            return axios({
                method: "post",
                url: "/patient/update-note",
                data: payload,
            })
                .then((response) => {
                    console.log(response);

                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        //statistic
        getDoctorsStatistic({ }, payload) {
            return axios({
                method: "post",
                url: "/providers/statistic",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getTotalVcAndPnStatistic({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/providers/statistic/total-vc-and-pn",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getPatientsStatistic({ }, payload) {
            return axios({
                method: "post",
                url: "/patients/statistic",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getPatientsAssignedToTherapistsStatistic({ }, payload) {
            return axios({
                method: "post",
                url: "/patients/assigned-to-therapists/statistic",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getPatientAssignedToTherapistsStatisticForDiagrams({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/patients/assigned-to-therapists-statistic-for-diagrams",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getPatientsStatisticForDiagrams({ commit }) {
            return axios({
                method: "get",
                url: "/dashboard/patients/statistic-for-diagrams",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "patients_statistic_for_diagrams",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getPatientsWithoutAppointmentsStatistic({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/patients/wo-appointments/statistic",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getUpcomingReauthorizationRequests({ }, payload) {
            return axios({
                method: "post",
                url: "/patients/upcoming-reauthorization-requests",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        stopWatching({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/patients/statistic/stop-watching",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        stopWatchingForPatientsWoAppointments({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/patients-without-appointments/statistic/stop-watching",
                data: { patientId: payload },
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        isNoteEditingAllowed({ }, payload) {
            return axios({
                method: "get",
                url: "/patient/note-editing-status/" + payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        storeComment({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/comment",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        deleteComment({ }, payload) {
            return axios({
                method: "delete",
                url: "/patient/comment/" + payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        deleteLateCancelation({ }, payload) {
            return axios({
                method: "post",
                url: "/api/dashboard/salary/timesheets/delete/" + payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        storeDocumentComment({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/document-comment/store",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    throw error.response;
                });
        },

        sendDocumentByEmail({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/document-mail-send",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    throw error.response;
                });
        },

        sendDocumentByFax({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/document-fax-send",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    throw error.response;
                });
        },

        getAssessmentFormTemplates({ commit }) {
            return axios({
                method: "get",
                url: "/assessment-forms/templates",
            })
                .then((response) => {
                    commit("setAssessmentFormsTemplates", response.data);

                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getDocumentTypesTree({ commit }) {
            return axios({
                method: "get",
                url: "/documents/types-tree",
            })
                .then((response) => {
                    commit("setDocumentTypesTree", response.data);

                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        createNewPatientAssessmentForm({ commit }, payload) {
            return axios({
                method: "post",
                url: "/assessment-forms/create",
                data: payload,
            })
                .then((response) => {
                    console.log(response);
                    commit("setOpenAssessmentFormToModal", response.data);
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getAssessmentFormData({ commit }, payload) {
            return axios({
                method: "get",
                url: "/assessment-forms/" + payload,
            })
                .then((response) => {
                    console.log(response);
                    commit("setOpenAssessmentFormToModal", response.data);
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        savePatientAssessmentForm({ commit, dispatch }, payload) {
            return axios({
                method: "post",
                url: "/assessment-forms/" + payload.id + "/save",
            })
                .then((response) => {
                    commit("setOpenAssessmentFormToModal", null);
                    dispatch("getPatientNotesWithDocumentsPaginated", {
                        id: payload.patient_id,
                    });
                    dispatch("getPatient", { patientId: payload.patient_id });
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        getCurrentTime() {
            return axios({
                method: "get",
                url: "/api/time/current-time",
            })
                .then((response) => {
                    console.log(response.data);
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        getCurrentDateAndTime() {
            return axios({
                method: "get",
                url: "/api/time/current-date-and-time",
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        getAppointmentTimeByAppointmentDate({ }, payload) {
            return axios({
                method: "post",
                url: "/appointment/get-time-by-date",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        setDocumentType({ }, payload) {
            return axios({
                method: "post",
                url: "/documents/set-type",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        changeDocumentStatus({ }, payload) {
            return axios({
                method: "post",
                url: "/documents/change-status",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        deleteDocument({ }, payload) {
            return axios({
                method: "post",
                url: "/documents/delete",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    return error.response;
                });
        },

        getTime() {
            return axios({
                method: "get",
                url: "/api/time/now",
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        getDateParts() {
            return axios({
                method: "get",
                url: "/api/time/date-parts",
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        getDocumentTypeID({ }, payload) {
            return axios({
                method: "post",
                url: "/documents/get-type-id",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        getFirstAppointmentDateHasntNote({ }, payload) {
            return axios({
                method: "get",
                url: "/patient/first-appointment-date-hasnt-note?" + payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    console.error(err);
                    return err.response;
                });
        },

        softDeleteDocument({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/document/delete",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((err) => {
                    return err.response;
                });
        },

        getPatientStatuses({ commit }, payload) {
            return axios({
                method: "get",
                url:
                    "/patient/statuses" +
                    (payload ? "?" + $.param(payload) : ""),
            })
                .then((response) => {
                    commit("setPatientStatusesList", response.data);
                    return response;
                })
                .catch((err) => {
                    return err.response;
                });
        },

        getPatientVisitFrequenciesList({ commit }) {
            return axios
                .get("/patient/visit-frequencies")
                .then((response) => {
                    commit("setVal", {
                        key: "patient_visit_frequencies",
                        val: response.data,
                    });
                    return response;
                })
                .catch((err) => {
                    return err.response;
                });
        },

        getProviderList({ commit }) {
            return axios({
                method: "get",
                url: "/provider/all",
            })
                .then((response) => {
                    commit("setProviderList", response.data);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        deleteUnfinalizedNote({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/pn-unfinalized/delete",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getProviderAvailabilityCalendarAppointments({ commit }) {
            return axios({
                method: "get",
                url: "/provider/availability-calendar/appointments",
                // data:data
            })
                .then((response) => {
                    commit(
                        "setProviderAvailabilityCalendarAppointments",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },
        getProviderAvailabilityCalendarWorkHours({ commit }) {
            return axios({
                method: "get",
                url: "/provider/availability-calendar/work-hours",
            })
                .then((response) => {
                    commit(
                        "setProviderAvailabilityCalendarWorkHours",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        saveProviderAvailabilityCalendarWorkHours({ commit }, data) {
            return axios({
                method: "post",
                url: "/provider/availability-calendar/work-hours",
                data: data,
            })
                .then((response) => {
                    commit(
                        "addProviderAvailabilityCalendarWorkHours",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        updateProviderAvailabilityCalendarWorkHours({ commit }, data) {
            return axios({
                method: "put",
                url: "/provider/availability-calendar/work-hours",
                data: data,
            })
                .then((response) => {
                    commit(
                        "updateProviderAvailabilityCalendarWorkHours",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        deleteProviderAvailabilityCalendarWorkHours({ commit }, data) {
            return axios({
                method: "post",
                url: "/provider/availability-calendar/work-hours/delete",
                data: data,
            })
                .then((response) => {
                    commit(
                        "deleteProviderAvailabilityCalendarWorkHours",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getProviderAvailabilityCalendarWeeks({ commit }) {
            return axios({
                method: "get",
                url: "/provider/availability-calendar/weeks",
            })
                .then((response) => {
                    commit(
                        "setProviderAvailabilityCalendarWeeks",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        saveProviderAvailabilityCalendarWeeks({ commit }, data) {
            return axios({
                method: "post",
                url: "/provider/availability-calendar/weeks",
                data: data,
            })
                .then((response) => {
                    console.log(response);
                    commit(
                        "setProviderAvailabilityCalendarWeeks",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        checkProviderAvailabilityWeeksCompleted({ commit }) {
            return axios({
                method: "get",
                url: "/provider/availability-calendar/check-completed",
            })
                .then((response) => {
                    console.log(response.data);
                    commit(
                        "setProviderAvailabilityWeeksCompleted",
                        response.data,
                    );
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        checkProviderMissingNotes({ commit }) {
            return axios({
                method: "get",
                url: "/provider/missing-notes-count",
            })
                .then((response) => {
                    console.log(response.data);
                    commit("setProviderMissingNotes", response.data);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        allowEditingNote({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/allow-editing-note",
                data: { noteId: payload },
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        allowEditingDocument({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/allow-editing-document",
                data: { documentId: payload },
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        allowEditingAssessmentForm({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/allow-editing-assessment-form",
                data: { formId: payload },
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        getProvidersScore({ commit }) {
            return axios({
                method: "get",
                url: "/provider/score",
            })
                .then((response) => {
                    commit("setProvidersScore", response.data);
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        getPatientAppointments({ commit }, payload) {
            commit("setVal", { key: "loading_appointments_tab", val: true });
            return axios({
                method: "get",
                url: "/patient/" + payload + "/appointments",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "patient_appointments",
                        val: response.data,
                    });
                    commit("setVal", {
                        key: "loading_appointments_tab",
                        val: false,
                    });

                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        getTelehealthAppointments({ }, patientID) {
            return axios.get(
                `/api/patients/${patientID}/telehealth-appointments`,
            );
        },

        getPatientFormsForChart({ commit }, payload) {
            commit("setVal", { key: "loading_patient_forms_tab", val: true });
            return axios
                .get(`/api/patients/${payload.id}/forms`)
                .then((response) => {
                    commit("setVal", {
                        key: "patient_forms",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                })
                .finally(() => {
                    commit("setVal", {
                        key: "loading_patient_forms_tab",
                        val: false,
                    });
                });
        },

        getPatientFormsForModal({ commit }, patientID) {
            return axios
                .get(`/api/patients/${patientID}/forms`)
                .then((response) => {
                    commit("setVal", {
                        key: "patient_forms_modal",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        getFormsByPatientForModal({ commit }, id) {
            return axios
                .get("/api/patients/" + id + "/form-types")
                .then((response) => {
                    commit("setVal", {
                        key: "patient_forms_modal",
                        val: response.data,
                    });
                    return response.data;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        getPatientPreprocessedTransactions({ commit }, payload) {
            commit("setVal", { key: "loading_transactions_tab", val: true });
            return axios({
                method: "get",
                url: "/patient/" + payload + "/preprocessed-transactions",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "patient_transactions",
                        val: response.data,
                    });
                    commit("setVal", {
                        key: "loading_transactions_tab",
                        val: false,
                    });

                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        getPatientVisitCreatedAppointments({ commit }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/visit-created-appointments",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "patient_visit_created_appointments",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getPatientNotifications({ commit }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/notifications",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "patient_notifications",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.error(error);
                    return error.response;
                });
        },

        checkProviderTherapistSurveyStatus({ commit }) {
            return axios({
                method: "get",
                url: "/profile-status",
            })
                .then((response) => {
                    commit("setProviderTherapistSurveyStatus", response.data);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getPreviousNoteData({ }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/get-previous-note-data",
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        isReadOnlyMode({ commit }, payload) {
            return axios({
                method: "get",
                url: "/provider/has-patient/" + payload,
            })
                .then((response) => {
                    commit("setVal", {
                        key: "is_read_only_mode",
                        val: !response.data.has,
                    });
                    commit("setVal", {
                        key: "is_audit_mode",
                        val: response.data.audit,
                    });
                    commit("setVal", {
                        key: "is_supervisor_mode",
                        val: response.data.supervisor,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getCompleteAppointmentData({ commit, state }, payload) {
            if (state.fetchingAppointmentData != null) {
                state.fetchingAppointmentData();
            }

            commit("setVal", {
                key: "complete_appointment_action",
                val: payload.action || '',
            });

            return axios({
                method: "get",
                url:
                    "/patient/" +
                    payload.patient_id +
                    "/complete-appointment-data/" +
                    payload.appointment_id,
                cancelToken: new state.cancelToken(function executor(c) {
                    commit("setFetchingAppointmentData", c);
                }),
            })
                .then((response) => {
                    commit("setVal", {
                        key: "complete_appointment_data",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        completeAppointment({ commit, dispatch }, payload) {
            return axios({
                method: "post",
                url: "/appointment/complete",
                data: payload,
            })
                .then((response) => {
                    if (response.status === 201) {
                        commit("setVal", {
                            key: "complete_appointment_data",
                            val: [],
                        });
                        // dispatch('getProviderTodayPatients');
                        dispatch("getPatientAppointments", payload.patient_id);
                        dispatch(
                            "getPatientVisitCreatedAppointments",
                            payload.patient_id,
                        );
                    }
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getCancelAppointmentStatuses({ commit }) {
            return axios({
                method: "get",
                url: "/appointment/cancel-statuses",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "appointment_cancel_statuses",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getOtherCancelAppointmentStatuses({ commit }) {
            return axios({
                method: "get",
                url: "/appointment/other-cancel-statuses",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "appointment_cancel_statuses",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getRescheduleAppointmentStatuses({ commit }) {
            return axios({
                method: "get",
                url: "/appointment/reschedule-statuses",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "appointment_reschedule_statuses",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getRescheduleAppointmentSubStatuses({ commit }) {
            return axios({
                method: "get",
                url: "/appointment/reschedule-sub-statuses",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "appointment_reschedule_sub_statuses",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        rescheduleAppointment({ commit, dispatch }, payload) {
            return axios({
                method: "post",
                url: `/appointment/reschedule/${payload.id}`,
                data: payload,
            })
                .then((response) => {
                    if (response.status === 201) {
                        commit("setVal", {
                            key: "complete_appointment_data",
                            val: [],
                        });
                        dispatch("getPatientAppointments", payload.patient_id);
                    }
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        cancelAppointment({ commit, dispatch }, payload) {
            return axios({
                method: "post",
                url: "/appointment/cancel",
                data: payload,
            })
                .then((response) => {
                    if (response.status === 201) {
                        commit("setVal", {
                            key: "complete_appointment_data",
                            val: [],
                        });
                        // dispatch('getProviderTodayPatients');
                        dispatch("getPatientAppointments", payload.patient_id);
                    }
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        payAppointment({ }, payload) {
            return axios({
                method: "post",
                url: "/appointment/pay-co-pay",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        changeOnPaperAppointmentNote({ dispatch }, payload) {
            return axios({
                method: "post",
                url: "/appointment/change-on-paper-note",
                data: payload,
            })
                .then((response) => {
                    dispatch("getSearchedPatients", { query: "" });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getSystemMessages({ }, page) {
            return axios({
                method: "get",
                url: "/system-messages/get?page-name=" + page,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getDocumentDefaultEmails({ commit }) {
            return axios({
                method: "get",
                url: "/document-default-emails",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "document_default_emails",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    commit("setVal", {
                        key: "document_default_emails",
                        val: [],
                    });
                    return error.response;
                });
        },

        getDocumentDefaultFaxes({ commit }) {
            return axios({
                method: "get",
                url: "/document-default-faxes",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "document_default_faxes",
                        val: response.data,
                    });
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        deletePatientProviderRelationship({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/delete-patient-provider",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        addPatientProviderRelationship({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/add-patient-provider",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getAvailableProvidersForPatient({ }, payload) {
            return axios({
                method: "get",
                url: "/dashboard/patient/" + payload + "/available-providers",
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        hasInitialAssessmentForm({ }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/has-initial-assessment",
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        isPasswordOutdated({ commit }) {
            return axios({
                method: "get",
                url: "/user/is-password-outdated",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "is_password_outdated",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getSentEmailsStatistic({ }, payload) {
            return axios({
                method: "get",
                url: "/dashboard/statistic/sent-documents-by-email" + payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getSentFaxesStatistic({ }, payload) {
            return axios({
                method: "get",
                url: "/dashboard/statistic/sent-documents-by-fax" + payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        syncPatientWithOfficeAlly({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/sync-with-office-ally",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getDashboardProviderMissingNotes({ }, payload) {
            return axios({
                method: "get",
                url: "/provider/missing-notes",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getDashboardProviderMissingInitialAssessments({ }, payload) {
            return axios({
                method: "get",
                url: "/provider/missing-initial-assessments",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getDashboardReauthorizationRequests({ }, payload) {
            return axios({
                method: "get",
                url: "/provider/reauthorization-requests",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getDashboardMissingCopay({ }, payload) {
            return axios({
                method: "get",
                url: "/provider/missing-copay",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getDashboardProviderAssignedPatients({ }, payload) {
            return axios({
                method: "get",
                url: "/provider/assigned-patients",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getDashboardProviderInactivePatients({ }, payload) {
            return axios({
                method: "get",
                url: "/provider/inactive-patients",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        getVisitsDatasetForChart({ }, payload) {
            return axios({
                method: "get",
                url: "/provider/visits-dataset-for-chart",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.error(error);

                    return error.response;
                });
        },

        addPatientCreditCard({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/" + payload.patient_id + "/add-credit-card",
                data: payload,
            })
                .then((response) => {
                    console.log(response);

                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getDocumentsToSend({ commit }) {
            return axios({
                method: "get",
                url: "/dashboard/get-documents-to-send",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "documents_to_send",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getDocumentsToSendReauthorizationRequests({ commit }, payload) {
            console.log(payload);
            return axios({
                method: "get",
                url:
                    "/dashboard/get-documents-to-send/reauthorization-requests?" +
                    $.param(payload),
            })
                .then((response) => {
                    commit("setVal", {
                        key: "documents_to_send_reauthorization_requests",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getDocumentsToSendDischargeSummary({ commit }, payload) {
            console.log(payload);
            return axios({
                method: "get",
                url:
                    "/dashboard/get-documents-to-send/discharge-summary?" +
                    $.param(payload),
            })
                .then((response) => {
                    commit("setVal", {
                        key: "documents_to_send_discharge_summary",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getDocumentsToSendInitialAssessment({ commit }, payload) {
            console.log(payload);
            return axios({
                method: "get",
                url:
                    "/dashboard/get-documents-to-send/initial-assessment?" +
                    $.param(payload),
            })
                .then((response) => {
                    commit("setVal", {
                        key: "documents_to_send_initial_assessment",
                        val: response.data,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        markDocumentAsSent({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/mark-document-as-sent",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        approveSentDocument({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/approve-sent-document",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        syncVisits({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/salary/sync-visits",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },
        createVisit({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/appointments/create-visit",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getCompletedAppointments({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/appointments/completed",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getOfficeallyPaymentsForPosting({ }, payload) {
            return axios({
                method: "get",
                url: "/dashboard/posting?" + $.param(payload),
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        makePosting({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/posting",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        addAdjustment({ }, payload) {
            return axios({
                method: "post",
                url: "/dashboard/adjustment",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getElectronicDocument(store, payload) {
            return axios({
                method: "get",
                url: "/patient/electronic-document/" + payload.documentId,
            })
                .then((response) => {
                    let documentData = JSON.parse(response.data.document_data);

                    if (!/\d{2}\/\d{2}\/\d{4}/.test(documentData.date_of_service)) {
                        documentData.date_of_service = moment(
                            documentData.date_of_service,
                        ).format("MM/DD/YYYY"); 
                    }

                    if (
                        response.data.type.slug ===
                        "kp-initial-assessment-adult-pc" ||
                        response.data.type.slug ===
                        "kp-initial-assessment-adult-wh" ||
                        response.data.type.slug ===
                        "kp-initial-assessment-adult-la"
                    ) {
                        if (!documentData.selected_diagnoses_1) {
                            documentData.selected_diagnoses_1 =
                                response.data.diagnoses[1] || [];
                        }
                        if (!documentData.selected_diagnoses_2) {
                            documentData.selected_diagnoses_2 =
                                response.data.diagnoses[2] || [];
                        }
                        if (!documentData.selected_diagnoses_3) {
                            documentData.selected_diagnoses_3 =
                                response.data.diagnoses[3] || [];
                        }
                    } else {
                        if (!documentData.selected_diagnoses) {
                            documentData.selected_diagnoses =
                                response.data.diagnoses[1] || [];
                        }
                    }

                    store.commit(
                        "setCurrentDocumentName",
                        payload.documentUniqueId, 
                    );
                    store.commit("setCurrentDocumentId", response.data.id);
                    store.commit("setCurrentDocumentData", documentData);
                    store.commit(
                        "setCurrentDocumentEditingStatus",
                        response.data.isEditingAllowed,
                    );
                    store.commit(
                        "setCurrentDocumentCommentUniqueId",
                        response.data.uniqueId,
                    );
                    store.commit(
                        "setCurrentDocumentSignature",
                        response.data.signature,
                    );

                    setTimeout(function () {
                        $("#" + payload.documentUniqueId).modal("show");
                    }, 400);
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getProviderSignature(store) {
            return axios({
                method: "post",
                url: "/provider/get-signature",
                data: {
                    provider_id: store.state.currentProvider.id,
                },
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        sendSmsToUpdateSignature({ }, payload) {
            return axios({
                method: "post",
                url: `/users/send-sms-to-update-signature`,
                data: payload
            });
        },

        sendRemovePatientRequest({ }, payload) {
            return axios({
                method: "post",
                url: "/patient/removal-request",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        providerCancelRemovalRequest({ }, payload) {
            return axios({
                method: "delete",
                url: "/patient/removal-request",
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getActiveRemoveRequests({ }, payload) {
            return axios({
                method: "get",
                url: "/patient/" + payload + "/removal-requests/active",
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientsForSquarePage({ }, payload) {
            return axios({
                method: "get",
                url: "/dashboard/patients/for-square?" + $.param(payload),
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        getPatientSquareCustomers({ commit }, payload) {
            return axios({
                method: "get",
                url: "/dashboard/patients/" + payload + "/square/customers",
            })
                .then((response) => {
                    commit("setVal", {
                        key: "patient_square_customers",
                        val: response.data.customers,
                    });

                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    commit("setVal", {
                        key: "patient_square_customers",
                        val: [],
                    });

                    return error.response;
                });
        },

        detachSquareCustomerFromPatient({ }, payload) {
            return axios({
                method: "delete",
                url: "/dashboard/square/customers/" + payload.customer_id,
                data: { patient_id: payload.patient_id },
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        updatePatient({ }, payload) {
            const url = `/dashboard/patients/${payload.patientId}`;
            let requestPayload = {};
            if (payload.hasOwnProperty("email")) {
                requestPayload.email = payload.email;
            }
            if (payload.hasOwnProperty("statusId")) {
                requestPayload.status_id = payload.statusId;
            }

            if (payload.hasOwnProperty("cell_phone")) {
                requestPayload.cell_phone = payload.cell_phone;
                requestPayload.cell_phone_label = payload.cell_phone_label;
                requestPayload.additional_phones = payload.additional_phones;
                requestPayload.additional_phones_phone_type = 'cell_phone';
            }
            if (payload.hasOwnProperty("home_phone")) {
                requestPayload.home_phone = payload.home_phone;
                requestPayload.home_phone_label = payload.home_phone_label;
                requestPayload.additional_phones = payload.additional_phones;
                requestPayload.additional_phones_phone_type = 'home_phone';
            }
            if (payload.hasOwnProperty("work_phone")) {
                requestPayload.work_phone = payload.work_phone;
                requestPayload.work_phone_label = payload.work_phone_label;
                requestPayload.additional_phones = payload.additional_phones;
                requestPayload.additional_phones_phone_type = 'work_phone';
            }

            return axios({
                method: "put",
                url: url,
                data: requestPayload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);

                    return error.response;
                });
        },

        updatePatientLanguage({ }, payload) {
            const url = `/dashboard/patients/language/${payload.patient_id}`;
            return axios({
                method: "put",
                url: url,
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        updatePatientSecondEmail({ }, payload) {
            const url = `/dashboard/patients/secondary-email/${payload.patient_id}`;
            return axios({
                method: "put",
                url: url,
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        updatePatientVisitFrequency({ }, payload) {
            const url = `/dashboard/patients/visit-frequency/${payload.patient_id}`;
            return axios({
                method: "put",
                url: url,
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        addPatientAlert({ }, payload) {
            const url = "/api/patients/patient-alert";
            return axios({
                method: "post",
                url: url,
                data: payload,
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        confirmWeek({ commit }, payload) {
            const url = `/provider/availability-calendar/work-hours/confirm-week`;
            return axios
                .post(url, payload)
                .then((response) => {
                    commit("setVal", { key: "confirmationStatus", val: true });
                })
                .catch((errors) => {
                    commit("setVal", { key: "confirmationStatus", val: false });
                });
        },
        duplicateWeek({ commit }, payload) {
            return axios
                .post(
                    "/provider/availability-calendar/work-hours/copy",
                    payload,
                )
                .then((response) => {
                    commit("setVal", { key: "copiedWeekStatus", val: true });
                    commit("setVal", { key: "copiedWeekSuccess", val: true });
                });
        },

        checkCopyWeek({ commit }, payload) {
            return axios
                .get("/provider/availability-calendar/work-hours/check-copy", {
                    params: payload,
                })
                .then((response) => {
                    let answer = {
                        message: response.data.message,
                        status: true,
                    };
                    commit("setVal", {
                        key: "attentionModalCopy",
                        val: answer,
                    });
                })
                .catch((errors) => {
                    console.log(errors);
                    let answer = {
                        message: errors.response.data.message,
                        status: false,
                    };
                    commit("setVal", {
                        key: "attentionModalCopy",
                        val: answer,
                    });
                });
        },

        copiedWeekStatusUpdate({ commit }, payload) {
            commit("setVal", { key: "copiedWeekStatus", val: payload });
        },

        weekConfirmationStatus({ commit }, payload) {
            axios
                .get(
                    "/provider/availability-calendar/work-hours/check-week-confirmation",
                    {
                        params: payload,
                    },
                )
                .then((response) => {
                    commit("setConfirmationLoad", true);
                    commit("setConfirmationStatus", response.data.confirmed);
                });
        },

        // appointmentsNotifications({commit}, payload) {
        //     axios.get('/provider/availability-calendar/get-notifications', {
        //         params: payload
        //     }).then(response => {
        //         commit('appointmentsNotificationsLoad', true);
        //         console.log(response);
        //     });
        // },

        getMaxTimeEvent({ commit }, payload) {
            commit("setVal", {key: "loadingMaxTimeEvent", val: true});
            const url = `/provider/availability-calendar/get-event-max-time`;
            axios.get(url, { params: payload })
                .then((response) => {
                    commit("setVal", { key: "maxTimeEvent", val: response.data });
                })
                .finally(() => {
                    commit("setVal", {key: "loadingMaxTimeEvent", val: false});
                });
        },

        getKaizerAppointments({ commit }, payload) {
            const url = `/dashboard/appointments/kaiser/get`;
            return axios.get(url, { params: payload }).then((response) => {
                commit("setKaizerAppointments", response.data);
            });
        },

        getKaiserAppointmentDetail({ commit }, payload) {
            const url = `/dashboard/appointments/kaiser/get/${payload}`;
            return axios.get(url).then((response) => {
                commit("setKaiserAppointmentDetail", response.data);
            });
        },

        appointmentRingOutCall({ commit }, payload) {
            const url = "/api/ringcentral/appointments/ring-out";
            return axios.post(url, payload).then((response) => {
                commit("setCallLog", response.data.call_log);

                return response;
            });
        },

        patientRingOutCall({ commit }, payload) {
            return axios.post("/api/ringcentral/patients/ring-out", payload).then(response => {
                commit("setCallLog", response.data.call_log);

                return response;
            });
        },

        getRingCentralPatientCallDetails({ }, payload) {
            const { patient_id, patient_type } = payload;
            return axios.get(`/api/ringcentral/patients/${patient_id}/call-details?patient_type=${patient_type}`);
        },

        getRingOutCallByAppointment({ commit }, payload) {
            const url = `/api/ringcentral/appointments/${payload}/ring-out`;
            return axios.get(url);
        },

        cancelRingOutCall({ commit }, payload) {
            const url = `/api/ringcentral/ring-out/${payload}`;
            return axios.delete(url);
        },

        callPatient({ commit }, payload) {
            const url = `/dashboard/appointments/kaiser/ringout`;
            axios.post(url, payload).then((response) => {
                commit("setCallLog", response.data);
            });
        },

        updateCallLog({ commit, state }, payload) {
            const url = `/api/ringcentral/ring-out/${state.callLog.id}`;
            return axios.put(url, payload).then((response) => {
                commit("setCallLog", response.data);
            });
        },

        updateAppointment({ commit, state }, payload) {
            const url = `/dashboard/appointments/kaiser/update/${state.kaiserAppointmentDetail.id}`;
            return axios.put(url, payload).then((response) => {
                commit("setKaiserAppointmentDetail", response.data);
            });
        },

        getRingCentralNumbers({ commit }) {
            const url = `/api/ringcentral/account-numbers`;
            return axios.get(url).then((response) => {
                commit("setRingCentralNumbers", response.data);
            });
        },

        getKaiserSites({ commit }) {
            return axios
                .get("/dashboard/appointments/kaiser/sites")
                .then((response) => commit("setKaiserSites", response.data));
        },

        getTridiuumAppointmentsData({ commit }) {
            return axios
                .get("/api/secretaries-dashboard/tridiuum-appointments-data")
                .then((response) => commit("setTridiuumAppointmentsData", response.data));
        },

        restartTridiuumParsers({ commit }) {
            commit("setTridiuumAppointmentsData", { is_restarting_tridiuum_parsers: true });

            return axios.post("/api/secretaries-dashboard/restart-tridiuum-parsers");
        },

        getInvalidTridiuumCredentials({ commit }) {
            return axios
                .get("/dashboard/tridiuum/invalid-credentials")
                .then((response) =>
                    commit("setInvalidTridiuumCredentials", response.data),
                );
        },

        getPatientInfoDocumentBase64({ commit, state }, payload) {
            return axios.get("/patient/" + payload + "/patient-info/base64");
        },

        sendPatientInfoDocumentViaEmail({ commit, state }, payload) {
            return axios.post(
                "/patient/" + payload.patient_id + "/patient-info/send",
                payload,
            );
        },

        getPatientForms({ commit, state }, payload) {
            return axios.get("/provider/patient-forms/index", {
                params: payload,
            });
        },

        sendPatientFormsViaEmail({ }, payload) {
            let config = {
                headers: {
                    "Content-Type": "application/json",
                },
            };
            return axios.post(
                `/api/patients/${payload.id}/forms/email/send`,
                payload.data,
                config,
            );
        },

        sendPatientFormsViaSMS({ }, payload) {
            let config = {
                headers: {
                    "Content-Type": "application/json",
                },
            };
            return axios.post(
                `/api/patients/${payload.id}/forms/phone/send`,
                payload.data,
                config,
            );
        },

        sendPatientForms({ }, payload) {
            let config = {
                headers: {
                    "Content-Type": "application/json",
                },
            };
            return axios.post(
                `/api/patients/${payload.patient_id}/forms/send`,
                payload.data,
                config,
            );
        },

        sendPatientDocumentsByEmail({ }, payload) {
            let config = {
                headers: {
                    "Content-Type": "application/json",
                },
            };

            return axios.post(
                `/api/patients/${payload.patientId}/documents/send`,
                payload.data,
                config,
            );
        },

        downloadPatientDocuments({ }, payload) {
            return axios.get(
                `/api/patients/${payload.patientId}/documents/download`,
                {
                    params: {
                        documents: payload.data,
                    },
                    responseType: "blob",
                },
            );
        },

        generatePatientZipDocuments({ }, payload) {
            return axios.get(
                `/api/patients/${payload.patientId}/documents-zip/generate`,
                {
                    params: {
                        documents: payload.data,
                    },
                },
            );
        },

        downloadPatientZipDocuments({ }, payload) {
            return axios.get(
                `/api/patients/${payload.patientId}/documents-zip/download/${payload.fileName}`,
                {
                    responseType: "blob",
                },
            );
        },

        clearTabsData({ commit }) {
            commit("setVal", { key: "patient_notifications", val: [] });
            commit("setVal", { key: "patient_forms", val: [] });
            commit("setVal", { key: "patient_appointments", val: [] });
            commit("setVal", {
                key: "patient_visit_created_appointments",
                val: [],
            });
            commit("setVal", { key: "patient_transactions", val: [] });
        },

        getBase64PatientDocument({ }, payload) {
            let config = {
                headers: {
                    "Content-Type": "application/json",
                },
            };

            return axios.get(
                `/api/patients/${payload.patientId}/documents/${payload.documentId}`,
                config,
            );
        },

        getPastAppointments({ }) {
            return axios.get("/provider/past-appointments");
        },
        getDraftProgressNote({ }, payload) {
            return axios.get(`/api/patients/${payload.id}/patient-notes/draft`);
        },

        getProviderSalaryDetail({ }, payload) {
            return axios.get(
                `/dashboard/salary/${payload.id}/details${payload.query}`,
            );
        },

        storeVideoSession({ }, payload) {
            return axios.post(
                `/api/patients/${payload.patient_id}/video-session`,
                payload.data,
            );
        },

        getVideoSession({ }, { patient_id, video_session_id }) {
            return axios.get(
                `/api/patients/${patient_id}/video-session/${video_session_id}`,
            );
        },

        // @todo remove when "upheal" integration will be finished
        storeUphealVideoSession({ }, payload) {
            return axios.post(
                `/api/patients/${payload.patient_id}/upheal-video-session`,
                payload.data,
            );
        },

        updatePatientDiagnoses({ }, payload) {
            return axios.put(
                `/api/patients/${payload.patient_id}/diagnoses`,
                payload.data,
            );
        },

        getTableAppointments({ commit }) {
            return axios
                .get("/api/appointments")
                .then((response) => {
                    commit("setEHRAppointments", response);
                    return response;
                })
                .catch((error) => {
                    return error;
                });
        },

        getEHRAppointments({ commit }, payload) {
            return axios
                .get("/api/appointments", { params: payload })
                .then((response) => {
                    commit("setEHRAppointments", response);
                    return response;
                })
                .catch((error) => {
                    return error;
                });
        },

        removeEHRAppointments({ }, id) {
            return axios
                .delete(`/api/appointments/${id}`)
                .then((response) => response)
                .catch((error) => error);
        },

        getPatientForAppointments({ }, payload) {
            return axios
                .get("/api/patients", { params: payload })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    return error;
                });
        },

        createAppointment({ }, payload) {
            return axios
                .post("/api/appointments", payload)
                .then((response) => {
                    return response;
                })
                .catch(({ response }) => {
                    return response;
                });
        },

        updateAppointmentModal({ }, payload) {
            return axios
                .put(`/api/appointments/${payload.id}`, payload)
                .then((response) => {
                    return response;
                })
                .catch(({ response }) => {
                    return response;
                });
        },

        getProviderListForAppointments({ commit, dispatch }, payload) {
            return axios
                .get("/api/providers", { params: payload })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    return error;
                });
        },

        getOfficeListForAppointments({ }) {
            return axios.get("/api/offices");
        },

        getFreeOfficeRoom({ }, officeID) {
            return axios.get(`/api/offices/${officeID}/rooms`);
        },

        getAppointmentList({ }, payload) {
            return axios.get('/api/appointments', { params: payload });
        },

        getAppointmentDialogData({ }, appointmentID) {
            return axios.get(`/api/appointments/${appointmentID}`);
        },

        // @todo remove when "upheal" integration will be finished
        getUphealAppointmentDialogData({ }, appointmentID) {
            return axios.get(`/api/appointments/${appointmentID}?type=upheal`);
        },

        getStatusListForAppointments({ }) {
            return axios.get("/api/appointments/available-statuses");
        },

        getUnicPatientFormId() {
            return new Promise((resolve) => {
                let id = Math.floor(Math.random() * Math.floor(1000));
                resolve(id);
            });
        },

        setVideoSessionAppointment({ commit }, payload) {
            commit("setVideoSessionAppointment", payload);
        },

        setCollectPaymentAppointment({ commit }, payload) {
            commit("setCollectPaymentAppointment", payload);
        },

        setCollectPaymentDataLoading({ commit }, payload) {
            commit("setCollectPaymentDataLoading", payload);
        },

        getInsuranceList({ }, payload) {
            return axios.get("/api/insurances", { params: payload });
        },

        getPayersList({ }, payload) {
            return axios.get("/api/eligibility-payers", { params: payload });
        },

        getCPTList() {
            return axios.get("/api/insurance-procedures");
        },

        getPatientPreferredLanguageList() {
            return axios.get("/api/system/languages");
        },

        getTherapyTypeList() {
            return axios.get("/api/therapy-types");
        },

        createPatient({ }, payload) {
            let config = {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            };
            let bodyFormData = new FormData();
            for (const formDataKey in payload.formData) {
                handleObjectToFormData(
                    payload.formData[formDataKey],
                    bodyFormData,
                    `${formDataKey}`,
                );
            }
            return axios.post("/api/patients", bodyFormData, config);
        },

        updatedPatient({ }, payload) {
            return axios.patch(
                `/api/patients/${payload.patient_id}`,
                payload.formData,
            );
        },

        checkPatientLateCancellationPayment({ }, id) {
            return axios
                .get(`/patient/${id}/check-late-cancellation-payment`)
                .then((response) => response)
                .catch((error) => error);
        },

        getAvailabilityTypes({ commit }) {
            return axios
                .get("/api/availability/types")
                .then((response) => {
                    commit("setAvailabilityTypes", response.data);
                })
                .catch((error) => {
                    return error;
                });
        },

        getAvailabilitySubtypes({ commit }) {
            return axios
                .get("/api/availability/subtypes")
                .then((response) => {
                    commit("setAvailabilitySubtypes", response.data);
                })
                .catch((error) => {
                    return error;
                });
        },

        getPatientForEdit({ }, id) {
            return axios.get(`/api/patients/${id}`);
        },

        getBillingPeriodList() {
            return axios.get("/api/system/billing-periods");
        },

        getSalaryVisitsData({ }) {
            return axios.get("/api/salary/timesheet/visits");
        },

        getSalaryCancellationsData({ }) {
            return axios.get("/api/salary/timesheet/late-cancellations");
        },

        getSalarySupervisionsData({ }) {
            return axios.get("/api/salary/timesheet/supervisions");
        },

        saveStepVisits({ }, payload) {
            return axios.post("/api/salary/timesheet/visits", payload);
        },

        saveStepCancellation({ }, payload) {
            return axios.post(
                "/api/salary/timesheet/late-cancellations",
                payload,
            );
        },

        saveStepSupervisions({ }, payload) {
            return axios.post("/api/salary/timesheet/supervisions", payload);
        },

        saveStepSickTime({ }, payload) {
            return axios.post("/api/salary/timesheet/complete", payload);
        },

        initSickTimeBillingPeriods({ }) {
            return axios.get("/api/system/billing-periods/previous");
        },

        getTimesheetsList({ }, payload) {
            return axios.get("/api/dashboard/salary/timesheets", {
                params: payload,
            });
        },

        getTimesheetData({ }, { timeSheetId, show_only_therapist_changes = 0 }) {
            return axios.get(
                `/api/dashboard/salary/timesheets/${timeSheetId}`,
                {
                    params: {
                        show_only_therapist_changes:
                            show_only_therapist_changes,
                    },
                },
            );
        },

        acceptedTimesheet({ }, { id, name }) {
            return axios.post(
                `/api/dashboard/salary/timesheets/${name}/${id}/accept`,
            );
        },

        declinedTimesheet({ }, { id, name }) {
            return axios.post(
                `/api/dashboard/salary/timesheets/${name}/${id}/decline`,
            );
        },

        completeTimesheet({ }, { id, data }) {
            return axios.post(`/api/dashboard/salary/timesheets/${id}`, data);
        },

        getTimesheetConfirmation({ }) {
            return axios.get("/api/salary/timesheet-confirmation");
        },

        timesheetNotificationMarkAsViewed({ }) {
            return axios.post(
                "/api/salary/timesheet-notification/mark-as-viewed",
            );
        },

        timesheetNotificationRemindLater({ }) {
            return axios.post(
                "/api/salary/timesheet-notification/remind-later",
            );
        },

        sendEmptyRequest() {
            return axios.post("/empty-request");
        },

        removeEmailFromRejectList({ }, { patientId, email }) {
            return axios.post(`/api/remove-email-from-reject-list/${patientId}`, { email });
        },

        sendUnlockEditingRequest({ state }, { reason }) {
            return axios.post("/patient/notes/unlock-request", {
                patient_note_id: state.currentNote.id,
                reason,
            });
        },

        cancelUnlockEditingRequest({ state }, { reason }) {
            return axios.delete("/patient/notes/unlock-request", {
                data: {
                    patient_note_id: state.currentNote.id,
                    reason,
                },
            });
        },

        getCreditCards({ }, { patientId, forceSync }) {
            return axios.get(`/api/patients/${patientId}/credit-cards?force_sync=${forceSync}`);
        },

        getSquareCatalogItems({ }, { patientId }) {
            return axios.get(`/api/patients/${patientId}/catalog-items`);
        },

        getChargeableAppointments({ }, { patientId }) {
            return axios.get(`/api/patients/${patientId}/chargeable-appointments`);
        },

        chargePatient({ }, payload) {
            return axios.post(`/api/patients/${payload.patientId}/charge`, payload.data);
        },

        getTreatmentModalities({commit}, params) {
            commit("setVal", { key: "treatmentModalities", val: [] });

            return axios.get('/treatment-modalities', { params }).then(response => {
                commit("setVal", { key: "treatmentModalities", val: response.data });
                return response;
            });
        },

        getFeePerVisitForAppointment({}, payload) {
            return axios.get(`/appointment/${payload.appointmentId}/${payload.treatmentModalityId}/fee-per-visit`);
        },

        getFeePerVisitForProvider({}, payload) {
            return axios.get(`/provider/${payload.patientId}/${payload.treatmentModalityId}/fee-per-visit`);
        },
        
        captureFrontendMessage({}, payload) {
            return axios.post(`/api/frontent-logs/capture-message`, payload);
        },

        getUserInfo({ }) {
            return axios.get(`/user/meta`);
        },

        loadTridiuumInitialAssessment({},payload) {
            return axios.post(`/api/patients/${payload.patient_id}/load-tridiuum-initial-assessment`, payload);
        },
        
        getProviderComments({}, { providerId, params }) {
            return axios.get(`/api/provider/${providerId}/comment`, {
                params
            });
        },

        createProviderComment({}, payload) {
            const providerId = payload.get("provider_id");
            return axios.post(`/api/provider/${providerId}/comment`, payload);
        },
        
        checkPatientIsSynchronized({}, patientId) {
            return axios.get(`/api/patients/${patientId}/is-synchronized`);
        },

        getProviderAvailabilityCalendarTotalWorkHours({ commit }, payload) {
            return axios({
                method: "get",
                url: "/provider/availability-calendar/work-hours/total" + (payload ? "?" + $.param(payload) : ""),
            })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    console.log(error);
                    return error.response;
                });
        },

        getTransactionPurposes({ commit }) {
            return axios
                .get("/api/transaction-purposes")
                .then(({ data }) =>
                    commit("setVal", {
                        key: "transactionPurposes",
                        val: data,
                    }),
                );
        },

        getAppointmentDocumentDates({}, {patientId, isInitial}) {
            let url = `/patient/${patientId}/appointment-document-dates`;
            if (isInitial) {
                url += '?is_initial=1';
            }
            return axios.get(url);
        },

        getProvidersForSalaryQuota() {
            return axios.get("/dashboard/salary/providers-for-salary-quota");
        },

        calculateSalary({}, payload) {
            return axios.post("/dashboard/salary/calculate-salary", payload);
        },

        getTherapists({}, payload) {
            return axios
                .get("/dashboard/providers/api", { params: payload })
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    return error;
                });
        },

        getTherapistsFilterOptions({}) {
            return axios
                .get("/dashboard/providers/filter-options")
                .then((response) => {
                    return response;
                })
                .catch((error) => {
                    return error;
                });
        },
    },
});

