<template>
    <div>
        <div id="page-sidebar">
            <div class="sidebar-wrap">
                <div class="sidebar-section sidebar-section-search">
                    <div class="sidebar-section-header all-patients sidebar-patient-control">
                        <div class="inline-block" style="padding-top:5px;padding-bottom:5px;">
                            All Patients <span v-if="searchedPatients" class="count">({{searchedPatients.patient_count}})</span>
                        </div>
                        <div>
                            <el-button 
                                v-if="!need_show_all_patients && !isAuditMode"    
                                class="btn-view-all"
                                :loading="loading_search_block"
                                type="primary"
                                size="small"
                                @click.prevent="all_patients_showed = true;"
                            >
                                View All
                            </el-button>
                            <el-button
                                v-if="need_show_all_patients && !isAuditMode"
                                class="btn-view-all"
                                type="danger"
                                size="small"
                                @click.prevent="cancelSearch()"
                            >
                                Back
                            </el-button>
                        </div>
                    </div>


                    <div class="sidebar-section-container" id="search-block">
                        <div class="form-group all-patients">
                        <span class="patient-list-btn patient-list-btn-danger cancel-search"
                              @click.prevent="cancelSearch()" v-show="searchQuery.length > 0">
                            <i class="fa fa-times"></i>
                        </span>
                            <input type="text" id="patient-fast-search" class="form-control search"
                                   placeholder="Patient Search"
                                   v-model="searchQuery" @keyup="sendSearchRequest(selectedStatusesArray)"
                                   @keydown="clearTimer">
                            <div class="patient-status-filter" v-if="need_show_all_patients">
                                <div class="inline-block">
                                    <label class="patient-status-checkbox" v-for="status in patient_statuses"
                                           :style="{color: getStatusColor(status)}">
                                        <input type="checkbox" v-model="selected_statuses[status.status]"
                                               @change="sendSearchRequest(selectedStatusesArray)">
                                        {{status.status}}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-section-container-scrollable" :class="{'active': need_show_all_patients}">
                            <div class="container-patients-list">
                                <div v-show="need_show_all_patients && patient_statuses_blocks_visible[key] !== false && selected_statuses[key] !== false"
                                     v-for="(statuses, key) in searchedPatients.patients"
                                     v-if="searchedPatients.patients[key].length">

                                    <div class="sidebar-section-header">
                                        {{ key }} Patients ({{ searchedPatients.statuses_count[key] }})
                                    </div>

                                    <div class="sidebar-section-container patients-container">
                                        <table class="table sidebar-patients-list sidebar-patients-list-search"
                                               :id="`${'status-' + key}`">
                                            <thead>
                                            <tr>
                                                <th>Patient Name</th>
                                                <th class="patient-dob-col">DOB</th>
                                            </tr>
                                            </thead>
                                            <tbody class="list">
                                            <tr 
                                                v-for="(searchedPatient, index) in statuses"
                                                :key="`searchedPatient.id-${index}`"
                                                :class="{active: $route.params.id == searchedPatient.id}"
                                                @click.prevent="openChart(searchedPatient.id)"
                                            >
                                                <td class="office-ally-id" v-show="false">
                                                    {{searchedPatient.office_ally_patient_id}}
                                                </td>
                                                <router-link :title="searchedPatient.full_name" tag="td"
                                                             :to="{ path: '/chart/'+ searchedPatient.id }"
                                                             class="patient-link text-preview-container">
                                                    <a :style="{color: '#'+searchedPatient.status_color}"
                                                       class="patient-name">
                                                        {{ searchedPatient.full_name }}
                                                    </a>
                                                </router-link>
                                                <td>{{getFormattedDate(searchedPatient.date_of_birth)}}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div v-if="paginationSearched && paginationSearched.next_page_url && need_show_all_patients"
                                     v-observe-visibility="!loading_search_block && !searchScrolled ? loadMoreSearched : false"
                                     class="load-more absolute"></div>

                                <div class="text-center sidebar-loader-wrapper-list" v-if="need_show_all_patients">
                                    <pageloader add-classes="sidebar-loader"
                                                v-if="loading_search_block && need_show_all_patients"></pageloader>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-section" v-if="messages && messages.length > 0" v-show="!need_show_all_patients">
                    <div class="sidebar-section-header">
                        Messages <span class="count" v-if="paginationMessages">({{ paginationMessages.total }})</span>
                        <span class="sidebar-loader-wrapper" v-if="loading_messages_block && !paginationMessages">
                        <pageloader add-classes="sidebar-loader"></pageloader>
                    </span>
                    </div>

                    <div class="sidebar-section-container" id="messages-list" v-if="messages.length">
                        <div class="patients-container" :class="{'max-15': isMoreThan15(messages)}">
                            <table class="table sidebar-patients-list">
                                <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th class="text-right">Message from</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="(message, index) in messages">
                                    <tr v-if="paginationMessages.next_page_url && index === messages.length - 1" class="load-more">
                                        <td colspan="3">
                                            <div v-observe-visibility="!messageScrolled ? loadMoreMessages : false"></div>
                                        </td>
                                    </tr>
                                    <tr v-if="message.model === 'PatientLeadComment'" class="active">
                                        <td
                                            class="patient-link text-preview-container"
                                            :title="fullName(message.comment.patient_lead)"
                                        >
                                            <a target="_blank" :href="`/new-patients-dashboard?selectedCard=${message.comment.patient_lead.inquiry.id}&comment=${message.comment.id}`" style="color: black"
                                            >
                                                <span>{{ fullName(message.comment.patient_lead) }} (Not created)</span>
                                            </a>
                                        </td>
                                        <td class="text-preview-container" :title="getProviderTitle(message.comment)">
                                            <a target="_blank" :href="`/new-patients-dashboard?selectedCard=${message.comment.patient_lead.inquiry.id}&comment=${message.comment.id}`" style="color: black">
                                                <span v-if="message.comment.provider">{{ message.comment.provider.provider_name }}</span>
                                                <span v-else-if="message.comment.provider_name">{{ message.comment.provider_name }}</span>
                                            </a>
                                        </td>
                                        <td style="padding-left: 0; padding-right: 0;">
                                            <span
                                                class="patient-list-btn patient-list-btn-danger"
                                                :class="{
                                                    disabled: message.isLoadingDelete,
                                                }"
                                                @click.prevent="deleteMessage(message)"
                                            >
                                                <i class="fa fa-times"></i>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-else :class="{active: ($route.params.id == message.comment.patient_id && $route.query.scrollto == (message.model + message.comment_id))}">
                                        <td
                                            class="patient-link text-preview-container"
                                            :title="fullName(message.comment.patient)"
                                        >
                                            <a :href="'/chart/' + message.comment.patient_id + '?scrollto=' + message.model + message.comment_id"
                                               :style="{color: '#' + statusColor(message.comment.patient)}"
                                               @click.prevent="openMessage(message)"
                                            >
                                                <span>{{ fullName(message.comment.patient) }}</span>
                                            </a>
                                        </td>
                                        <td
                                            class="text-preview-container"
                                            :title="getProviderTitle(message.comment)"
                                            @click.prevent="openMessage(message)"
                                        >
                                            <span v-if="message.comment.provider">{{ message.comment.provider.provider_name }}</span>
                                            <span v-else-if="message.comment.provider_name">{{ message.comment.provider_name }}</span>
                                        </td>
                                        <td style="padding-left: 0; padding-right: 0;">
                                            <span
                                                class="patient-list-btn patient-list-btn-danger"
                                                :class="{
                                                    disabled: message.isLoadingDelete,
                                                }"
                                                @click.prevent="deleteMessage(message)"
                                            >
                                                <i class="fa fa-times"></i>
                                            </span>
                                            </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                            <div class="text-center sidebar-loader-wrapper-list" v-if="loading_messages_block">
                                <pageloader add-classes="sidebar-loader"></pageloader>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sidebar-section" v-show="!need_show_all_patients">
                    <div class="sidebar-section-header">
                        Today&#39;s Appointments <span class="count" v-if="todayPatients.length">({{ todayPatients.length }})</span>
                        <span class="sidebar-loader-wrapper"
                              v-show="loading_today_block || sidebar_loading_today_block">
                        <pageloader add-classes="sidebar-loader"></pageloader>
                    </span>
                    </div>

                    <div class="sidebar-section-container p-0" v-if="todayPatients.length">
                        <div class="patients-container">
                            <table class="table sidebar-patients-list">
                                <tbody>
                                <tr v-for="(todayPatient, index) in todayPatients" :key="`todayPatient.id-${index}`" :class="{active: $route.params.id == todayPatient.id}">
                                    <router-link
                                        tag="td"
                                        :to="{ path: '/chart/' + todayPatient.id }"
                                        :title="fullName(todayPatient)"
                                        class="patient-link text-preview-container"
                                        @click.prevent="openChart(todayPatient.id)"
                                    >
                                        <a
                                            :style="{color: '#' + todayPatient.status_color, display: 'inline-block'}"
                                            :class="{'appt-complete': isAppointmentComplete(todayPatient)}"
                                        >
                                            {{ fullName(todayPatient) }}
                                        </a>
                                    </router-link>
                                    <td style="width:90px;"
                                        :class="{'appt-complete': isAppointmentComplete(todayPatient)}"
                                        @click.prevent="openChart(todayPatient.id)">
                                        {{todayPatient.appointment_time}}
                                    </td>
                                    <td class="appt-buttons-container" v-if="!isAppointmentComplete(todayPatient)"  style="white-space: nowrap;">
                                    <span class="patient-list-btn patient-list-btn-primary"
                                          @click.prevent="startVideoSession(todayPatient)"
                                          title="Start Telehealth Session"
                                          :class="{disabled: (sidebar_loading_today_block)}"
                                          v-if="!isUserAdmin">
                                        <i class="fa fa-phone" style="font-size:14px;"></i>
                                    </span>
                                    <span class="patient-list-btn patient-list-btn-success"
                                        @click.prevent="setCompleteStatus(todayPatient)"
                                        :class="{disabled: (sidebar_loading_today_block)}"
                                        title="Complete Appointment">
                                        <i class="fa fa-check"></i>
                                    </span>
                                    <span class="patient-list-btn patient-list-btn-warning"
                                        @click.prevent="setRescheduleStatus(todayPatient)"
                                        :class="{disabled: (sidebar_loading_today_block)}"
                                        title="Reschedule Appointment">
                                        <i class="fa fa-refresh"></i>
                                    </span>
                                    <span class="patient-list-btn patient-list-btn-danger"
                                        @click.prevent="setCancelStatus(todayPatient)"
                                        :class="{disabled: (sidebar_loading_today_block)}"
                                        title="Cancel Appointment">
                                        <i class="fa fa-times"></i>
                                    </span>
                                    </td>
                                    <td class="appt-status-container"
                                        v-else-if="isAppointmentComplete(todayPatient) === 1"
                                        @click.prevent="openChart(todayPatient.id)">
                                        <span class="complete-appt-status">completed</span>
                                    </td>
                                    <td class="appt-status-container"
                                        v-else-if="isAppointmentComplete(todayPatient) === 2"
                                        @click.prevent="openChart(todayPatient.id)">
                                        <span class="cancel-appt-status">canceled</span>
                                    </td>
                                    <td class="appt-status-container"
                                        v-else-if="isAppointmentComplete(todayPatient) === 3"
                                        @click.prevent="openChart(todayPatient.id)">
                                        <span class="reschedule-appt-status">rescheduled</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <CreateAppointmentModal
            v-if="isShowAppointmentDialog"
            dialog-title="Schedule appointment"
            :visibleAppointmentModal="isShowAppointmentDialog"
            :created-patient="appointmentPatient"
            :isEditable="false"
            :isCreated="true"
            @close="closeAppointmentDialog"
        />
    </div>
