<template>
    <div class="sick-time">
        <el-form :model="formData" ref="sickTimeForm">
            <el-form-item
                v-if="isUserAdmin || isUserSecretary"
                class="form-row form-row--number"
                label="Sick time"
                prop="sick_times"
            >
                <div class="sick-time__list">
                    <div
                        v-for="(sickTime, index) in sickTimeList"
                        class="sick-time__item"
                    >
                        <div class="sick-time__el-date-picker-wrap">
                            <el-date-picker
                                v-model="sickTime.date"
                                :disabled="!isEditingAllowed"
                                :placeholder="isEditingAllowed ? 'Choose a date' : ''"
                                format="MM/dd/yyyy"
                                value-format="yyyy-MM-dd"
                                :default-value="startDate"
                                :picker-options="datePickerOptions"
                                type="date"
                                @change="() => handleDatePickerChange(sickTime)"
                            />
                        </div>

                      <div class="sick-time_multiselect-wrap">
                            <multiselect
                                v-model="sickTime.selectedAppointments"
                                :disabled="!isEditingAllowed || !sickTime.date || sickTime.isLoadingAppointments"
                                class="sick-time_multiselect"
                                track-by="id"
                                label="label"
                                placeholder="Select appointments"
                                :options="sickTime.appointments"
                                :multiple="true"
                                :close-on-select="false"
                                :searchable="false"
                                :internal-search="false"
                                :loading="sickTime.isLoadingAppointments"
                                :showLabels="false"
                            />
                      </div>

                      <div v-if="isEditingAllowed">
                        <div v-if="index === 0">
                            <i class="fa fa-plus fa-lg fa-relationship-button" @click.prevent="addSickTime"/>
                        </div>
                        <div v-else>
                            <i class="fa fa-close fa-lg fa-relationship-button" @click.prevent="() => deleteSickTime(index)"/>
                        </div>
                      </div>
                  </div>
                </div>

                <div>
                    Total sick time (hours): {{ totalSickTimeHours }}
                </div>

                <span v-if="this.isEditingAllowed && this.isRemainingSickHoursSet" class="help-block" style="line-height: 1.5;">
                    {{ remainingSickHours }} hours left this year
                </span>
            </el-form-item>

            <div v-if="isUserAdmin || isUserSecretary">
                <el-form-item
                    v-for="checkbox in checkboxList"
                    :prop="checkbox.name"
                    :key="checkbox.id"
                    class="form-row form-row--checkbox"
                >
                    <el-checkbox
                        v-model="formData[checkbox.name]"
                        :disabled="!isEditingAllowed"
                        :class="{ 'is-error': isInvalidField(checkbox.name) }"
                    >
                        {{ checkbox.description }}
                    </el-checkbox>
                </el-form-item>
            </div>

            <template v-if="showDescription">
                <p>
                    <b>As part of our commitment to accurate and transparent reporting, please review your timesheet carefully. Below is a legally binding attestation regarding the accuracy of your recorded work hours. If there are any discrepancies or additional information you need to report, please use the field provided before confirming your submission.</b>
                </p>

                <ul class="bullet-list">
                    <li v-for="bullet in bulletList" :key="bullet.id">
                        {{ bullet.description }}
                    </li>
                </ul>
            </template>
            
            <div class="form-group" v-if="this.$router.currentRoute.name !== page">
                <div class="col-10">
                    <label for="complaint" class="col-10 control-label">
                        Report Discrepancies or Additional Details: <br />
                        <span style="font-weight: normal;">
                            If you identify any variations from your actual work hours or missed information in this 
                            timesheet, please provide specific details below. Your input is essential for maintaining 
                            accurate records.
                        </span>
                    </label>
                    <textarea
                        id="complaint"
                        v-model="formData.complaint"
                        class="form-control"
                        rows="8"
                    ></textarea>
                </div>
            </div>

            <p v-if="showDescription">
                <b>I am submitting this timesheet of my own free will and understand the contents of this document completely. I acknowledge that submitting this timesheet is not a condition of my continued employment, and that my decision to submit or refrain from submitting this timesheet will have no impact or effect on my employment with the Company. I declare under penalty of perjury within the state of California that the information I have provided is true and correct.</b>
            </p>
        </el-form>
    </div>
</template>

<script>
import Multiselect from 'vue-multiselect';
import {getDateString} from "../../../helpers/date";

