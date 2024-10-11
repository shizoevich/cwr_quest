<template>
    <div>
        <div id="page-sidebar" class="filters-sidebar">
            <div class="sidebar-wrap">
                <div class="sidebar-section sidebar-section-collapse">
                    <div class="sidebar-section-container">
                        <el-collapse>
                            <el-collapse-item title="Filters" name="filters">
                                <filters-list/>
                            </el-collapse-item>
                        </el-collapse>
                    </div>
                </div>
                <div class="sidebar-section sidebar-section-column">
                    <div class="sidebar-section-header">
                        Filters
                    </div>
                    <div class="sidebar-section-container">
                        <filters-list @setFilter="setFilter" @changeFilter="isLoading" @updateAppointmentStatistic="updateAppointmentStatistic"/>
                    </div>
                </div>
            </div>
        </div>
        <div id="page-content-wrapper" class="page-ehr-appointments" v-loading.fullscreen.lock="loading">
            <div id="page-content">
                <!-- <help :video-options="helpVideoOption"/>-->

                <schedule-appointments @openScheduleModal="openScheduleModal" class="schedule-appointments-wrapper"/>

                <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 20px">
                    <div>
                        <div class="btn-group">
                            <button class="fc-prev-button btn btn-primary" @click="handlePrevDayClick">
                                <span class="fa fa-chevron-left"></span>
                            </button>
                            <button class="fc-next-button btn btn-primary" @click="handleNextDayClick">
                                <span class="fa fa-chevron-right"></span>
                            </button>
                        </div>
                        <button
                            class="fc-today-button btn btn-primary" 
                            :disabled="isCurrentDate" 
                            style="margin-left: 15px"
                            @click="handleCurrentDayClick"
                        >
                            Current Day
                        </button>
                    </div>
                    <div>
                        <h2 v-if="filters && filters.date" class="selected-day">{{ filters.date }}</h2>
                    </div>
                    <div></div>
                </div>

                <div class="statistic-cards">
                    <div class="statistic-card" v-for="(value, key) in appointmentStatistic" :key="key">
                            <div>{{key}}</div>
                            <div>{{value}}</div>
                    </div>
                    <div class="statistic-card" style="font-weight: bold">
                        <div>Total</div>
                        <div>{{totalAppointments}}</div>
                    </div>
                </div>
                
                <ehr-table :appointments="appointments" @startRemoveAppointment="loading = true" @removeAppointment="updateAppointmentsList"
                           @openTableModal="openTableModal" @updateAppointmentStatistic="updateAppointmentStatistic"/>
            </div>
        </div>
        <CreateAppointmentModal
                v-if="appointmentModal"
                @updateAppointments="updateAppointmentsList"
                :visibleAppointmentModal="appointmentModal"
                :scheduleAppointments="filters"
                :isEditable="isEditable"
                @close="closeAppointmentModal"/>

        <CreateAppointmentModal
                v-if="editableAppointmentModal"
                @updateAppointments="updateAppointmentsList"
                :dialog-title="appointmentDialogTitle"
                :visibleAppointmentModal="editableAppointmentModal"
                :tableAppointments="dataTableAppointments"
                :isEditable="isEditable"
                :isCreated="isAppointmentCreated"
                @close="closeEditableAppointmentModal"/>

        <reschedule-appointment />
        
        <cancel-appointment />

        <confirm-telehealth @telehealth-confirmed="setTelehealth"  />

        <complete-appointment :is-telehealth="isTelehealth" :is-data-fetching="false" />
    </div>
</template>

