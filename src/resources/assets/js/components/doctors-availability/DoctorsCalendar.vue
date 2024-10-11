<template>
    <full-calendar ref="calendar" v-if="calendarOptions" :options="calendarOptions"/>
</template>

<script>
    import FullCalendar from '@fullcalendar/vue';
    import timeGridPlugin from "@fullcalendar/timegrid";
    import interactionPlugin from "@fullcalendar/interaction";
    import bootstrapPlugin from "@fullcalendar/bootstrap";

    export default {
        props: {
            filtersList: {
                type: Object,
                default() {
                    return {};
                }
            },
            week: {
                type: Date,
                default: '',
            }
        },
        name: 'DoctorsCalendar',
        components: {FullCalendar},
        data() {
            return {
                isNotPreloader: false,
                loading: false,
                calendarOptions: null,
                providers_score: {},
            }
        },
        methods: {
            initialCalendarData() {
                this.calendarOptions = {
                    plugins: [
                        timeGridPlugin,
                        interactionPlugin,
                        bootstrapPlugin
                    ],
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: '',
                    },
                    buttonText: {
                        today: 'Current Week'
                    },
                    firstDay: 1,
                    contentHeight: 700,
                    expandRows: true,
                    initialView: 'timeGridWeek',
                    slotMinTime: "07:00:00",
                    slotMaxTime: "22:00:00",
                    slotDuration: "00:15:00",
                    slotLabelInterval: "01:00:00",
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                    },
                    slotLabelFormat: [
                        {
                            hour: 'numeric',
                            omitZeroMinute: true,
                            omitCommas: true,
                        },
                    ],
                    dayHeaderFormat: {weekday: 'short', month: '2-digit', day: '2-digit', omitCommas: true},
                    editable: true,
                    forceEventDuration: true,
                    themeSystem: "bootstrap",
                    footerToolbar: false,
                    allDaySlot: false,
                    eventDidMount: ({el, event}) => {
                        $(el).find('.fc-event-main-frame .fc-event-title').append('<span>' + event._def.extendedProps.description + '</span>');
                        $(el).css('background-color', '#67c23a');
                        $(el).css('border', '2px solid #67c23a');
                        $(el).css('color', '#fff');
                        $(el).css('cursor', 'pointer');
                        // if ($(el).hasClass('fc-event-future') || $(el).hasClass('fc-event-today')) {
                        //     $(el).addClass('fc-event-editable');
                        //     $(el).find('.fc-event-main-frame').append('<button class="btn-add-appointments">+</button>')
                        // }
                        if (event._def.extendedProps.popover) {
                            $(el).parent().popover({
                                animation: true,
                                html: true,
                                content: event._def.extendedProps.popover,
                                trigger: 'hover',
                                placement: 'bottom'
                            });
                        }
                    },
                    eventSources: [
                        {
                            events: (info, successCallback) => {
                                let payload = Object.assign(this.filtersList, {
                                    start: info.startStr,
                                    end: info.endStr
                                })
                                this.$store.dispatch('getDoctorCalendarEventSource', payload).then(({data}) => successCallback(data));
                            },
                        },
                        {
                            url: '/provider/score',
                            success: (response) => {
                                this.$emit('updateProvidersScore', response)
                            }
                        }
                    ],
                    loading: (val) => {
                        this.isLoading(val);
                    },
                    eventClick: (info) => {
                        let calEvent = info.event._def;
                        calEvent.start = moment(info.event.startStr);
                        calEvent.end = moment(info.event.endStr);
                        this.$emit('openEvent', Object.assign(calEvent, info.event._def.extendedProps))
                    },
                    datesSet: (changeInfo) => {
                        this.$emit('changeDate', moment(changeInfo.startStr).add(1, 'd')._d)
                    }
                }
            },
            refreshCalendar() {
                this.$refs.calendar.getApi().refetchEvents();
            },
            isLoading(val) {
                if (val) {
                    if (!this.isNotPreloader) {
                        return this.$emit('calendarLoading', true)
                    }
                } else {
                    this.isNotPreloader = false;
                    return this.$emit('calendarLoading', false)
                }
            },
            watchUpdate() {
                window.Echo.private('availabilityFor')
                    .listen('.availability.changed', ({date}) => {
                        let dateChange = moment(date).format('YYYY-MM-DD'),
                            currentStart = this.$refs.calendar.getApi().view.currentStart,
                            currentEnd = moment(this.$refs.calendar.getApi().view.currentEnd).format('YYYY-MM-DD');

                        if ((new Date(dateChange) >= currentStart) && (new Date(dateChange) <= new Date(currentEnd))) {
                            this.isNotPreloader = true;
                            this.refreshCalendar();
                        }
                    });
            }
        },
        watch: {
            filtersList: {
                handler() {
                    this.refreshCalendar();
                },
                deep: true
            },
            loading() {
                $('.fc-prev-button, .fc-next-button').prop('disabled', this.loading);
            },
            week(value) {
                if (value) {
                    this.$refs.calendar.getApi().gotoDate(value);
                }
            }
        },
        mounted() {
            this.initialCalendarData();
            this.watchUpdate();
        }
    }
</script>

<style lang="scss">
    .fc-timegrid {

        .popover {
            font-size: 10px;
        }
    }

    .fc-event-editable {

        .fc-event-main {
            padding-right: 15px;
        }

        .btn-add-appointments {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 2px;
            height: auto;
            line-height: 1;
            border: none;
            background: transparent;
            font-size: 1.25rem;
            padding: 3px;
            border-radius: 2px;
        }
    }
</style>