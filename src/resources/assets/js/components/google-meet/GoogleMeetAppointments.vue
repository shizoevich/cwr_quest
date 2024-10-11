<template>
    <div class="google-meet-appointments">
      <el-dialog
        width="30%"
        title="Inner Dialog"
        :visible.sync="innerVisible"
        append-to-body>
      </el-dialog>
        <div class="google-meet-appointments-item" v-for="appointmentItem in appointmentsData"
             :key="appointmentItem.id">
            <div class="google-meet-appointments-item__title">
                {{appointmentItem.title}} Appointments
                <template v-if="!appointmentItem.isToday && Number(patient.id) !== 1111">
                    <el-button type="primary" @click.prevent="openAppointmentDialog">Schedule Appointment
                    </el-button>
                </template>
            </div>
            <template v-if="appointmentItem.appointments.length">
                <el-collapse class="collapse-appointments"
                             v-model="activeAppointment">
                    <el-collapse-item class="collapse-appointments-item"
                                      v-for="appointment in appointmentItem.appointments" :key="appointment.id"
                                      :name="appointment.id" :disabled="true"
                                      :class="{'is-invalid': !isAppointmentValid(appointment)}">
                        <template slot="title">
                            <div class="collapse-appointments-item__title">
                                <input type="checkbox" v-model="appointment.checked" v-if="!appointmentItem.isToday"
                                       @change="changeAppointment(appointment, true, appointmentItem.isToday)"/>
                                <div class="collapse-appointments-item__title-description">
                                    <p>
                                        {{appointmentTime(appointment.date_of_service) }}
                                    </p>
                                    <div v-html="appointmentInvite(appointment.google_meet)"/>
                                    <template v-if="appointmentItem.isToday">
                                      <el-checkbox style="margin-top:8px;" v-model="appointment.allow_to_join_by_phone">Allow to Join by Phone</el-checkbox>
                                      <p class="help-block"
                                         style="max-width:300px;margin-top:0;font-weight:normal;font-size:11px;">
                                        Checking the "Allow to Join by Phone" box above will allow patients to join this Telehealth session by dialing a phone number provided and entering a PIN code included in this invitation.</p>
                                    </template>
                                </div>
                                <div class="collapse-appointments-item-control"
                                     :class="{'collapse-appointments-item-control--date-picker': !appointmentItem.isToday}"
                                     v-if="!appointmentItem.isToday">
                                    <div class="collapse-appointments-item-control__title">Schedule Invitation</div>
                                    <el-date-picker
                                            popper-class="appointment-date-picker"
                                            v-model="appointment.date"
                                            format="MM/dd/yyyy hh:mm A"
                                            value-format="MM/dd/yyyy hh:mm A"
                                            @change="changeAppointment(appointment, false, appointmentItem.isToday)"
                                            @blur="resetSelectedRange"
                                            @input="changeTimeInvite(appointment.date, appointment.date_of_service)"
                                            :disabled="!appointment.checked"
                                            :picker-options="pickerOptions(appointment.date_of_service)"
                                            type="datetime"
                                            placeholder="Select date and time">
                                    </el-date-picker>
                                </div>
                                <div v-else
                                     class="collapse-appointments-item-control collapse-appointments-item-control--button">
                                    <template v-if="isJoin(appointment.google_meet)">
                                        <el-button type="primary" @click="controlVideoSession(appointment)">
                                            Join
                                        </el-button>
                                        <el-tooltip class="item" effect="dark" :content="errorSendFormMessage"
                                                    style="margin-left:15px;"
                                                    :disabled="isValidSendForm"
                                                    placement="top">
                                          <div>
                                            <el-button type="primary" @click="resendInvitationAndJoin(appointment)" :disabled="!isValidSendForm">
                                              Resend Invitation & Join
                                            </el-button>
                                          </div>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <el-tooltip class="item" effect="dark" :content="errorSendFormMessage"
                                                    :disabled="isValidSendForm"
                                                    placement="top">
                                            <div>
                                                <el-button type="primary" @click="controlVideoSession(appointment)"
                                                           :disabled="!isValidSendForm">
                                                    Send Invite & Join
                                                </el-button>
                                            </div>
                                        </el-tooltip>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </el-collapse-item>
                </el-collapse>
            </template>
            <template v-else>
                <div class="google-meet-appointments-no-data">
                    No data, please create an appointment first
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    import debounce from "../../helpers/debounce";
    import GoogleMeetAppointments from '../../mixins/google-meet-appointments';

    export default {
        name: 'GoogleMeetAppointments',
        mixins: [GoogleMeetAppointments],
        props: {
            patient: {
                type: Object,
                default() {
                    return {};
                }
            },
            appointments: {
                type: Array,
                default() {
                    return [];
                }
            },
            isStartValid: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                appointmentsData: _.cloneDeep(this.appointments),
                activeAppointment: [],
                selectedAppointments: [],
                initSelectableRange: '00:00:00 - 23:59:00',
                selectableRange: '00:00:00 - 23:59:00',
                innerVisible: false,
            }
        },
        watch: {
            appointments(newValue, oldValue) { // Update the appointment list without change old data
                let newAppointments = newValue.find(item => !item.isToday).appointments,
                    newTodayAppointments = newValue.find(item => item.isToday).appointments,
                    oldAppointments = [];
                if (oldValue.length) {
                    oldAppointments = oldValue.find(item => !item.isToday).appointments;
                    if (!oldValue.find(item => item.isToday).appointments.length && newTodayAppointments.length) {
                        this.appointmentsData.find(item => item.isToday).appointments = newTodayAppointments;
                    } else if (newAppointments.length > oldAppointments.length) {
                        newAppointments.slice(oldAppointments.length, newAppointments.length).forEach(item => {
                            this.appointmentsData.find(item => !item.isToday).appointments.push(item);
                        })
                    } else {
                        this.appointmentsData = newValue;
                    }
                } else {
                    this.appointmentsData = newValue;
                }
            }
        },
        methods: {
            isJoin(googleMeetData) {
                return googleMeetData && googleMeetData.invitations && googleMeetData.invitations.length;
            },
            changeAppointment(appointment, isCheckbox, isToday) {
                let isFind = Boolean(this.selectedAppointments.find(item => Number(item.id) === Number(appointment.id))),
                    index = this.selectedAppointments.map(item => item.id).indexOf(appointment.id),
                    date = appointment.date || null;
                let selectedElement = {id: appointment.id, date: date, isToday: isToday}
                if (!isFind && appointment.checked) {
                    this.selectedAppointments.push(selectedElement);
                }
                if (isFind && !appointment.checked) {
                    this.$delete(this.selectedAppointments, index);
                }
                if (!isCheckbox) {
                    this.selectedAppointments[index].date = appointment.date;
                }
                this.$emit('changeAppointment', {
                    appointments: this.appointmentsData,
                    selectedAppointment: this.selectedAppointments
                })
            },
            changeTimeInvite: debounce(function (timeData, appointmentTimeData) {
                if (new Date(timeData).setHours(0, 0, 0, 0) === new Date().setHours(0, 0, 0, 0)) {
                    this.selectableRange = `${moment(new Date()).format('HH:mm:ss')} - 23:59:00`;
                } else if (moment(appointmentTimeData.date).format('MM/dd/yyyy') === moment(timeData).format('MM/dd/yyyy')) {
                    this.selectableRange = `00:00:00 - ${moment(appointmentTimeData.date + ' ' + appointmentTimeData.time).format('HH:mm:ss')}`
                } else {
                    this.selectableRange = this.initSelectableRange;
                }
            }, 200),
            resetSelectedRange() {
                this.selectableRange = this.initSelectableRange;
            },
            openAppointmentDialog() {
                this.$emit('openAppointmentDialog');
            },
            isAppointmentValid(appointment) {
                if (this.isStartValid) {
                    if (appointment.checked) {
                        return appointment.date;
                    } else {
                        return true;
                    }
                }
                return true;
            },
            resendInvitationAndJoin(appointment) {
              this.$emit('sendInvite', {id: appointment.id, date: null, allow_to_join_by_phone: appointment.allow_to_join_by_phone});
            },
            controlVideoSession(appointment) {
                let googleMeetData = appointment.google_meet,
                    selectAppointment = {id: appointment.id, date: null, allow_to_join_by_phone: appointment.allow_to_join_by_phone};
                googleMeetData && googleMeetData.invitations && googleMeetData.invitations.length ? this.$emit('startVideoSession', googleMeetData.conference_uri) : this.$emit('sendInvite', selectAppointment)
            },
            pickerOptions(timeData) {
                let appointmentsDate = moment(timeData.date + ' ' + timeData.time).format('x');
                return {
                    disabledDate(time) {
                        return time.getTime() < Date.now() - 8.64e7 || time.getTime() > appointmentsDate;
                    },
                    selectableRange: this.selectableRange
                };
            },
        }
    }
