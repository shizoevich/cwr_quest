export default {
    data() {
        return {
            start_time: null,
            end_time: null,
        };
    },

    methods: {
        initTimePicker() {
            let self = this;

            this.startTimePicker
                .timepicker("remove")
                .timepicker({ minuteStep: 1 })
                .timepicker("setTime", self.start_time)
                .on("focusin", function () {
                    setTimeout(() => {
                        $(".bootstrap-timepicker-widget").attr(
                            "id",
                            "start_time",
                        );
                        self.determineButtonAvailability();
                    }, 50);
                })
                .on("changeTime.timepicker", function (e) {
                    self.start_time = e.time.value;

                    if (self.statuses) {
                        self.statuses.end_time.h = e.time.hours;
                        self.statuses.end_time.m = e.time.minutes;
                        self.statuses.end_time.meridian = e.time.meridian;
                    }
                })
                .on("hide.timepicker", self.handleInvalidTimeOrder);

            this.endTimePicker
                .timepicker("remove")
                .timepicker({
                    minuteStep: 1,
                })
                .timepicker("setTime", self.end_time)
                .on("focusin", function (e) {
                    setTimeout(() => {
                        self.determineButtonAvailability();
                    }, 50);
                })
                .on("changeTime.timepicker", function (e) {
                    self.end_time = e.time.value;
                })
                .on("hide.timepicker", self.handleInvalidTimeOrder);
        },

        handleInvalidTimeOrder() {
            if (this.validateTimeOrder()) {
                return;
            }

            this.updateEndTime();
        },

        destroyTimePicker() {
            this.startTimePicker.timepicker("remove");
            this.endTimePicker.timepicker("remove");
            this.startTimePicker.off("focusin changeTime.timepicker");
            this.endTimePicker.off("focusin changeTime.timepicker");
        },

        updateEndTime() {
            if (!this.start_time) {
                return;
            }

            let end_time = moment(this.start_time, "LT").add(
                this.durationMinutes,
                "m",
            );

            let timeString = end_time.format("LT");
            this.endTimePicker.timepicker("setTime", timeString);
            this.end_time = timeString;
        },

        disableButtons(buttonType) {
            const buttonHourLink = $(`td a[data-action="${buttonType}Hour"]`);
            const buttonMinuteLink = $(
                `td a[data-action="${buttonType}Minute"]`,
            );
            const toggleMeridianLink = $('td a[data-action="toggleMeridian"]');
            buttonHourLink.addClass("disabled");
            buttonMinuteLink.addClass("disabled");
            toggleMeridianLink.addClass("disabled");

            const disableClick = (e) => {
                e.stopPropagation();
            };

            buttonHourLink.on("click", disableClick);
            buttonMinuteLink.on("click", disableClick);
            toggleMeridianLink.on("click", disableClick);
        },

        enableButtons(buttonType) {
            const buttonHourLink = $(`td a[data-action="${buttonType}Hour"]`);
            const buttonMinuteLink = $(
                `td a[data-action="${buttonType}Minute"]`,
            );
            const toggleMeridianLink = $('td a[data-action="toggleMeridian"]');

            buttonHourLink.removeClass("disabled");
            buttonMinuteLink.removeClass("disabled");
            toggleMeridianLink.removeClass("disabled");

            buttonHourLink.off("click");
            buttonMinuteLink.off("click");
            toggleMeridianLink.off("click");
        },

        validateTimeOrder() {
            let minTimeDiff = 0;
            let maxTimeDiff = null;

            if (this.treatment_modality_id) {
                const currentTreatmentModality = this.treatmentModalities.find(
                    (el) => el.id === this.treatment_modality_id,
                );
                if (currentTreatmentModality) {
                    minTimeDiff = currentTreatmentModality.min_duration;
                    maxTimeDiff = currentTreatmentModality.max_duration;
                }
            }

            const start_time = moment(this.start_time, "hh:mm A");
            const end_time = moment(this.end_time, "hh:mm A");
            const timediff = end_time.diff(start_time) / 60 / 1000;

            const isTimeDiffWithinLowerLimit = minTimeDiff ? timediff >= minTimeDiff : true;
            const isTimeDiffWithinUpperLimit = maxTimeDiff ? timediff <= maxTimeDiff : true;

            return isTimeDiffWithinLowerLimit && isTimeDiffWithinUpperLimit;
        },

        resetTime() {
            this.start_time = null;
            this.end_time = null;
        },

        determineButtonAvailability() {
            if ($("#start_time").length > 0) {
                this.enableButtons("increment");
                this.enableButtons("decrement");
                return;
            }

            let minTimeDiff = 0;
            let maxTimeDiff = null;

            if (this.treatment_modality_id) {
                const currentTreatmentModality = this.treatmentModalities.find(
                    (el) => el.id === this.treatment_modality_id,
                );
                if (currentTreatmentModality) {
                    minTimeDiff = currentTreatmentModality.min_duration;
                    maxTimeDiff = currentTreatmentModality.max_duration;
                }
            }

            const start_time = moment(this.start_time, "h:mm A");
            const end_time = moment(this.end_time, "h:mm A");
            const timediff = end_time.diff(start_time) / 60 / 1000;

            if (timediff > minTimeDiff) {
                this.enableButtons("decrement");
            } else {
                this.disableButtons("decrement");
            }

            if (maxTimeDiff && timediff >= maxTimeDiff) {
                this.disableButtons("increment");
            } else {
                this.enableButtons("increment");
            }
        },
    },

    watch: {
        end_time() {
            this.determineButtonAvailability();
        },

        treatment_modality_id() {
            this.updateEndTime();
        },
    },

    computed: {
        startTimePicker() {
            return $("input#start_time_picker");
        },

        endTimePicker() {
            return $("input#end_time_picker");
        },

        treatmentModalities() {
            return this.$store.state.treatmentModalities;
        },

        durationMinutes() {
            let duration = 0;

            if (this.treatment_modality_id) {
                const currentTreatmentModality = this.treatmentModalities.find(
                    (el) => el.id === this.treatment_modality_id,
                );
                if (currentTreatmentModality) {
                    duration = currentTreatmentModality.duration;
                }
            }

            return duration;
        },
    },
};