export default {
    name: "SickTime",
    components: {
        Multiselect,
    },
    props: {
        requiredFields: {
            type: Array,
            default() {
                return [];
            },
        },
        showDescription: {
            type: Boolean,
            default: true,
        },
        billingPeriod: {
            type: Object,
            default() {
                return {};
            },
        },
        providerId: {
            type: Number,
        },
        initFormData: {
            type: Object,
            default() {
                return {};
            },
        },
        isEditingAllowed: {
            type: Boolean,
            default: true,
        },
        remainingSickHours: {
            type: Number
        }
    },
    data() {
        return {
            page: "timesheet",
            isAdmin: false,
            isSecretary: false,
            checkboxList: [
                {
                    id: 1,
                    name: "monthly_meeting_attended",
                    description: "Monthly Phone call attended",
                },
            ],
            bulletList: [
                {
                    id: 4,
                    name: "completed_timesheet",
                    description: "I hereby attest that the treatment sessions, time, hours and other information recorded on this document accurately and fully identify all the time and work I have performed during this pay period. I have not worked during times other than reflected on this document. I further attest that Change Within Reach has provided me with all meal periods and rest periods to which I am entitled under the law during this pay period. This includes one duty-free rest period of ten-minutes for every four hours of work or major fraction thereof. This also includes one duty-free meal period of thirty minutes whenever I worked five or more hours, which must begin before my fifth hour of work. (Employees are also entitled to a second duty-free meal periods of thirty minutes after working ten or more hours.) These breaks have been uninterrupted, free from all work-duties and free from control of Change Within Reach. I understand that it is my responsibility to take my meal and rest breaks, and that Change Within Reach cannot ensure that I take these breaks. I further acknoweldge that if I chose not to take a rest period or meal period, it was my personal choice and preference and I was not prevented from taking such breaks by Change Within Reach. I further attest that I have not violated any policy of the employer during the pay period, including, but not limited to, the employer’s policy against working unauthorized overtime or off-the-clock hours.",
                },
            ],
            formData: {
                monthly_meeting_attended: false,
                sick_times: [],
                sick_time_hours: null,
                complaint: "",
            },
            sickTimeList: [
                {
                    date: null,
                    selectedAppointments: [],
                    appointments: [],
                    isLoadingAppointments: false,
                },
            ],
        };
    },
    watch: {
        initFormData: {
            handler(value) {
                const initFormData = _.cloneDeep(value);

                this.formData.monthly_meeting_attended = initFormData.monthly_meeting_attended;
                if (initFormData.sick_times.length) {
                    this.sickTimeList = initFormData.sick_times;
                }
                this.formData.sick_time_hours = initFormData.sick_time_hours;
                this.formData.complaint = initFormData.complaint;
            },
            deep: true,
        },
        formData: {
            handler(value) {
                this.$emit("changeData", value);
            },
            deep: true,
        },
        formDataSickTime(value) {
            this.formData.sick_times = value;
        },
    },
    mounted() {
        this.$store.dispatch('getUserRoles');
        this.$store.dispatch('getCancelAppointmentStatuses');
    },
    computed: {
        isUserAdmin() {
            return this.$store.state.isUserAdmin;
        },
        isUserSecretary() {
            return this.$store.state.isUserSecretary;
        },
        isRemainingSickHoursSet() {
            return this.remainingSickHours !== null && this.remainingSickHours !== undefined;
        },
        startDate() {
            return new Date(this.billingPeriod.start_date);
        },
        endDate() {
            return new Date(this.billingPeriod.end_date);
        },
        datePickerOptions() {
            const startDate = this.startDate;
            const endDate = this.endDate;
            const selectedDates = this.sickTimeList.map(item => item.date);

            return {
                disabledDate(date) {
                    if (date < startDate || date > endDate) {
                        return true;
                    }

                    const dateString = getDateString(date);

                    return selectedDates.findIndex(item => item === dateString) !== -1;
                },
            }
        },
        totalSickTimeHours() {
            if (!this.isEditingAllowed) {
                return this.formData.sick_time_hours;
            }

            let totalHours = 0;
            for (let i = 0; i < this.sickTimeList.length; i++) {
                for (let j = 0; j < this.sickTimeList[i].selectedAppointments.length; j++) {
                    totalHours += this.sickTimeList[i].selectedAppointments[j].visit_length;
                }
            }

            return totalHours;
        },
        appointmentStatusIdCancelledByProvider() {
            return this.$store.state.appointment_cancel_statuses.find(item => item.status === "Cancelled by Provider").id;
        },
        formDataSickTime() {
            let data = [];

            for (let i = 0; i < this.sickTimeList.length; i++) {
                if (!this.sickTimeList[i].date || !this.sickTimeList[i].selectedAppointments.length) {
                    continue;
                }

                data.push({
                    date: this.sickTimeList[i].date,
                    appointments: this.sickTimeList[i].selectedAppointments.map(appointment => appointment.id),
                });
            }

            return data;
        },
    },
    methods: {
        isInvalidField(checkboxName) {
            return (
                !this.formData[checkboxName] &&
                this.requiredFields.indexOf(checkboxName) > -1
            );
        },

        handleDatePickerChange(sickTime) {
            this.resetSickTimeData(sickTime);

            if (! sickTime.date) {
                return;
            }

            sickTime.isLoadingAppointments = true;

            const params = {
                date: sickTime.date,
                providers_id: this.providerId,
                appointment_statuses: [this.appointmentStatusIdCancelledByProvider],
            }

            this.$store.dispatch('getAppointmentList', params)
                .then((response) => {
                    if (sickTime.date) {
                        sickTime.appointments = response.data.appointments.map(item => ({
                          id: item.id,
                          label: moment.unix(item.time).format('hh:mm A') + ' - ' + item.patient_name,
                          visit_length: item.visit_length / 60,
                        }));
                    }

                    sickTime.isLoadingAppointments = false;
                });
        },

        resetSickTimeData(sickTime) {
            sickTime.selectedAppointments = [];
            sickTime.appointments = [];
            sickTime.isLoadingAppointments = false;
        },

        addSickTime() {
            this.sickTimeList.push({
                date: null,
                selectedAppointments: [],
                appointments: [],
                isLoadingAppointments: false,
            });
        },

        deleteSickTime(index) {
            this.sickTimeList.splice(index, 1);
        },
    },
};
</script>