</template>

<script>
    import CreateAppointmentModal from "./appointments/CreateAppointmentModal";

    export default {
        components: {
            CreateAppointmentModal
        },

        data() {
            return {
                importantActive: false,
                todayActive: false,
                searchActive: false,
                searchQuery: '',
                loading_today_block: false,
                loading_messages_block: false,
                loading_search_block: false,
                all_patients_showed: false,
                listjs: {},
                patient_statuses_blocks_visible: {},
                selected_statuses: {},
                inputDelay: 1000,
                typingTimer: null,
                messageScrolled: false,
                searchScrolled: false,
                isShowAppointmentDialog: false,
                appointmentPatient: {},

            }
        },

        computed: {
            patient_statuses() {
                return this.$store.state.patient_statuses;
            },

            sidebar_loading_today_block() {
                return this.$store.state.sidebar_loading_today_block;
            },

            need_show_all_patients() {
                return (this.searchQuery.trim().length > 0 || this.all_patients_showed);
            },

            statusesForTodayPatients() {
                return this.$store.state.statusesForTodayPatients;
            },

            messages() {
                return this.$store.state.providerMessages;
            },

            appointments() {
                return this.$store.state.appointments;
            },

            patients() {
                return this.$store.state.patients;
            },
            todayPatients() {
                return this.$store.state.providerTodayPatients;
            },

            providerPatients() {
                return this.$store.state.providerPatients;
            },

            patientNotes() {
                return this.$store.state.currentPatientNotes;
            },

            patientNoteCount() {
                return this.$store.state.progressNoteCount;
            },

            offices() {
                return this.$store.state.offices;
            },

            currentPatient() {
                return this.$store.state.currentPatient;
            },

            searchedPatients() {
                return this.$store.state.searchedPatients;
            },
            isUserAdmin() {
                return this.$store.state.isUserAdmin;
            },
            isAuditMode() {
                return this.$store.state.is_audit_mode;
            },
            isSelfIsProvider() {
                return !this.$store.state.isUserAdmin;
            },
            paginationMessages() {
                return this.$store.state.paginationMessages;
            },
            paginationSearched() {
                return this.$store.state.paginationSearched;
            },
            selectedStatusesArray() {
                let availableStatuses = Object.keys(this.selected_statuses);
                let selectedStatusesArr = [];
                for (let i = 0; i < availableStatuses.length; i++) {
                    if (this.selected_statuses[availableStatuses[i]]) {
                        selectedStatusesArr.push(availableStatuses[i]);
                    }
                }
                return selectedStatusesArr;
            },
        },

        mounted() {
            this.$store.dispatch('getOtherCancelAppointmentStatuses');
            this.$store.dispatch('getRescheduleAppointmentStatuses');
            this.$store.dispatch('getRescheduleAppointmentSubStatuses');
            this.$store.dispatch('getCurrentProvider').then(() => {
              if(this.$store.state.currentProvider && this.$store.state.currentProvider.id) {
                window.Echo.private(`providers.${this.$store.state.currentProvider.id}.appointments`)
                  .listen('.appointments.today.updated', () => {
                    this.$store.dispatch('getProviderTodayPatients');
                  });
              } else {
                //admin channel
                window.Echo.private('appointments')
                  .listen('.appointments.today.updated', () => {
                    this.$store.dispatch('getProviderTodayPatients');
                  });
              }
            });
            this.$store.dispatch('getDocumentDefaultEmails');
            this.$store.dispatch('getDocumentDefaultFaxes');
            this.$store.dispatch('getPatientStatuses').then(() => {
                for (let i in this.patient_statuses) {
                    this.$set(this.selected_statuses, this.patient_statuses[i].status, true);
                }
            });
            this.loading_today_block = true;
            this.loading_messages_block = true;
            this.loading_search_block = true;
            this.$store.dispatch('getProviderMessages')
                .then(() => {
                    this.loading_messages_block = false;
                });
            this.$store.dispatch('getProviderTodayPatients')
                .then(() => {
                    this.loading_today_block = false;
                });
            this.$store.dispatch('getSearchedPatients', {query: ''}).then(() => {
                this.loading_search_block = false;
            });
            this.$store.dispatch('getUserRoles');
        },

        methods: {
            startVideoSession(patient) {
                this.$emit('showTelehealthChannelModal', {
                  patient: {
                    id: patient.id,
                    first_name: patient.first_name,
                    last_name: patient.last_name,
                    email: patient.email,
                    secondary_email: patient.secondary_email,
                    cell_phone: patient.cell_phone,
                  },
                  appointment: {
                    id: patient.a_id,
                  }
                });
                // patient.patientId = patient.id;
                // patient.isSelected = true;
                // this.$store.dispatch('getPatient', patient).then(() => { // For display visit co pay in patients form collapse component
                //     this.$store.dispatch('setVideoSessionAppointment', {
                //         patient: patient,
                //         appointment_id: patient.a_id
                //     });
                // })
            },

            sendSearchRequest(statuses = null) {
                clearTimeout(this.typingTimer);
                let self = this;
                self.loading_search_block = true;
                this.typingTimer = setTimeout(() => {
                    let payload = {
                        query: self.searchQuery,
                        statuses: statuses,
                    };
                    if (statuses != null) {
                        payload.page = 1;
                    }
                    self.$store.dispatch('getSearchedPatients', payload)
                        .then((response) => {
                            if (!window.axios.isCancel(response)) {
                                self.loading_search_block = false;
                            }
                        })
                }, this.inputDelay);
            },

            loadMoreSearched(isVisible, entry) {
                if (isVisible) {
                    this.searchScrolled = true;
                    this.loading_search_block = true;
                    this.$store.dispatch('getSearchedPatients', {
                        query: this.searchQuery,
                        page: this.paginationSearched.current_page + 1,
                        statuses: this.selectedStatusesArray
                    })
                        .then((response) => {
                            if (!window.axios.isCancel(response)) {
                                this.loading_search_block = false;
                                this.searchScrolled = false;
                            }
                        })
                }
            },

            clearTimer() {
                clearTimeout(this.typingTimer);
            },

            loadMoreMessages(isVisible, entry) {
                if (isVisible) {
                    this.messageScrolled = true;
                    this.loading_messages_block = true;
                    this.$store.dispatch('getProviderMessages', this.paginationMessages.current_page + 1)
                        .then(() => {
                            this.messageScrolled = false;
                            this.loading_messages_block = false;
                        });
                }
            },

            getStatusColor(status) {
                if (this.withColors === 'true') {
                    return '#' + status.hex_color;
                }
                return '#000000';
            },

            cancelSearch() {
                this.searchQuery = "";
//                if(this.listjs && this.searchedPatients && this.searchedPatients.patient_count) {
//                    for(let item in this.listjs) {
//                        this.listjs[item].search();
//                    }
//                }
                this.all_patients_showed = false;
            },

            getProviderTitle(comment) {
                if (comment.provider) {
                    return comment.provider.provider_name;
                } else {
                    return comment.provider_name;
                }
            },

            cutPatientName(name, max) {
                if (name.length > max && $('#page-sidebar').width() <= 370) {
                    return name.substr(0, max - 3) + '...';
                }
                return name;
            },

            isMoreThan15(patients) {
                return patients.length > 10;
            },

            openChart(id) {
                this.$router.push('/chart/' + id);
            },

            openMessage(message) {
                this.openChart(message.comment.patient_id + '?scrollto=' + message.model + message.comment_id);
            },

            deleteMessage(message) {
                if (message.isLoadingDelete) {
                    return;
                }

                message.isLoadingDelete = true;

                const payload = {
                    mention_id: message.id,
                };

                this.$store.dispatch('setReadProviderMessage', payload);
            },

            getFormattedDate(date) {
                if (date) {
                    return this.$moment(date).format('MM/DD/YYYY');
                }

                return '-';
            },

            setCompleteStatus(patient) {
                if (this.sidebar_loading_today_block) {
                    return false;
                }
                this.$emit('confirm-patient', patient);
                $('#confirm-telehealth').modal('show');
            },

            setRescheduleStatus(patient) {
                if (this.sidebar_loading_today_block) {
                    return false;
                }
                this.$store.dispatch('getCompleteAppointmentData', {
                    appointment_id: patient.a_id,
                    patient_id: patient.id,
                    action: 'reschedule'
                }).then(response => {
                    if (response.status === 200) {
                        $('#reschedule-appointment').modal('show');
                    }
                });
            },

            setCancelStatus(patient) {
                if (this.sidebar_loading_today_block) {
                    return false;
                }
                this.$store.dispatch('getCompleteAppointmentData', {
                    appointment_id: patient.a_id,
                    patient_id: patient.id,
                    action: 'cancel'
                }).then(response => {
                    if (response.status === 200) {
                        $('#cancel-appointment').modal('show');
                    }
                });
            },

            fullName(app) {
                return app.first_name + ' ' + app.last_name + ' ' + app.middle_initial;
            },

            statusColor(patient) {
                return (patient && patient.status) ? patient.status.status_color : '';
            },

            getFormattedTime(time) {
                return this.$moment(time * 1000).format('kk mm A');
            },

            isAppointmentComplete(patient) {
                let status_id = parseInt(patient.appt_status);
                if (patient.start_completing_date && patient.new_status_id) {
                    status_id = parseInt(patient.new_status_id);
                }
                if (this.statusesForTodayPatients.complete.indexOf(status_id) !== -1) {
                    return 1;
                } else if (this.statusesForTodayPatients.cancel.indexOf(status_id) !== -1) {
                    return 2;
                } else if (this.statusesForTodayPatients.reschedule.indexOf(status_id) !== -1) {
                    return 3;
                }
                return 0;
            },

            openAppointmentDialog(patient) {
                this.appointmentPatient = patient;
                this.isShowAppointmentDialog = true;
            },

            closeAppointmentDialog() {
                this.isShowAppointmentDialog = false;
            }
        },
        watch: {
            searchQuery() {
                if (this.searchQuery.length === 0) {
                    this.sendSearchRequest(this.selectedStatusesArray);
                }
            },
            isAuditMode(value) {
              if(value) {
                this.all_patients_showed = value;
              }
            }
        }
    }
