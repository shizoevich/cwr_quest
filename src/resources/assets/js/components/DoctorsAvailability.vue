<template>
    <div v-loading.fullscreen.lock="loading">
        <div id="page-sidebar" class="filters-sidebar">
            <div class="sidebar-wrap">
                <div class="sidebar-section">
                    <div class="sidebar-section-header">Filters</div>
                    <div class="sidebar-section-container">
                        <filters-list :filters-value="filters_values" :start-week-day="calendarStartDate"
                            @changeWeek="changeWeek" @changeFilter="changeFilter" />
                    </div>
                </div>
            </div>
        </div>

        <div id="page-content-wrapper" class="page-doctor-availability">
            <div id="page-content" class="content-with-footer" :class="{ disabled: loading }">
                <!--                <help :video-options="helpVideoOption"/>-->

                <div class="col-12">
                    <el-button class="btn-appointment" type="primary" @click.prevent="appointmentModal = true">
                        Schedule Appointment
                    </el-button>
                </div>

                <doctors-calendar :filters-list="filters" :week="selectedWeek" @calendarLoading="isLoading"
                    @openEvent="isOpenEvent" @changeDate="calendarChangeDate"
                    @updateProvidersScore="updateProvidersScore" />
            </div>
        </div>

        <doctors-modal v-if="isShowDoctorModal" :show-dialog="isShowDoctorModal" :events="openEvent"
            @openDialogAppointment="openDialogAppointment" :providers-score="providers_score"
            @closeDialog="closeDoctorsModal" />

        <CreateAppointmentModal v-if="appointmentModal" :visibleAppointmentModal="appointmentModal"
            :filters-value="filters_values" :isEditable="!isEditable" @startUpdateAppointment="startUpdateAppointment"
            @canceledUpdateAppointment="canceledUpdateAppointment" @updateAppointments="updateAppointments"
            @close="closeAppointmentModal" />
        <CreateAppointmentModal v-if="editableAppointmentModal" :visibleAppointmentModal="editableAppointmentModal"
            :filters-value="filters_values" :appointments="appointmentsData"
            @startUpdateAppointment="startUpdateAppointment" @canceledUpdateAppointment="canceledUpdateAppointment"
            @updateAppointments="updateAppointments" :isEditable="isEditable" @close="closeEditableAppointmentModal" />
    </div>
</template>

<script>
import FiltersList from "./doctors-availability/FiltersList";
import DoctorsCalendar from "./doctors-availability/DoctorsCalendar";
import DoctorsModal from "./doctors-availability/DoctorsModal";
import CreateAppointmentModal from "./appointments/CreateAppointmentModal";
import Help from "./help/Help";

export default {
    props: ["filters_values"],
    data() {
        return {
            openEvent: null,
            filters: {},
            providers_score: {},
            loading: false,
            appointmentModal: false,
            editableAppointmentModal: false,
            isShowDoctorModal: false,
            appointmentsData: {},
            calendarStartDate: null,
            selectedWeek: null,
            isEditable: true,
            helpVideoOption: {
                autoplay: false,
                controls: true,
                sources: [
                    {
                        src: "https://cwr-video-trainings.s3-us-west-1.amazonaws.com/1_2019_02_19david-kessler-grief-gr_360pAAC_640x360_700.mp4",
                    },
                ],
            },
        };
    },
    components: {
        FiltersList,
        DoctorsCalendar,
        DoctorsModal,
        CreateAppointmentModal,
        Help,
    },
    methods: {
        closeAppointmentModal() {
            this.appointmentModal = false;
        },
        closeEditableAppointmentModal() {
            this.editableAppointmentModal = false;
        },
        closeDoctorsModal() {
            this.isShowDoctorModal = false;
        },
        changeFilter(filters) {
            this.filters = filters;
        },
        isLoading(value) {
            this.loading = value;
        },
        isOpenEvent(event) {
            this.openEvent = event;
            this.isShowDoctorModal = true;
        },
        updateProvidersScore(value) {
            this.providers_score = value;
        },
        openDialogAppointment(value) {
            this.isShowDoctorModal = false;
            this.editableAppointmentModal = true;
            this.appointmentsData = value;
            this.openEvent = null;
        },
        startUpdateAppointment() {
            this.isLoading(true);
        },
        updateAppointments() {
            this.isLoading(false);
        },
        canceledUpdateAppointment() {
            this.isLoading(false);
        },
        calendarChangeDate(start) {
            this.calendarStartDate = start;
        },
        changeWeek(date) {
            this.selectedWeek = date;
        },
    },
};
</script>

<style lang="scss">
.page-doctor-availability {
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

        &-success {
            background: #67c23a;
            border-color: #67c23a;
            color: #fff;

            &:hover,
            &:focus {
                background: #85ce61;
                border-color: #85ce61;
            }
        }
    }
}

.fc-prev-button,
.fc-next-button {
    background: #409eff;
    border-color: #409eff;
    width: 40px !important;
    height: 40px !important;

    &:hover {
        background: #66b1ff;
        border-color: #66b1ff;
    }
}

.fc-direction-ltr .fc-toolbar>*> :not(:first-child) {
    padding: 10px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 40px;
    margin-left: 15px;

    &:disabled {
        &:hover {
            background: #409eff;
            border-color: #409eff;
        }
    }
}
</style>

<style scoped>
.btn-appointment {
    margin-bottom: 20px;
}

.saving-loader {
    position: absolute;
    left: calc(50% - 150px);
    top: calc(50% - 150px);
    z-index: 5000;
}

.disabled {
    pointer-events: none;
    -webkit-filter: grayscale(50%);
    /* Safari 6.0 - 9.0 */
    filter: grayscale(50%);
}
</style>
