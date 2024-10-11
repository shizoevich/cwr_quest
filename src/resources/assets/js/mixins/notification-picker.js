import debounce from "../helpers/debounce.js";

export default {
    computed: {
        initSelectableRange() {
            return "06:00:00 - 21:00:00";
        },
    },

    data() {
        return {
            notificationPickerOptions: {},
        };
    },

    methods: {
        updateNotificationPickerOptions: debounce(function () {
            const dateUnix = moment(this.ruleForm.date).unix() * 1000;

            let selectableRange = this.initSelectableRange;
            if (
                this.ruleForm.telehealth_notification_date &&
                this.ruleForm.date &&
                this.ruleForm.telehealth_notification_date.replace(
                    /(\d{2}\/\d{2}\/\d{4}).*/,
                    "$1",
                ) ===
                    this.ruleForm.date.replace(/(\d{2}\/\d{2}\/\d{4}).*/, "$1")
            ) {
                selectableRange = `06:00:00 - ${moment(
                    this.ruleForm.time,
                    "h:mm A",
                ).format("HH:mm:ss")}`;
            }

            this.notificationPickerOptions = {
                disabledDate(time) {
                    return (
                        time.getTime() < Date.now() - 8.64e7 ||
                        time.getTime() > dateUnix
                    );
                },
                selectableRange,
            };
        }, 500),

        updateTelehealthNotificationDate() {
            if (!this.ruleForm.date || !this.ruleForm.time) {
                return;
            }

            let datetime = moment(
                `${this.ruleForm.date} ${this.ruleForm.time}`,
                "MM/DD/yyyy h:mm A",
            ).subtract(1, "hours");
            this.$set(
                this.ruleForm,
                "telehealth_notification_date",
                datetime.format("MM/DD/yyyy hh:mm A"),
            );
        },
    },

    watch: {
        "ruleForm.telehealth_notification_date"() {
            this.updateNotificationPickerOptions();
        },

        "ruleForm.date"() {
            this.updateTelehealthNotificationDate();
        },

        "ruleForm.time"() {
            this.updateTelehealthNotificationDate();
        },
    },
};