</script>

<style scoped>
    .container-patients-list {
        position: relative;
    }

    .sidebar-loader-wrapper-list {
        padding: 4px 0 8px;
        min-height: 39px;
    }

    .sidebar-patients-list {
        padding: 10px 0;
    }

    .sidebar-patients-list a {
        margin-bottom: 0;
        display: block;
        width: 100%;
    }

    .sidebar-patients-list a.active,
    .sidebar-patients-list a:active {
        list-style: none !important;
    }

    .sidebar-loader-wrapper {
        position: absolute;
        right: 20px;
    }

    .sidebar-loader {
        max-height: 27px;
        max-width: 27px;
    }

    .sidebar-patients-list-search {
        padding-top: 0;
        padding-bottom: 0;
    }

    .max-15 {
        /*height: 670px;*/
        height: 460px;
    }

    #search-block {
        position: relative;
    }

    #search-block .max-15 {
        overflow-y: auto;
        padding: 0 10px;
    }

    .complete-appt-status {
        color: #5cb85c;
        font-weight: bold;
    }

    .reschedule-appt-status {
        color: #FDBF41;
        font-weight: bold;
    }

    .cancel-appt-status {
        color: #ce3c3e;
        font-weight: bold;
    }

    .has-alert a {
        margin-left: -4px;
    }

    .has-alert:before {
        content: '';
        border-left: 3px solid red;
        position: relative;
        left: -10px;
    }

    .load-more td {
        padding: 0 !important;
        border: none !important;
        line-height: 0 !important;
    }

    @media (max-width: 929px) {
        .sidebar-section-container-scrollable.active {
            overflow-y: auto;
            height: 50vh;
        }
    }

    .sidebar-patient-control {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn-view-all {
        margin-left: 5px;
        min-width: 75px;
    }
</style>