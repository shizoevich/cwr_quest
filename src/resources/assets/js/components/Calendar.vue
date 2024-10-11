<template>
    <div class="page-calendar">
        <system-messages page="doctor-availability"></system-messages>

        <div id="page-content-wrapper" v-if="wrapper" v-loading.fullscreen.lock="loading">
            <div id="page-content" class="content-with-footer" :class="{ disabled: loading }">
                <!--                <help :video-options="helpVideoOption" />-->
                
                <div v-if="alerts && alerts.length" class="container-alert">
                    <div class="alert alert-danger" role="alert" v-for="alert in alerts" v-html="alert"></div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="calendar-actions">
                            <div v-if="showTotalWorkHours" class="calendar-work-hours-collapse">
                                <total-work-hours-collapse
                                    :loading="loadingTotalWorkHours"
                                    :work-hours-data="workHoursData"
                                    :work-hours-period="workHoursPeriod"
                                />
                            </div>

                            <div class="calendar-actions-buttons">
                                <el-button
                                    v-if="confirmationLoad && !confirmationStatus"
                                    type="primary"
                                    class="btn btn-primary"
                                    @click="confirmWeekModal"
                                >
                                    Confirm your weekly availability
                                </el-button>

                                <el-button
                                    v-if="showDuplicate && !isWeekInPast"
                                    type="success"
                                    class="btn btn-success btn-duplicate"
                                    :loading="isDuplicateLoading"
                                    @click="openAttentionModal"
                                >
                                    Duplicate previous week availability
                                </el-button>
                            </div>
                        </div>

                        <div v-if="calendarOptions" class="calendar-options">
                            <div class="calendar-week-control">
                                <el-date-picker
                                    v-model="week"
                                    type="week"
                                    placeholder="Pick a week"
                                    :format="weekFormat"
                                    :clearable="false"
                                    :picker-options="weekOption"
                                    @change="changeWeek"
                                >
                                </el-date-picker>
                            </div>
                            <div class="calendar-filter-control">
                                <el-checkbox style="margin-right: 10px;" v-model="show_active_appointments" :disabled="loading">
                                    Active and Completed Appointments
                                </el-checkbox>
                                <el-checkbox style="margin-right: 10px;" v-model="show_canceled_appointments" :disabled="loading">
                                    Canceled Appointments
                                </el-checkbox>
                                <el-checkbox style="margin-right: 10px;" v-model="show_rescheduled_appointments" :disabled="loading">
                                    Rescheduled Appointments
                                </el-checkbox>
                            </div>
                        </div>
                    </div>
                </div>

                <full-calendar ref="calendar" v-if="calendarOptions" :options="calendarOptions" />
            </div>
        </div>

        <calendar-work-time-modal :workTime="selectTime" :workTimeId="selectedWorkTimeId" :eventToEdit="eventToEdit"
            :editWorkTimeId="editWorkTimeId" :deleteWorkTimeId="deleteWorkTimeId" :eventNeedsAction="event_needs_action"
            :changeEventCallback="change_event_callback" v-on:clearSelectTime="clearSelectTime"
            v-on:refreshCalendar="refreshCalendar" v-on:showDeleteWorkHour="showDeleteWorkHour"
            v-on:clearEditRecurringEventForm="clearEditRecurringEventForm"
            v-on:changeEventCallback="changeEventCallback($event)" v-on:changeEditWorkTimeId="changeEditWorkTimeId($event)"
            v-on:updateProviderAvailabilityCalendarWorkHours="
                updateProviderAvailabilityCalendarWorkHours($event)
            " v-on:showErrorMessage="showErrorMessage($event)" />

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" id="cant-add-event-modal"
            role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Warning
                            <button type="button" class="close" aria-label="Close" @click="closeErrorModal()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </h5>
                    </div>
                    <div class="modal-body">
                        {{ error_modal_text }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeErrorModal()">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" id="confirm-week-modal"
            role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>
                            Do you wish to confirm your availability for the week of {{ confirmationWeekRange }}? Confirming will finalize your schedule, notify management of your timely submission, ensure compliance with scheduling rules, and stop further notifications for this week.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" @click="confirmWeek()">
                            Confirm
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
            id="copy-availability-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5>
                            {{ attentionModalText.message }}
                        </h5>
                    </div>
                    <div class="modal-footer">
                        <button v-if="attentionModalText.status" class="btn btn-primary" @click="copyLast">
                            Duplicate
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <el-dialog title="Create event" :visible.sync="showModalEventType" top="35vh"
            class="modal-select-event-type bootstrap-modal">
            <el-form :model="formEventType">
                <div class="row">
                    <el-form-item class="col-12" label="Event type">
                        <el-select class="select-event-type" v-model="formEventType.event_type" @change="selectEventType"
                            placeholder="Select Type">
                            <el-option v-for="event in eventTypes" :key="event.value" :label="event.label"
                                :value="event.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </div>
            </el-form>
        </el-dialog>

        <CreateAppointmentModal v-if="showCreateAppointmentsModal" :selectTime="appointmentsSelectTime"
            :visibleAppointmentModal="showCreateAppointmentsModal" @startUpdateAppointment="startUpdateAppointment"
            @canceledUpdateAppointment="canceledUpdateAppointment" @createAppointment="refreshCalendar" :is-editable="true"
            :isTherapist="true" @close="closeAppointmentModal" />

        <CreateAppointmentModal v-if="showEditAppointmentsModal" :dialog-title="appointmentDialogTitle" :is-editable="true"
            :editable="true" :fields="appointmentsFields" :visibleAppointmentModal="showEditAppointmentsModal"
            :isCreated="isAppointmentCreated" :isTherapist="true" @startRemoveAppointment="startRemoveAppointment"
            @startUpdateAppointment="startUpdateAppointment" @canceledUpdateAppointment="canceledUpdateAppointment"
            @createAppointment="refreshCalendar" @updateAppointment="updateAppointment" @removeAppointment="refreshCalendar"
            @close="closeEditAppointmentModal" />
    </div>
</template>

<script>
import FullCalendar from "@fullcalendar/vue";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import bootstrapPlugin from "@fullcalendar/bootstrap";
import CreateAppointmentModal from "./appointments/CreateAppointmentModal";
import TotalWorkHoursCollapse from "./TotalWorkHoursCollapse";
import Help from "./help/Help";
import { Notification } from "element-ui";
import getDateOfMonthlyMeeting from '../helpers/getDateOfMonthlyMeeting';
import { MONTHLY_MEETING_TIME, MONTHLY_MEETING_WEEKDAY } from '../settings';
import { formatWeek } from "../helpers/date";

export default {
    props: ["withWrapper"],

    components: {
        "calendar-work-time-modal": require("./forms/CalendarWorkTimeModal.vue"),
        CreateAppointmentModal,
        TotalWorkHoursCollapse,
        FullCalendar,
        Help,
    },

    data() {
        return {
            isNotPreloader: false,
            showCreateAppointmentsModal: false,
            showEditAppointmentsModal: false,
            appointmentsFields: {},
            showModalEventType: false,
            isDuplicateLoading: false,
            formEventType: {
                event_type: null,
            },
            eventTypes: [
                {
                    value: "appointment",
                    label: "Appointment",
                },
                {
                    value: "availability",
                    label: "Availability",
                },
            ],
            selectTimeForModal: null,
            appointmentsSelectTime: null,
            currWeek: 0,
            wrapper: true,
            selectTime: null,
            eventToEdit: {},
            selectedWorkTimeId: null,
            editWorkTimeId: false,
            deleteWorkTimeId: false,
            loading: false,
            currWeekHours: 0,
            show_active_appointments: true,
            show_canceled_appointments: false,
            show_rescheduled_appointments: false,
            change_event_callback: null,
            event_needs_action: null,
            error_modal_text: "",
            default_texts: {
                new_event_in_old_day:
                    "Impossible to add an event on the date in the past.",
                new_availability_in_24_hours_from_now:
                    "Impossible to add an event for the next 24 hours.",
                cant_over_day: "The event cannot end later than 10 PM",
            },
            init_start_date: null,
            alerts: [],
            isClonable: true,
            intervalEnd: null,
            confirmedSuccess: false,
            calendarOptions: null,
            appointmentDialogTitle: "Schedule appointment",
            helpVideoOption: {
                autoplay: false,
                controls: true,
                sources: [
                    {
                        src: "https://cwr-video-trainings.s3-us-west-1.amazonaws.com/1_2019_02_19david-kessler-grief-gr_360pAAC_640x360_700.mp4",
                    },
                ],
            },
            isAppointmentCreated: "Schedule appointment",
            week: "",
            weekOption: {
                firstDayOfWeek: 1,
            },
            loadingTotalWorkHours: false,
            workHoursPeriod: {
                startDate: '',
                endDate: '',
            },
            workHoursData: null,
        };
    },
    computed: {
        confirmationWeek() {
            if (moment().isoWeekday() >= 4) {
                return parseInt(moment().format("w")) + 1;
            }
            
            return parseInt(moment().format("w"));
        },

        confirmationWeekRange() {
            let startDate = moment().isoWeekday() >= 4 ? moment().add(1, 'week').startOf('isoWeek') : moment().startOf('isoWeek');

            return formatWeek(startDate, startDate.clone().endOf('isoWeek'));
        },

        confirmationLoad() {
            return this.$store.state.confirmationLoad;
        },

        confirmationStatus() {
            return this.$store.state.confirmationStatus;
        },

        attentionModalText() {
            return this.$store.state.attentionModalCopy;
        },

        showDuplicate() {
            return moment().isBefore(this.intervalEnd);
        },

        isWeekInPast() {
            const selectedWeekStart = moment(this.week).startOf('week');
            const currentWeekStart = moment().startOf('week');
            return selectedWeekStart.isSameOrBefore(currentWeekStart);
        },

        weekFormat() {
            let startDateOfWeek = moment(this.week),
                lastDateOfWeek = moment(this.week).add(6, "d");
            return `[${startDateOfWeek.format("MMM")}] ${startDateOfWeek.format("DD")}  -  [${lastDateOfWeek.format("MMM")}] ${lastDateOfWeek.format("DD")}`;
        },

        provider() {
            return this.$store.state.currentProvider;
        },

        isBillingPeriodTypeMonthly() {
            return this.provider && this.provider.billing_period_type_id === 2;
        },

        showTotalWorkHours() {
            return this.workHoursData && this.workHoursData.minimum_work_hours;
        },
    },
    methods: {
        selectEventType() {
            this.showModalEventType = false;
            if (this.formEventType.event_type === "availability") {
                if (this.selectTimeForModal.isBefore(moment().add(24, "hours"))) {
                    this.showErrorMessage(this.default_texts.new_availability_in_24_hours_from_now);
                } else {
                    this.selectTime = this.selectTimeForModal;
                }
            } else {
                this.appointmentDialogTitle = "Schedule appointment";
                this.isAppointmentCreated = true;
                this.showCreateAppointmentsModal = true;
                this.appointmentsSelectTime = this.selectTimeForModal;
            }
            this.formEventType.event_type = null;
        },

        closeAppointmentModal() {
            this.showCreateAppointmentsModal = false;
        },

        closeEditAppointmentModal() {
            this.showEditAppointmentsModal = false;
        },

        closeErrorModal() {
            this.error_modal_text = "";
        },

        confirmWeek() {
            this.$store
                .dispatch("confirmWeek", {
                    week: this.confirmationWeek,
                    year: moment().format("Y"),
                })
                .then(() => {
                    Notification.success({
                        title: "Success",
                        message: "Weekly availability has been confirmed",
                        type: "success",
                    });
                });
            $("#confirm-week-modal").modal("hide");
            this.confirmedSuccess = true;
        },

        selectedWeek() {
            return parseInt(
                $(".fc-week-number")
                    .text()
                    .replace(/[^\d.]/g, "")
            );
        },

        openSelectEventTypeModal(selectTime) {
            this.showModalEventType = true;
            this.selectTimeForModal = selectTime;
        },

        openAttentionModal() {
            this.isDuplicateLoading = true;
            let calendar = this.$refs.calendar.getApi().view,
                currentStart = calendar.currentStart,
                currentEnd = moment(calendar.currentEnd).format("YYYY-MM-DD");
            let dates = {
                start: moment(currentStart).utc().format(),
                end: moment(currentEnd).format(),
            };
            this.$store.dispatch("checkCopyWeek", dates).then(() => {
                $("#copy-availability-modal").modal("show");
                this.isDuplicateLoading = false;
            });
        },

        copyLast() {
            this.isDuplicateLoading = true;
            let calendar = this.$refs.calendar.getApi().view,
                currentStart = calendar.currentStart,
                currentEnd = moment(calendar.currentEnd).format("YYYY-MM-DD");
            let dates = {
                start: moment(currentStart).utc().format(),
                end: moment(currentEnd).format(),
            };
            this.$store.dispatch("duplicateWeek", dates).then(() => {
                this.refreshCalendar();
                this.isDuplicateLoading = false;
                Notification.success({
                    title: "Success",
                    message: "Availability for the selected week has been saved",
                    type: "success",
                });
            });

            $(".modal").modal("hide");
        },

        initCalendar() {
            const vueElement = this;
            this.calendarOptions = {
                nowIndicator: true,
                now: moment().format("YYYY-MM-DD\THH:mm:ssZ"),
                timeZone: "America/Los_Angeles",
                plugins: [timeGridPlugin, interactionPlugin, bootstrapPlugin],
                headerToolbar: {
                    left: "prev,next today",
                    center: "",
                    right: "",
                },
                buttonText: {
                    today: "Current Week",
                },
                eventDisplay: "block",
                slotEventOverlap: false,
                firstDay: 1,
                contentHeight: 700,
                expandRows: true,
                initialView: "timeGridWeek",
                slotMinTime: "07:00:00",
                slotMaxTime: "22:00:00",
                slotDuration: "00:15:00",
                slotLabelInterval: "01:00:00",
                eventTimeFormat: {
                    hour: "2-digit",
                    minute: "2-digit",
                },
                editable: true,
                forceEventDuration: true,
                themeSystem: "bootstrap",
                footerToolbar: false,
                weekNumbers: false,
                selectable: false,
                slotLabelFormat: [
                    {
                        hour: "numeric",
                        omitZeroMinute: true,
                        omitCommas: true,
                    },
                ],
                dayHeaderFormat: {
                    weekday: "short",
                    month: "2-digit",
                    day: "2-digit",
                    omitCommas: true,
                },
                customButtons: {
                    btnCopyEvents: {
                        text: "Copy last events",
                    },
                },
                allDaySlot: false,
                initialDate: this.init_start_date,
                eventSources: [
                    {
                        events: function (info, successCallback) {
                            const getDateMonthlyMeetingInInterval = (startDate, endDate) => {
                                let meetingDate = null

                                const meetingDaysArray = []

                                meetingDaysArray.push(getDateOfMonthlyMeeting(startDate, MONTHLY_MEETING_WEEKDAY))
                                meetingDaysArray.push(getDateOfMonthlyMeeting(endDate, MONTHLY_MEETING_WEEKDAY))

                                const start = moment(startDate)
                                const end = moment(endDate)

                                meetingDaysArray.forEach(date => {
                                    if (date.isBetween(start, end)) {
                                        meetingDate = date
                                    }
                                })
                                return meetingDate
                            }

                            const dateMonthlyMeetingCurrentWeek = getDateMonthlyMeetingInInterval(info.startStr, info.endStr)

                            vueElement.$store
                                .dispatch("getChartCalendar", {
                                    start: info.startStr,
                                    end: info.endStr,
                                    with_active_appointments: Number(
                                        vueElement.show_active_appointments
                                    ),
                                    with_canceled_appointments: Number(
                                        vueElement.show_canceled_appointments
                                    ),
                                    with_rescheduled_appointments: Number(
                                        vueElement.show_rescheduled_appointments
                                    ),
                                })
                                .then(({ data }) => {
                                    //TODO: remove else if construction and second condition in if (these were added to avoid big changes)
                                    if (dateMonthlyMeetingCurrentWeek && !(info.startStr == '2024-07-01T00:00:00')) {
                                        let timeOffset = '00:00';
                                        if (data.length > 0) {
                                            timeOffset = data[0].start.split('-')[3]
                                        }

                                        const meetingDate = dateMonthlyMeetingCurrentWeek.format('YYYY-MM-DD')

                                        const startTime = MONTHLY_MEETING_TIME.split('-')[0]
                                        const endTime = MONTHLY_MEETING_TIME.split('-')[1]
                                        
                                        data.push({
                                            allDay: false,
                                            id: 10000,
                                            editable: false,
                                            forceEventDuration: false,
                                            title: 'Monthly staffing',
                                            type: 'meeting',
                                            start: `${meetingDate}T${startTime}:00-${timeOffset}`,
                                            end: `${meetingDate}T${endTime}:00-${timeOffset}`,
                                            backgroundColor: '#800080',
                                            borderColor: '#800080'
                                        })
                                    } else if(!dateMonthlyMeetingCurrentWeek && info.startStr == '2024-07-08T00:00:00'){
                                        let timeOffset = '00:00';
                                        if (data.length > 0) {
                                            timeOffset = data[0].start.split('-')[3]
                                        }

                                        const meetingDate = '2024-07-12'

                                        const startTime = MONTHLY_MEETING_TIME.split('-')[0]
                                        const endTime = MONTHLY_MEETING_TIME.split('-')[1]
                                        
                                        data.push({
                                            allDay: false,
                                            id: 10000,
                                            editable: false,
                                            forceEventDuration: false,
                                            title: 'Monthly staffing',
                                            type: 'meeting',
                                            start: `${meetingDate}T${startTime}:00-${timeOffset}`,
                                            end: `${meetingDate}T${endTime}:00-${timeOffset}`,
                                            backgroundColor: '#800080',
                                            borderColor: '#800080'
                                        })
                                    }

                                    successCallback(data);
                                });
                        },
                    },
                ],
                eventContent: function( info ) {
                    const { start, end, title, extendedProps: { item_source } } = info.event;
                    const startTime = moment.utc(start).format('h:mm A');
                    const endTime = moment.utc(end).format('h:mm A');
                    let rescheduledInfo;

                    if (item_source && item_source.rescheduled_appointment_date && item_source.rescheduled_appointment_date.start && item_source.rescheduled_appointment_date.end) {
                        const reStart = moment.utc(item_source.rescheduled_appointment_date.start).format('MM/DD/YY h:mm A');
                        const reEnd = moment.utc(item_source.rescheduled_appointment_date.end).format('h:mm A');
                        rescheduledInfo = `<div class="calendar-item-tooltip">
                                                <i class="el-icon-question custom-help-icon calendar-item-tooltip__icon" slot="reference"></i>
                                                <span class="calendar-item-tooltip__text">Rescheduled to:<br>${reStart}</span>
                                            </div>`;
                    } else {
                        rescheduledInfo = ''
                    }
                    const timeInfo = `<div class="fc-event-time" style="overflow: visible;">${rescheduledInfo} ${startTime} - ${endTime}</div>`;
                    const titleLink = item_source && item_source.patient ? `<a href='/chart/${item_source.patient.id}' target="_blank" class="patient-link" onclick="event.stopPropagation()">${title}</a>` : title;

                    const html = `
                        <div class="fc-event-main-frame" style="overflow: visible;">
                            ${ timeInfo }
                            <div class="fc-event-title-container">
                                <div class="fc-event-title fc-sticky">${ titleLink }</div>
                            </div>
                        </div>`;

                    return {html};
                },
                loading: (isLoading) => {
                    $(".qtip").remove();
                    if (isLoading) {
                        if (!this.isNotPreloader) {
                            this.loading = isLoading;
                        }
                    } else {
                        this.isNotPreloader = false;
                        this.loading = isLoading;
                    }
                },
                eventDidMount: function ({ el, event }) {
                    if (event._def.extendedProps.type === "appointment") {
                        if ($(el).hasClass("fc-event-past")) {
                            $(el).css("cursor", "default");
                        } else {
                            $(el).css("cursor", "pointer");
                        }

                        setTimeout(() => {
                            let parent = $(el).parent(),
                                parentInset = $(parent).css("inset").split(" ");
                            if (parentInset && parentInset.length) {
                                parentInset[1] = "0";
                                parentInset[3] = "0";
                                parent.css("inset", parentInset.join(" "));
                            }
                            $(el).parent().css("z-index", "4");
                        }, 10);
                    } else if (event._def.extendedProps.type === "canceled_appointment") {
                        // $(el).parent().popover({
                        //   animation: true,
                        //   html: false,
                        //   content: 'Canceled Appointment',
                        //   trigger: 'hover',
                        //   placement: 'bottom'
                        // });
                        setTimeout(() => {
                            let parent = $(el).parent(),
                                parentInset = $(parent).css("inset").split(" ");
                            if (parentInset && parentInset.length) {
                                const leftPosNotZero = parentInset.length > 1 && parentInset[1].replace(/[^0-9]+/, "") != 0;
                                const rightPosNotZero = parentInset.length > 3 && parentInset[3].replace(/[^0-9]+/, "") != 0;

                                if (leftPosNotZero || rightPosNotZero) {
                                    parentInset[1] = "0";
                                    parentInset[3] = "35%";
                                    parent.css("inset", parentInset.join(" "));
                                }
                            }
                            $(el).parent().css("z-index", "3");
                        }, 10);
                    } else if (
                        event._def.extendedProps.type === "workTime" ||
                        event._def.extendedProps.type === "oldDays"
                    ) {
                        if ($(el).hasClass("fc-event-past") || event._def.extendedProps.type === "oldDays") {
                            $(el).css("cursor", "default");
                        } else {
                            $(el).css("cursor", "pointer");
                        }

                        setTimeout(() => {
                            let parent = $(el).parent(),
                                parentInset = $(parent).css("inset").split(" ");
                            if (parentInset && parentInset.length) {
                                parentInset[1] = "0";
                                parentInset[3] = "15%";
                                parent.css("inset", parentInset.join(" "));
                            }
                            $(el).parent().css("z-index", "2");
                        }, 10);
                    } else if (event._def.extendedProps.type === 'meeting') {
                        setTimeout(() => {
                            let parent = $(el).parent(),
                                parentInset = $(parent).css("inset").split(" ");
                            if (parentInset && parentInset.length) {
                                parentInset[1] = "0";
                                parentInset[3] = "15%";
                                parent.css("inset", parentInset.join(" "));
                            }
                            $(el).parent().css("z-index", "1");
                        }, 10);
                    }
                },
                dateClick: ({ dateStr }) => {
                    $(".qtip").css("display", "none");
                    if (moment(dateStr).format("HH:mm:ss") === "00:00:00") {
                        return;
                    }
                    let now = moment().format("YYYY-MM-DD");
                    let date = moment(dateStr).format("YYYY-MM-DD");
                    if (moment(now).isAfter(date, "day")) {
                        this.error_modal_text =
                            vueElement.default_texts.new_event_in_old_day;
                        return;
                    }
                    this.$store.dispatch("getMaxTimeEvent", {
                        date: moment(dateStr).format(moment.HTML5_FMT.DATE),
                        time: moment(dateStr).format(moment.HTML5_FMT.TIME_SECONDS),
                    });
                    this.openSelectEventTypeModal(moment(dateStr));
                },
                eventOverlap: function (stillEvent, movingEvent) {
                    $(".qtip").css("display", "none");
                    return !(
                        stillEvent.type === "workTime" && movingEvent.type === "workTime"
                    );
                },
                eventClick: function (info) {
                    if (info.event._def.extendedProps.type === 'meeting') {
                        return;
                    }

                    vueElement.$store.dispatch("getMaxTimeEvent", {
                        date: moment(info.event.startStr).format(moment.HTML5_FMT.DATE),
                        time: moment(info.event.startStr).format(
                            moment.HTML5_FMT.TIME_SECONDS
                        ),
                        event_id: info.event.id,
                    });
                    $(".qtip").css("display", "none");
                    if (
                        (info.event._def.extendedProps.type === "appointment" ||
                            info.event._def.extendedProps.type === "canceled_appointment") &&
                        !info.el.classList.contains("fc-event-past")
                    ) {
                        let infoDate = {};
                        infoDate.start = moment(info.event.startStr);
                        infoDate.end = moment(info.event.endStr);
                        vueElement.appointmentsFields = Object.assign(
                            infoDate,
                            info.event._def.extendedProps
                        );
                        vueElement.showEditAppointmentsModal = true;
                        this.appointmentDialogTitle = "Update appointment";
                        this.isAppointmentCreated = false;
                    } else if (info.event._def.extendedProps.type === "workTime") {
                        if (
                            info.jsEvent.target.classList.contains("fc-bgevent-delete-span")
                        ) {
                            var button = $(info.jsEvent.target).closest("button");

                            vueElement.selectedWorkTimeId = $(button).data("work-time-id");
                            vueElement.deleteWorkTimeId = true;
                            return;
                        }
                        let calEvent = info.event._def;
                        calEvent.start = moment(info.event.startStr);
                        calEvent.end = moment(info.event.endStr);
                        vueElement.selectedWorkTimeId =
                            info.event._def.extendedProps.item_source.id;
                        vueElement.eventToEdit = Object.assign(
                            calEvent,
                            info.event._def.extendedProps
                        );
                        vueElement.editWorkTimeId = true;
                    }
                }.bind(this),
                eventDrop: function ({ event, revert }) {
                    let maxDate = moment(
                        moment(event.startStr).format("Y-MM-DD") +
                        " " +
                        moment
                            .utc(event._context.options.slotMaxTime.milliseconds)
                            .format("HH:mm::ss")
                    );
                    let eventEnd = moment(
                        moment(event.endStr).format("Y-MM-DD HH:mm:ss")
                    );
                    let minDate = moment(
                        moment(event.startStr).format("Y-MM-DD") +
                        " " +
                        moment
                            .utc(event._context.options.slotMinTime.milliseconds)
                            .format("HH:mm::ss")
                    );
                    let eventBegin = moment(
                        moment(event.endStr).format("Y-MM-DD HH:mm:ss")
                    );
                    if (eventEnd.isAfter(maxDate)) {
                        revert();
                        vueElement.error_modal_text =
                            vueElement.default_texts.cant_over_day;
                        return false;
                    }

                    if (eventBegin.isBefore(minDate)) {
                        revert();
                        return false;
                    }

                    $(".qtip").css("display", "none");
                    let now = moment().set({ hour: 0, minute: 0, second: 0 });
                    if (now.isAfter(event.startStr)) {
                        revert();
                        vueElement.error_modal_text =
                            vueElement.default_texts.new_event_in_old_day;
                        return;
                    }
                    let calEvent = event._def;
                    calEvent.start = moment(event.startStr);
                    calEvent.end = moment(event.endStr);
                    event.edit_event_mode = 0;
                    event.action = "event_drop";
                    vueElement.updateProviderAvailabilityCalendarWorkHours(
                        Object.assign(calEvent, event._def.extendedProps)
                    );
                },
                eventResize: function (info) {
                    let calEvent = info.event._def;
                    calEvent.start = moment(info.event.startStr);
                    calEvent.end = moment(info.event.endStr);

                    const duration = moment.duration(calEvent.end.diff(calEvent.start)).asMinutes();
                    if (duration < 60) {
                        vueElement.$message({
                            type: "error",
                            message: "The duration of availability must be at least 1 hour.",
                            duration: 10000,
                        });
                        info.revert();
                        return;
                    }

                    calEvent.edit_event_mode = 0;
                    calEvent.action = "event_resize";
                    $(".qtip").css("display", "none");
                    vueElement.updateProviderAvailabilityCalendarWorkHours(
                        Object.assign(calEvent, info.event._def.extendedProps)
                    );
                },
                viewDidMount: function ({ view }) {
                    vueElement.$store.dispatch("copiedWeekStatusUpdate", false);
                    vueElement.intervalEnd = view.currentEnd;
                },
                datesSet: (changeInfo) => {
                    this.week = moment(changeInfo.startStr).add(1, "d")._d;

                    this.getProviderTotalWorkHours();
                },
            };
            $(".fc-row.fc-week.panel-default").addClass("hide");
        },

        changeEventCallback(val) {
            this.change_event_callback = val;
        },

        changeEditWorkTimeId(val) {
            this.editWorkTimeId = val;
        },

        clearEditRecurringEventForm() {
            this.refreshCalendar();
            this.change_event_callback = null;
            this.event_needs_action = null;
        },

        showErrorMessage(message) {
            this.error_modal_text = message;
        },

        updateProviderAvailabilityCalendarWorkHours(event) {
            let length = (moment(event.end).format("X") - moment(event.start).format("X")) / 60;
            this.$store.dispatch("updateProviderAvailabilityCalendarWorkHours", {
                id: event.item_source.id,
                office_id: event.item_source.office_id,
                office_room_id: event.item_source.office_room_id,
                on: event.start.format("E") - 1,
                at: event.start.format("HH:mm:ss"),
                length: length,
                edit_event_mode: event.edit_event_mode,
                start_date: event.start.format(),
                action: event.action,
                availability_type_id: event.item_source.availability_type_id,
                availability_subtype_id: event.item_source.availability_subtype_id,
                comment: event.item_source.comment,
            })
                .then((response) => {
                    this.refreshCalendar();
                    if (!response.data.success) {
                        let errorMessage = response.data.message;
                        const errors = response.data.errors
                            ? Object.values(response.data.errors).reduce((prev, curr) => prev.concat(curr))
                            : [];
                        if (errors.length) {
                            errorMessage = errors[0];
                        }
                        if (errorMessage) {
                            this.showErrorMessage(errorMessage);
                        }
                    }
                });
        },

        clearSelectTime() {
            this.selectTime = null;
            this.selectedWorkTimeId = null;
            this.editWorkTimeId = false;
            this.deleteWorkTimeId = false;
            this.eventToEdit = {};
        },

        refreshCalendar() {
            this.$refs.calendar.getApi().refetchEvents();
            this.getProviderTotalWorkHours();
        },

        showDeleteWorkHour() {
            this.deleteWorkTimeId = true;
            this.editWorkTimeId = false;
        },

        closeAlert() {
            this.$store.dispatch("copiedWeekStatusUpdate", false);
        },

        confirmWeekModal() {
            $("#confirm-week-modal").modal("show");
        },

        watchUpdate(providerId) {
            window.Echo.private(`availabilityFor.${providerId}`).listen(
                ".availability.changed",
                ({ date }) => {
                    let dateChange = moment(date).format("YYYY-MM-DD"),
                        currentStart = this.$refs.calendar.getApi().view.currentStart,
                        currentEnd = moment(
                            this.$refs.calendar.getApi().view.currentEnd
                        ).format("YYYY-MM-DD");

                    if (
                        new Date(dateChange) >= currentStart &&
                        new Date(dateChange) <= new Date(currentEnd)
                    ) {
                        this.isNotPreloader = true;
                        this.refreshCalendar();
                    }
                }
            );
        },

        startRemoveAppointment() {
            this.isNotPreloader = false;
            this.loading = true;
        },

        startUpdateAppointment() {
            this.isNotPreloader = false;
            this.loading = true;
        },

        canceledUpdateAppointment() {
            this.loading = false;
        },

        updateAppointment() {
            this.loading = false;
        },

        initStartOfWeek() {
            //  sets the week start date
            moment.updateLocale("en", { week: { dow: 1, doy: 7 } });
            this.week = moment().startOf("week").toString();
            moment.updateLocale("en", null);
        },

        changeWeek() {
            this.$refs.calendar.getApi().gotoDate(this.week);
        },

        getProviderTotalWorkHours() {
            if (this.loadingTotalWorkHours || !this.provider || this.isBillingPeriodTypeMonthly || !this.$refs || !this.$refs.calendar) {
                return;
            }

            const calendarApi = this.$refs.calendar.getApi();

            if (!calendarApi || !calendarApi.view) {
                return;
            }

            this.loadingTotalWorkHours = true;

            const startDate = moment(calendarApi.view.currentStart).add(1, 'days').format("YYYY-MM-DD");
            const endDate = moment(calendarApi.view.currentEnd).format("YYYY-MM-DD");

            const payload = {
                start_date: startDate,
                end_date: endDate
            };

            this.$store.dispatch("getProviderAvailabilityCalendarTotalWorkHours", payload)
                .then((response) => {
                    if (!response || response.status === 403 || response.status === 404 || response.status === 422) {
                        return;
                    }

                    this.workHoursData = response.data;

                    this.workHoursPeriod.startDate = startDate;
                    this.workHoursPeriod.endDate = endDate;
                })
                .finally(() => {
                    this.loadingTotalWorkHours = false;
                });
        },
    },
    created() {
        this.$root.$on('refresh-сalendar', this['refreshCalendar']);
    },
    mounted() {
        this.initStartOfWeek();
        Promise.all([
            this.$store.dispatch("getOffices"),
            this.$store.dispatch("getOfficesRooms"),
            this.$store.dispatch("getProviderAvailabilityCalendarWorkHours"),
            this.$store.dispatch("getAvailabilityTypes"),
            this.$store.dispatch("getAvailabilitySubtypes"),
        ]).then(() => {
            this.init_start_date = this.$route.query.start
                ? this.$route.query.start
                : null;
            this.initCalendar();
        });
        this.$store.dispatch("getCurrentProvider").then(({ data }) => {
            this.watchUpdate(data.id);
        });
    },
    watch: {
        show_active_appointments() {
            this.$refs.calendar.getApi().refetchEvents();
        },
        show_canceled_appointments() {
            this.$refs.calendar.getApi().refetchEvents();
        },
        show_rescheduled_appointments() {
            this.$refs.calendar.getApi().refetchEvents();
        },

        error_modal_text() {
            let action = "hide";
            if (this.error_modal_text) {
                action = "show";
            }
            $("#cant-add-event-modal").modal(action);
        },

        loading() {
            $("#calendar .fc-prev-button, #calendar .fc-next-button").prop(
                "disabled",
                this.loading
            );
        },

        provider() {
            this.getProviderTotalWorkHours();
        },
    },
};
</script>

<style lang="scss">
.calendar-item-tooltip {
    max-width: 13px;
    position: relative;
    display: inline-block;
    margin-right: 2px;
    cursor: pointer;

    &__text {
        visibility: hidden;
        width: 120px;
        background-color: rgba(0, 0, 0, 0.8);
        color: #fff;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 99;
        top: 130%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s;

        &::after {
            content: "";
            position: absolute;
            top: -10px;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: transparent transparent rgba(0, 0, 0, 0.8) transparent;
        }
    }
    
    &__icon {
        color: #3f3f3f;
    }

    &:hover {
        .calendar-item-tooltip__text {
            visibility: visible;
            opacity: 1;
        }
    }
}

.patient-link {
    color: #fff;
    text-decoration: underline;

    &:hover {
        color: #22527b;
    }
}

.modal-select-event-type {
    .el-dialog {
        width: 95%;
        max-width: 500px;

        &__body {
            padding-top: 15px;
        }

        .el-form {
            .row {
                width: 100%;
                margin: 0;
            }
        }
    }
}

.page-calendar {
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

    .fc-prev-button,
    .fc-next-button {
        width: 40px;
        height: 40px;
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

    .calendar-actions {
        display: flex;
        flex-wrap: wrap;
    }

    .calendar-actions-buttons {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20px;

        .el-button + .el-button {
            margin-left: 15px;
        }
    }

    .el-button[disabled] {
        background: #99d57c !important;
        border-color: #99d57c !important;
    }
    
    .calendar-options {
        position: relative;
    }

    .calendar-week-control {
        margin: 20px 0;
        max-width: 275px;

        @media (min-width: 1300px) {
            position: absolute;
            top: 0;
            left: 246px;
            max-width: 225px;
            margin: 0;
        }
    }

    .calendar-filter-control {
        margin: 20px 0;
        max-width: 690px;

        @media (min-width: 1300px) {
            position: absolute;
            top: 10px;
            left: 483px;
            max-width: 690px;
            margin: 0;
        }
    }

    .calendar-work-hours-collapse {
        position: relative;
        width: 100%;
        max-width: 468px;
        min-width: 275px;
        height: 40px;
        margin-right: 15px;
        margin-bottom: 20px;

        .work-hours-collapse {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 2;
        }
    }
}
</style>

<style scoped>
.select-event-type {
    width: 100%;
}

.modal {
    text-align: center;
    padding: 0 !important;
}

.modal:before {
    content: "";
    display: inline-block;
    height: 100%;
    vertical-align: middle;
    margin-right: -4px;
}

.modal-dialog {
    display: inline-block;
    text-align: left;
    vertical-align: middle;
}

.fc-bgevent-delete {
    opacity: 1;
}

.close.fc-bgevent-delete:hover {
    opacity: 1 !important;
}

.fc-v-event {
    cursor: pointer;
}

.fc-week-number {
    display: none;
}

.saving-loader {
    position: absolute;
    left: calc(50% - 150px);
    top: calc(50% - 150px);
    z-index: 5000;
}

.disabled {
    pointer-events: none;
    /*-webkit-filter: grayscale(50%); !* Safari 6.0 - 9.0 *!*/
    /*filter: grayscale(50%);*/
}

.hide-appt {
    height: auto;
    width: auto;
    display: inline-block;
}
</style>