<style lang="scss">
.sick-time {
    .form-row {
        &--number {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            margin-bottom: 30px;
        }

        &--checkbox {
            .el-checkbox {
                display: flex;
                align-items: flex-start;
                white-space: normal;

                &.is-error {
                    .el-checkbox__inner {
                        border-color: #f56c6c;
                    }

                    .el-checkbox__label {
                        color: #f56c6c;
                    }
                }
            }

            .el-checkbox__input {
                margin-top: 3px;
            }
        }
    }

    .bullet-list {
        padding-left: 0;
        list-style: none;
        //li {
        //position: relative;
        //padding-left: 15px;
        //margin-bottom: 10px;
        //color: #606266;
        //font-weight: 500;
        //line-height: 19px;
        //
        //    &::before {
        //        content: "";
        //        position: absolute;
        //        top: 5px;
        //        left: 0;
        //        width: 5px;
        //        height: 5px;
        //        border-radius: 50%;
        //        background: #606266;
        //    }
        //}

        li:not(:last-child) {
            margin-bottom: 11px;
        }
    }

    .sick-time__list {
        display: flex;
        flex-direction: column;
        gap: 6px;

        .sick-time__item {
            display: flex;
            gap: 8px;

            .sick-time__el-date-picker-wrap {
                width: 180px;
            }
        }
    }

    .sick-time_multiselect-wrap {
        .sick-time_multiselect {
            min-width: 350px;
            max-width: 350px;

            &.multiselect--disabled {
                border-radius: 5px;
                cursor: not-allowed;
                pointer-events: all;

                * {
                    pointer-events: none;
                }

                .multiselect__tag-icon {
                    display: none;
                }
            }

            &.multiselect--active {
                .multiselect__tags {
                    border-top-left-radius: 5px;
                    border-top-right-radius: 5px;
                    border-bottom-left-radius: 0;
                    border-bottom-right-radius: 0;
                }
            }

            .multiselect__content-wrapper {
                max-height: 250px !important;
                bottom: auto;
                border: 1px solid #e8e8e8;
                border-top: none;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
                border-bottom-left-radius: 5px;
                border-bottom-right-radius: 5px;
            }

            .multiselect__tag, .multiselect__option--selected, .multiselect__option--highlight, .multiselect__tag-icon:hover {
                background: #43A2F3;
            }

            .multiselect__spinner:before, .multiselect__spinner:after {
                border-top-color: #43A2F3;
            }

            .multiselect__tags {
                line-height: normal;
                border: 1px solid #DCDFE6;
                transition: border-color .2s cubic-bezier(.645,.045,.355,1);

                &:hover {
                    border: 1px solid #C0C4CC;
                }

                .multiselect__tags-wrap {
                    display: flex;
                    flex-flow: column wrap;
                }

                .multiselect__placeholder {
                    padding-top: 0;
                    color: #606266;
                    opacity: 0.5;
                }
            }

            .multiselect__select {
                display: none;
            }

            .multiselect__spinner {
                border-radius: 5px;
            }
        }
    }
}
</style>