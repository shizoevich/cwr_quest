<template>
    <div>
        <el-dialog :title="getDateTimeWorkString()" :visible.sync="showDoctorsModal" class="doctor-modal bootstrap-modal">
            <div class="doctor-modal-body">
                <div class="doctor-modal-row">
                    <el-table :data="getProvidersByScore(openEvent.info)" style="width: 100%" :cell-style="{padding: '5px 0'}">
                        <el-table-column prop="provider" label="Provider">
                            <template slot-scope="scope">
                                <div :style="{ backgroundColor: scope.row.availability_subtype ? scope.row.availability_subtype.hex_color : scope.row.availability_type.hex_color}" class="provider-name">
                                    {{ scope.row.provider.provider_name }}
                                    <template v-if="scope.row.virtual">
                                        <i class="fa fa-video-camera" title="Virtual"></i>
                                    </template>
                                    <template v-if="scope.row.in_person">
                                        <i class="fa fa-user" title="In Person"></i>
                                    </template>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column prop="created_at" label="Created at">
                            <template slot-scope="scope">
                                {{ getFormattedDateTime(scope.row.created_at) }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="time" label="Time">
                            <template slot-scope="scope">
                                <ul class="insurance-list">
                                    <li v-for="time in getProviderFreeTime(scope.row)">
                                        {{ time }}
                                    </li>
                                </ul>
                            </template>
                        </el-table-column>
                        <el-table-column prop="availability_type" label="Instructions">
                            <template slot-scope="scope">
                                <a v-if="scope.row.availability_subtype" role="button" @click.stop.prevent="availabilityTypeClick(scope.row)">
                                    View
                                </a>
                                <span v-else>-</span>
                            </template>
                        </el-table-column>
                        <el-table-column v-if="!isDayBefore" label="Appointment" width="200">
                            <template slot-scope="scope">
                                <el-button
                                    type="primary"
                                    size="small"
                                    :disabled="scope.row.availability_subtype && isUnavailableAvailabilitySubtype(scope.row.availability_subtype.id)"
                                    @click="addAppointment(scope.row)"
                                >
                                    Schedule Appointment
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </el-dialog>

        <el-dialog
            title="Availability special instructions"
            :visible.sync="showAvailabilitySubtypeInfoModal"
            width="30%"
            class="doctors-confirmation-window"
            :modal-append-to-body="false"
        >
            <div v-if="availabilitySubTypeInfo" class="d-flex flex-column" style="gap: 10px;word-break: break-word;">
                <div class="d-flex">
                    <div style="width: 135px;flex-shrink: 0;">
                        Instructions:
                    </div>
                    <div>
                        {{ availabilitySubTypeInfo.type }}
                    </div>
                </div>
                <div v-if="availabilitySubTypeInfo.comment" class="d-flex">
                    <div style="width: 135px;flex-shrink: 0;">
                        Comment:
                    </div>
                    <div style="flex: 1">
                        {{ availabilitySubTypeInfo.comment }}
                    </div>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script>

import DatetimeFormated from '../../mixins/datetime-formated';
import { UNAVAILABLE_AVAILABILITY_SUBTYPE_ID } from '../../settings';

export default {
    mixins: [DatetimeFormated],
    props: ["events", "providersScore", "showDialog"],
    name: "DoctorsModal",
    data() {
        return {
            openEvent: this.events,
            showAvailabilitySubtypeInfoModal: false,
            availabilitySubTypeInfo: null
        };
    },
    computed: {
        providers() {
            return this.providersScore;
        },
        offices() {
            return this.$store.state.offices;
        },
        showDoctorsModal: {
            get() {
                return this.showDialog;
            },
            set(value) {
                if (!value) {
                    this.$emit("closeDialog");
                }
            },
        },
        isDayBefore() {
            return moment(this.openEvent.start._a).isBefore(
                moment(new Date()),
                "day"
            );
        },
    },
    methods: {
        addAppointment(availability) {
            const data = Object.assign({}, this.events, {
                selectedAvailabilityId: availability.id,
            });
            this.$emit("openDialogAppointment", data);
            this.showDoctorsModal = false;
        },
        stringifyArray(rooms) {
            if (rooms[0] == "") {
                return rooms.slice(1).join(", ");
            } else {
                return rooms.join(", ");
            }
        },
        getDateTimeWorkString() {
            if (this.openEvent != null) {
                return this.openEvent.start.format("ddd MM/DD/YYYY h:mm A");
            } else {
                return "";
            }
        },
        makeTimeString(hours, minutes) {
            let a = "AM";
            if (typeof minutes === "undefined") {
                minutes = 0;
            }
            if (hours > 12) {
                a = "PM";
                hours -= 12;
            } else if (hours === 12) {
                a = "PM";
            }

            return hours + ":" + (minutes < 10 ? "0" : "") + minutes + " " + a;
        },

        getProviderFreeTime(provider) {
            let timeHours = [];
            let result = [];
            let curHour = moment(provider.date.date);
            let sH = curHour.hours();
            let sM = curHour.hours() * 60 + curHour.minutes();

            let eM = sM + provider.length;
            let endHour = moment(provider.date.date).add(provider.length, "minute");
            let eH = endHour.hours();
            for (let i = sH; i <= eH; i++) {
                timeHours.push((i < 10 ? "0" : "") + i + ":00:00");
            }

            let t_timeHours = [];
            t_timeHours.push({
                type: "work",
                flag: "start",
                minutes: sM,
                hours: sH,
                string: this.makeTimeString(sH, curHour.minutes()),
            });
            t_timeHours.push({
                type: "work",
                flag: "end",
                minutes: eM,
                hours: eH,
                string: this.makeTimeString(eH, endHour.minutes()),
            });

            if (
                typeof this.appointments !== "undefined" &&
                typeof this.appointments[provider.office_id] !== "undefined" &&
                typeof this.appointments[provider.office_id][provider.provider_id] !==
                "undefined"
            ) {
                let provider_date = provider.date.date.substr(0, 10);
                let t_appointments =
                    this.appointments[provider.office_id][provider.provider_id][
                    provider_date
                    ];
                if (typeof t_appointments !== "undefined") {
                    $.each(t_appointments, (dat, appointments) => {
                        $.each(appointments, (i, appointment) => {
                            let start = moment(appointment.time * 1000);
                            let end = moment(appointment.time * 1000).add(
                                appointment.visit_length,
                                "minute"
                            );
                            t_timeHours.push({
                                type: "appointment",
                                flag: "start",
                                minutes: start.hours() * 60 + start.minutes(),
                                hours: start.hours(),
                                string: this.makeTimeString(start.hours(), start.minutes()),
                            });
                            t_timeHours.push({
                                type: "appointment",
                                flag: "end",
                                minutes: end.hours() * 60 + end.minutes(),
                                hours: end.hours(),
                                string: this.makeTimeString(end.hours(), end.minutes()),
                            });
                        });
                    });
                }
            }

            let deleted = 0;
            do {
                deleted = 0;
                for (let j = 0; j < t_timeHours.length - 1; j++) {
                    if (
                        t_timeHours[j].minutes === t_timeHours[j + 1].minutes &&
                        t_timeHours[j].type === t_timeHours[j + 1].type &&
                        t_timeHours[j].flag === "end" &&
                        t_timeHours[j + 1].flag === "start" &&
                        deleted == 0
                    ) {
                        t_timeHours.splice(j, 2);
                        deleted++;
                    }
                }
            } while (deleted > 0);

            t_timeHours = t_timeHours.sort((timeA, timeB) => {
                return timeA.minutes > timeB.minutes;
            });

            for (let j = 0; j < t_timeHours.length; j++) {
                if (
                    t_timeHours[j].type === "appointment" &&
                    t_timeHours[j].flag === "start"
                ) {
                    t_timeHours[j].type = "work";
                    t_timeHours[j].flag = "end";
                } else if (
                    t_timeHours[j].type === "appointment" &&
                    t_timeHours[j].flag === "end"
                ) {
                    t_timeHours[j].type = "work";
                    t_timeHours[j].flag = "start";
                }
            }

            for (let j = 0; j < t_timeHours.length - 1; j++) {
                if (
                    (t_timeHours[j].type === "work" &&
                        t_timeHours[j].flag === "start" &&
                        t_timeHours[j + 1].type === "work" &&
                        t_timeHours[j + 1].flag === "end") ||
                    (t_timeHours[j].type === "work" &&
                        t_timeHours[j].flag === "start" &&
                        t_timeHours[j + 1].type === "appointment" &&
                        t_timeHours[j + 1].flag === "start") ||
                    (t_timeHours[j].type === "appointment" &&
                        t_timeHours[j].flag === "end" &&
                        t_timeHours[j + 1].type === "work" &&
                        t_timeHours[j + 1].flag === "end")
                ) {
                    if (t_timeHours[j].string !== t_timeHours[j + 1].string) {
                        result.push(
                            t_timeHours[j].string + " - " + t_timeHours[j + 1].string
                        );
                    }
                }
            }

            return result;
        },

        getProvidersByScore(providers) {
            if (
                typeof providers != "undefined" &&
                typeof providers.map != "undefined"
            ) {
                providers = providers.map((provider) => {
                    var weekNum = moment(provider.date).week();

                    if (typeof this.providers[provider.provider_id] != "undefined") {
                        provider.score = this.providers[provider.provider_id]["score"];
                    } else {
                        provider.score = 0;
                    }
                    return provider;
                });

                providers = providers.sort((providerA, providerB) => {
                    return providerA.score < providerB.score;
                });
            }
            return providers;
        },

        availabilityTypeClick(availability) {
            if (availability.availability_subtype) {
                const subtypeInfo = {
                    type: availability.availability_subtype.type,
                    comment: availability.comment
                }
                this.availabilitySubTypeInfo = subtypeInfo;
                this.showAvailabilitySubtypeInfoModal = true;
            }
        },

        isUnavailableAvailabilitySubtype(type) {
            return type === UNAVAILABLE_AVAILABILITY_SUBTYPE_ID;
        }
    },
    watch: {
        showAvailabilitySubtypeInfoModal(val) {
            if (!val) {
                this.availabilitySubTypeInfo = null;
            }
        }
    }
};
</script>

<style lang="scss">
.doctor-modal {
    .el-dialog {
        width: 95%;
        max-width: 1000px;
    }

    &-row {
        margin-bottom: 25px;

        & .provider-name {
            padding: 3px 8px;
            border-radius: 15px;
            color: #fff;
        }

        &:last-of-type {
            margin-bottom: 0;
        }
    }

    &__title {
        font-size: 16px;
        margin-bottom: 5px;
    }

    .insurance-list {
        padding-left: 15px;
        margin-bottom: 0;

        li {
            font-size: 1.2rem;
        }
    }

    .clickable {
        cursor: pointer;
    }

    .clickable:hover {
        background: rgb(225, 172, 49) !important;
    }
}

.doctors-confirmation-window {
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 50px;
  padding-bottom: 15vh;
}
</style>