<script>
    import EhrTable from "../appointments/ehr/EHRTable";
    import ScheduleAppointments from "../appointments/ehr/Schedule";
    import FiltersList from "../appointments/ehr/FiltersList";
    import CreateAppointmentModal from '../appointments/CreateAppointmentModal';
    import Help from './../help/Help.vue';

    export default {
        data() {
            return {
                loading: false,
                appointments: [],
                openEvent: null,
                filters: {},
                dataTableAppointments: {},
                appointmentModal: false,
                editableAppointmentModal: false,
                isEditable: true,
                appointmentDialogTitle: 'Schedule appointment',
                helpVideoOption: {
                    autoplay: false,
                    controls: true,
                    sources: [
                        {
                            src: "https://cwr-video-trainings.s3-us-west-1.amazonaws.com/1_2019_02_19david-kessler-grief-gr_360pAAC_640x360_700.mp4",
                        }
                    ]
                },
                isAppointmentCreated: true,
                appointmentStatistic: {
                    "Active": 0,
                    "Canceled": 0,
                    "Rescheduled": 0,
                    "Completed": 0,
                    "Visit Created": 0
                },
                totalAppointments: 0,
                telehealth: false,
            }
        },
        components: {EhrTable, ScheduleAppointments, FiltersList, CreateAppointmentModal, Help},
        computed: {
            isTelehealth: {
                get() {
                    return this.telehealth;
                },

                set(value) {
                    this.telehealth = value;
                }
            },
            cancelStatuses() {
                return this.$store.state.appointment_cancel_statuses;
            },
            rescheduledStatuses() {
                return this.$store.state.appointment_reschedule_statuses;
            },
            isCurrentDate() {
                return this.filters.date === moment().format("MM/DD/YYYY");
            }
        },
        methods: {
            setFilter(val) {
                this.appointments = val.appointments;
                this.filters = val.filters;
            },
            updateAppointmentStatistic(appointments) {
                this.appointmentStatistic = {
                    "Active": 0,
                    "Canceled": 0,
                    "Rescheduled": 0,
                    "Completed": 0,
                    "Visit Created": 0
                };

                appointments.forEach(appointment => {
                    if (this.cancelStatuses.some(status => status.id === appointment.status.id)) {
                        this.appointmentStatistic['Canceled']++;
                    } else if (this.rescheduledStatuses.some(status => status.id === appointment.status.id)) {
                        return this.appointmentStatistic['Rescheduled']++;
                    } else {
                        this.appointmentStatistic[appointment.status.status]++;
                    }
                });

                this.totalAppointments = appointments.length;
            },
            isLoading(val) {
                this.loading = val;
            },
            openScheduleModal() {
                this.appointmentDialogTitle = 'Schedule appointment';
                this.isAppointmentCreated = true;
                this.appointmentModal = true;
            },
            openTableModal(data) {
                if(data.patient_name) {
                    this.appointmentDialogTitle = 'Update appointment';
                    this.isAppointmentCreated = false;
                } else {
                    this.appointmentDialogTitle = 'Schedule appointment';
                    this.isAppointmentCreated = true;
                }
                this.dataTableAppointments = Object.assign(data, {date: this.filters.date});
                this.editableAppointmentModal = true;
            },
            closeAppointmentModal() {
                this.appointmentModal = false
            },
            closeEditableAppointmentModal() {
                this.editableAppointmentModal = false
            },
            updateAppointmentsList() {
                this.loading = true;
                this.$store.dispatch('getEHRAppointments', this.filters)
                    .then(({data}) => {
                        this.appointments = data.appointments;
                        this.loading = false;
                    });
            },
            setTelehealth(status) {
                this.isTelehealth = status;
                window.setTimeout(function() {
                    $('#complete-appointment').modal('show');
                }, 500);
            },
            watchUpdate() {
                window.Echo.private('availabilityFor')
                    .listen('.availability.changed', ({date}) => {
                        let dateChange = moment(date).format('YYYY-MM-DD'),
                            currentDateArray = this.filters.date.split("/");
                        [currentDateArray[0], currentDateArray[1]] = [currentDateArray[1], currentDateArray[0]];
                        let currentDate = currentDateArray.reverse().join("-");
                        if (new Date(dateChange).getTime() === new Date(currentDate).getTime()) {
                            this.$store.dispatch('getEHRAppointments', this.filters)
                                .then(({data}) => {
                                    this.appointments = data.appointments;
                                });
                        }
                    });
            },
            handlePrevDayClick() {
                this.$emit('prev-day-click');
            },
            handleNextDayClick() {
                this.$emit('next-day-click');
            },
            handleCurrentDayClick() {
                this.$emit('current-day-click');
            }
        },
        mounted() {
            this.watchUpdate();
            this.$store.dispatch('getUserRoles');
        }
    }
</script>

<style lang="scss">
    .sidebar-section-collapse {

        .el-collapse-item__header {
            font-size: 16px;
        }
    }

    .page-ehr-appointments {
        .btn {
            &-primary {
                background: #409eff;
                border-color: #409eff;

                &:hover,
                &:focus {
                    background: #66b1ff;
                    border-color: #66b1ff;
                }
            }
        }

        .fc-prev-button,
        .fc-next-button {
            width: 40px;
            height: 40px;
        }

        .fc-today-button {
            height: 40px;
        }

        .help-icon {
            top: 65px;

            @media (max-width: 929px) {
                top: 20px;
            }
        }
    }
</style>

<style lang="scss" scoped>
    #page-content {
        padding-bottom: 0;
    }

    .sidebar-section {

        &-collapse {
            display: block;

            @media (min-width: 930px) {
                display: none;
            }
        }

        &-column {
            display: none;

            @media (min-width: 930px) {
                display: block;
            }
        }

        &-container {
            @media (min-width: 930px) {
                height: calc(100vh - 85px);
            }
        }
    }

    .schedule-appointments-wrapper {
        margin-bottom: 20px;
    }

    .statistic-cards {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;

        .statistic-card {
            background-color: white;
            border-radius: 4px;
            padding: 6px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 125px;
            border: 1px solid #EBEEF5;
        }
    }

    .selected-day {
        font-size: 1.75em;
        margin: 0;
    }
</style>
