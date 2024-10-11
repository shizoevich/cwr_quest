<template>
    <div class="container" v-loading.fullscreen.lock="isLoading">
        <div class="row">
            <div class="col-sm-12 content-wrapper">
                <h1 class="salary-title text-center mb-small">
                    {{provider.provider_name}}
                </h1>
                <h2 class="salary-subtitle text-center">
                    Billing Period: {{billingPeriod.date}}
                </h2>
                <table-timesheet
                    :init-tables-data="tablesData"
                    :is-disabled="isDisabled"
                    @changeTable="changeTablesData"
                />

                <div v-show="provider.is_supervisor">
                    <div class="salary-table-item">
                        <div class="salary-table-title">Supervision</div>
                        <supervisions
                            :init-form-data="supervisionsData"
                            :changeData="changeSupervisionsFormData"
                            :is-editing-allowed="!isDisabled"
                        />
                    </div>
                </div>

                <div class="salary-table-item">
                    <div class="salary-table-title">Comments & Corrections Requested</div>
                    <p>{{ complaint || '-' }}</p>
                </div>
                
                <sick-time
                    :init-form-data="sickTimeData"
                    :billing-period="billingPeriod"
                    :provider-id="provider.id"
                    :show-description="false"
                    :is-editing-allowed="!isDisabled"
                    @changeData="changeSickTimeFormData"
                />
                <div class="salary-footer">
                    <el-button type="warning" slot="reference" @click="backButton">
                        Back
                    </el-button>
                    <el-popconfirm title="Are you sure to complete this?" @confirm="completeTimesheet">
                        <el-button type="primary" slot="reference" :title="reviewedData" :disabled="isDisabled">
                            Complete
                        </el-button>
                    </el-popconfirm>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TableTimesheet from './../components/TableTimesheet';
    import SickTime from "../../salary/time-records/SickTime";
    import Supervisions from "../../salary/time-records/Supervisions";
    import {Notification} from "element-ui";

    export default {
        name: 'Timesheet',
        components: {
            TableTimesheet,
            SickTime,
            Supervisions
        },
        data() {
            return {
                timeSheetId: '',
                isLoading: false,
                provider: {},
                billingPeriod: {},
                tablesData: [],
                supervisionsData: [],
                supervisionsFormData: [],
                sickTimeData: {},
                sickTimeFormData: {},
                complaint: null,
                reviewedAt: null,
                signedAt: null,
            }
        },
        computed: {
            reviewedData() {
                if (this.reviewedAt) {
                    return `Reviewed at ${moment(this.reviewedAt).format('MM/DD/YYYY')}`
                }
                return '';
            },
            isDisabled() {
                return this.reviewedAt !== null || this.signedAt === null;
            },
        },
        mounted() {
            this.initTimesheetData();
        },
        methods: {
            backButton() {
                this.$router.go(-1)
            },
            initTimesheetData() {
                this.isLoading = true;
                this.timeSheetId = this.$route.params.id;
                let payload = {
                    timeSheetId: this.$route.params.id,
                    show_only_therapist_changes: 0
                }
                this.$store.dispatch('getTimesheetData', payload)
                    .then(({data}) => {
                        this.provider = data.provider;
                        this.complaint = data.timesheet.complaint;
                        this.reviewedAt = data.timesheet.reviewed_at;
                        this.signedAt = data.timesheet.signed_at;
                        this.billingPeriod = this.initBillingPeriodData(data.billing_period);
                        this.supervisionsData = data.supervisions;
                        this.sickTimeData = this.initSickTimeData(data);
                        this.tablesData = this.initTableData({
                            visits: data.visits,
                            late_cancellations: data.late_cancellations
                        });
                    })
                    .catch(() => {
                        this.$message({
                            type: 'error',
                            message: 'Oops, something went wrong!',
                            duration: 10000,
                        });
                    })
                    .finally(() => this.isLoading = false)
            },
            initBillingPeriodData(period) {
                let billingPeriodData = {
                    start_date: moment(period.start_date).format('MM/DD/YYYY'),
                    end_date: moment(period.end_date).format('MM/DD/YYYY'),
                    id: period.id,
                };
                if (period.type.name === 'monthly') {
                    billingPeriodData.date = moment(billingPeriodData.start_date).format('MMMM YYYY');
                    return billingPeriodData;
                }
                billingPeriodData.date = billingPeriodData.start_date + ' - ' + billingPeriodData.end_date;
                return billingPeriodData;
            },
            initSickTimeData(data) {
                const sick_times = data.sick_times.map(item => {
                    const appointments = item.appointments.map(appointment => {
                        return {
                            id: appointment.id,
                            label: moment.unix(appointment.time).format('hh:mm A') + ' - ' + appointment.patient.patient_name,
                            visit_length: appointment.visit_length / 60,
                        }
                    });

                    return {
                        date: item.date,
                        selectedAppointments: appointments,
                        appointments: appointments,
                        isLoadingAppointments: false,
                        placeholder: '',
                    };
                });

                return {
                    sick_times: sick_times,
                    sick_time_hours: data.timesheet.seek_time,
                    monthly_meeting_attended: data.timesheet.monthly_meeting_attended,
                    complaint: data.timesheet.complaint,
                }
            },
            initTableData(data) {
                let initTableData = [];
                for (const dataKey in data) {
                    if (data[dataKey].length) {
                        data[dataKey].forEach(item => {
                            item.date = moment(item.date).format('MM/DD/YYYY');
                            item.patient_name = item.patient.first_name + ' ' + item.patient.last_name + ' ' + item.patient.middle_initial
                        })
                    }
                    initTableData.push(
                        {
                            "title": dataKey === 'late_cancellations' ? 'Late Cancellation Fee Charges' : 'Visits',
                            "slug": dataKey,
                            "name": dataKey === 'late_cancellations' ? 'late-cancellations' : 'visits',
                            "isOvertime": dataKey !== 'late_cancellations',
                            "isAmount": dataKey === 'late_cancellations',
                            "data": data[dataKey]
                        },
                    )
                }
                return initTableData;
            },
            changeTablesData(isShowOnlyChanged) {
                this.isLoading = true;
                let payload = {
                    timeSheetId: this.$route.params.id,
                    show_only_therapist_changes: Number(isShowOnlyChanged)
                }
                this.$store.dispatch('getTimesheetData', payload)
                    .then(({data}) => {
                        this.tablesData = this.initTableData({
                            visits: data.visits,
                            late_cancellations: data.late_cancellations
                        });
                    })
                    .catch(() => {
                        this.$message({
                            type: 'error',
                            message: 'Oops, something went wrong!',
                            duration: 10000,
                        });
                    })
                    .finally(() => this.isLoading = false)
            },
            changeSupervisionsFormData(data) {
                this.supervisionsFormData = data;
            },
            changeSickTimeFormData(data) {
                this.sickTimeFormData = data;
            },
            completeTimesheet() {
                this.isLoading = true;
                this.sickTimeFormData.billing_period_id = this.billingPeriod.id;
                this.sickTimeFormData.supervisions = this.supervisionsFormData;

                let payload = {
                    id: this.timeSheetId,
                    data: this.sickTimeFormData
                }

                this.$store.dispatch('completeTimesheet', payload)
                    .then(() => {
                        Notification.success({
                            title: 'Success',
                            message: 'Timesheet was complete successfully',
                            type: 'success'
                        });
                        this.$router.push('/dashboard/timesheets');
                    })
                    .catch(() => {
                        this.$message({
                            type: 'error',
                            message: 'Oops, something went wrong!',
                            duration: 10000,
                        });
                    })
                    .finally(() => this.isLoading = false)
            }
        },
    }
</script>