</script>

<style lang="scss">
    .google-meet-appointments {

        &-item {

            .collapse-appointments {
                max-height: 350px;
                overflow-y: auto;
                margin: 0 -15px;
                padding: 10px 15px 30px;

                &-item {
                    &.is-invalid {

                        .el-date-editor {

                            .el-input__inner {
                                border-color: #F56C6C;
                            }
                        }
                    }

                    &__title {
                        width: 100%;
                        align-items: center;

                        p {
                            margin-bottom: 8px;
                        }
                    }

                    &-control {
                        width: 100%;
                        max-width: 220px;
                        margin-left: auto;
                        padding: 10px 15px;

                        &--date-picker {
                            padding: 10px 15px 15px;
                        }

                        @media (min-width: 930px) {
                            max-width: 285px;
                        }

                        &--button {
                            display: flex;
                            justify-content: flex-end;
                        }

                        &__title {
                            margin-bottom: 5px;
                        }
                    }
                }
            }

            &:last-of-type {
                margin-bottom: 0;
            }

            &__title {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 15px;
                margin-bottom: 15px
            }
        }

        &-no-data {
            font-size: 15px;
            text-align: center;
            padding: 30px 0;
        }

        .text {

            &-success {
                color: #67C23A;
            }

            &-primary {
                color: #409EFF;
            }

            &-danger {
                color: #F56C6C;
            }
        }
    }

    .appointment-date-picker {

        .el-picker-panel__footer {

            button {
                &:first-of-type {
                    display: none;
                }
            }
        }
    }
</style>
