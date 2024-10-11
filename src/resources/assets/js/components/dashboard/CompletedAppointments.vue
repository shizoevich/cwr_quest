<template>
    <div>
        <div class="page-loader-wrapper text-center" v-if="loading.appointments">
            <pageloader add-classes="page-loader" />
        </div>

        <div v-else>
            <div class="row">
                <div class="col-xs-9">
                    <form action="" method="post" @submit.prevent="">
                        <input type="hidden" name="_token" :value="csrfToken">
                        <div class="form-group date-filter-item">
                            <label>Therapist Name</label>
                            <select class="form-control" v-model="selected_provider" name="selected_provider">
                                <option value="-1">All</option>
                                <option v-for="provider in provider_list" :value="provider.id">
                                    {{provider.provider_name}}
                                </option>
                            </select>
                        </div>
                        <div class="form-group date-filter-item">
                            <label>Filter By</label>
                            <select class="form-control" v-model="selected_filter_type" name="selected_filter_type">
                                <option value="1">Date</option>
                                <option value="2">Date Range</option>
                                <option value="3">Month</option>
                            </select>
                        </div>

                        <div class="form-group date-filter-item" v-if="selected_filter_type == 1 || selected_filter_type == 2">
                            <label v-if="selected_filter_type == 1">Date</label>
                            <label v-if="selected_filter_type == 2">From</label>
                            <ElDatePicker class="date-filter date-filter-2"
                                          v-model="date_from"
                                          name="date_from"
                                          :format="date_format"
                                          :value-format="date_format"
                                          :editable="false"
                                          :clearable="false"/>
                        </div>

                        <div class="form-group date-filter-item" v-if="selected_filter_type == 3">
                            <label>Month</label>
                            <ElDatePicker class="date-filter date-filter-2"
                                          v-model="month"
                                          name="month"
                                          format="MMMM yyyy"
                                          value-format="dd MMMM yyyy"
                                          type="month"
                                          :editable="false"
                                          :clearable="false"/>
                        </div>

                        <div class="form-group date-filter-item" v-if="selected_filter_type == 2">
                            <label>To</label>
                            <ElDatePicker class="date-filter date-filter-2"
                                          v-model="date_to"
                                          name="date_to"
                                          :format="date_format"
                                          :value-format="date_format"
                                          :editable="false"
                                          :clearable="false"/>
                        </div>

                        <div class="form-group" v-if="statusesFilter">
                            <label v-for="status in statuses_filter" style="width:250px;white-space:nowrap;">
                                <input type="checkbox" name="statuses[]" v-model="status.selected" :value="status.id"> {{status.status}}
                            </label>
                            <label style="white-space:nowrap;">
                                <input type="checkbox" v-model="only_60_min_sessions"> 
                                Only 60 min. sessions
                            </label>
                        </div>

                        <div class="form-group inline-block">
                            <button class="btn btn-primary" @click.prevent="getCompletedAppointments()">Show</button>
                        </div>
                    </form>
                </div>

                <div class="col-xs-3">
                    <div class="form-group text-right">
                        <label style="display:block;">&nbsp;</label>
                        <button class="btn btn-primary" @click.prevent="showConfirmDialog()"
                                :disabled="is_create_visit_disabled">
                            Create Visit
                        </button>
                    </div>
                </div>
            </div>

            <div v-for="dailyAppointments in appointments">
                <h2 class="text-center">{{dailyAppointments.date}}</h2>
                <div class="table-responsive">
                    <table class="table" id="completed-appointments-table" data-datatable="true">
                        <thead>
                        <tr>
                            <th class="text-center">
                                <input type="checkbox" v-model="dailyAppointments.selected" @change="selectAllAppointments(dailyAppointments)">
                            </th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Patient Name</th>
                            <th>Authorization Number</th>
                            <th>Therapist Name</th>
                            <th>Supervisor Name</th>
                            <th>Insurance</th>
                            <th>Insurance Plan</th>
                            <th>CPT in template</th>
                            <th>POS</th>
                            <th>Modifier A</th>
                            <th>Reason for Visit</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="appt in dailyAppointments.dataset"
                            :class="{'bg-warning':appt.error_message && !appt.is_creating_visit_inprogress && appt.is_warning,
                            'with-errors': (appt.error_message && !appt.is_creating_visit_inprogress && !appt.is_warning)}"
                        >
                            <td class="text-center" style="width:30px;">
                                <input type="checkbox" v-model="selected_appointments[appt.id]" :disabled="!appt.allow_create_visit">
                            </td>
                            <td style="width:90px;">
                                <span v-show="false">{{appt.time}}</span>
                                {{ getFormattedTime(appt.appt_date_time) }}
                            </td>
                            <td>
                                {{ getAppointmentStatusName(appt) }}
                                <el-tooltip v-if="appt.start_completing_date || appt.patient_requested_cancellation_at || appt.custom_notes" class="item" effect="dark" placement="bottom">
                                    <template #content>
                                        <p v-if="appt.start_completing_date" style="margin:0;">
                                            Status changed at: {{ getFormattedDateTime(appt.start_completing_date) }}
                                        </p>
                                        <p v-if="appt.patient_requested_cancellation_at" style="margin:0;">
                                            Patient Requested Cancellation At: {{ getFormattedDateTime(appt.patient_requested_cancellation_at) }}
                                        </p>
                                        <p v-if="appt.custom_notes" style="margin:0;">
                                            Comment: {{ appt.custom_notes }}
                                        </p>
                                    </template>
                                    <help />
                                </el-tooltip>
                            </td>
                            <td>
                                <a :href="`${'/chart/' + appt.patient_id}`" target="_blank">{{appt.patient_name}}</a>
                                (<a :href="`${office_ally_href+appt.external_patient_id}`" target="_blank">OA</a>)
                            </td>
                            <td class="text-center">
                                <insurance-authorization-number :patient="getInsuranceAuthorizationNumberData(appt)"/>
                            </td>
                            <td>{{ appt.provider_name }}</td>
                            <td>
                                <span v-if="appt.supervisor">{{ appt.supervisor.provider_name }}</span>
                                <span v-else>-</span>
                            </td>
                            <td>{{ appt.primary_insurance || '-' }}</td>
                            <td>{{ appt.insurance_plan || '-' }}</td>
                            <td>
                                <p v-for="template in appt.patient_templates" style="margin-bottom:0;">
                                    <span v-if="template.cpt" :class="{'hightlight-cpt': highlightCpt(template.cpt)}">{{ template.cpt }}</span>
                                    <span v-else>-</span>
                                </p>
                            </td>
                            <td>
                                <p v-for="template in appt.patient_templates" style="margin-bottom:0;">
                                    <span v-if="template.pos">{{ template.pos }}</span>
                                    <span v-else>-</span>
                                </p>
                            </td>
                            <td>
                                <p v-for="template in appt.patient_templates" style="margin-bottom:0;">
                                    <span v-if="template.modifier_a">{{ template.modifier_a }}</span>
                                    <span v-else>-</span>
                                </p>
                            </td>
                            <td>{{appt.reason_for_visit}}</td>
                            <td>
                                <span v-if="appt.is_creating_visit_inprogress">Processing...</span>
                                <span v-else-if="appt.error_message" v-html="appt.error_message"></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <CompletedAppointmentsInfoModal
            v-if="show_confirmation_dialog"
            :is-show-dialog="show_confirmation_dialog"
            :selected-appointment-ids="selected_appointment_ids"
            @closeDialog="closeDialog"></CompletedAppointmentsInfoModal>
    </div>
</template>

<script>
    import DatetimeFormated from '../../mixins/datetime-formated';
    import AppointmentStatus from '../../mixins/appointment-status';
    import CompletedAppointmentsInfoModal from './CompletedAppointmentsInfoModal';
    import { OFFICE_ALLY_BASE_HREF, EAP_CPT_CODE } from "../../settings";

    export default {
        mixins: [
            DatetimeFormated,
            AppointmentStatus
        ],

        components: {CompletedAppointmentsInfoModal},

        props: [
            'initAppointments',
            'csrfToken',
            'propMonth',
            'propFilterType',
            'propDateFrom',
            'propDateTo',
            'statusesFilter',
            'visitInprogressCount',
        ],

        watch: {
            'loading.appointments': function(val) {
                if(!val) {
                    this.initDatatable()
                }
            },
        },

        data() {
            return {
                office_ally_href: OFFICE_ALLY_BASE_HREF,
                show_confirmation_dialog: false,
                loading: {
                    appointments: false,
                },
                appointments: this.initAppointments,
                all_appointments: {},
                selected_appointments: {},
                visits_inprogress_timer: null,
                visits_inprogress: 0,

                selected_filter_type: 2,
                selected_provider: -1,
                date_from: null,
                date_to: null,
                date_format: 'MM/dd/yyyy',
                month: null,
                interval: null,

                statuses_filter: this.statusesFilter,
                visit_inprogress_count: this.visitInprogressCount,
                only_60_min_sessions: false,
            }
        },

        computed: {
            is_create_visit_disabled() {
                if(!this.selected_appointments) {
                    return true;
                }

                for (let i in this.selected_appointments) {
                    if(this.selected_appointments[i]) {
                        return false;
                    }
                }

                return true;
            },
            provider_list() {
                return this.$store.state.provider_list;
            },

            selected_appointment_ids()
            {
                let appointments = [];
                for (let i in this.selected_appointments) {
                    if(this.selected_appointments[i]) {
                        appointments.push(i)
                    }
                }

                return appointments;
            }
        },

        methods: {
            closeDialog() {
                this.show_confirmation_dialog = false;
            },

            initDatatable() {
                window.setTimeout(() => {
                    $("table#completed-appointments-table[data-datatable='true']").DataTable({
                        'paging': false,
                        'lengthChange': false,
                        'searching': false,
                        'ordering': true,
                        'info': false,
                        'autoWidth': false,
                        order: [[1, 'desc']],
                        columns: [
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                            {searchable: false, sortable: false},
                        ]
                    });
                }, 500);
            },

            selectAllAppointments(data) {
                for (let i in data.dataset) {
                    if(data.dataset[i].allow_create_visit) {
                        this.$set(this.selected_appointments, data.dataset[i].id, data.selected);
                    }
                }
            },


            showConfirmDialog() {
                this.show_confirmation_dialog = true;
            },

            getCompletedAppointments(with_loading = true) {
                if(with_loading) {
                    this.selected_appointments = {};
                    this.loading.appointments = true;
                }
                let self = this;
                let statuses = [];
                for(var i in self.statuses_filter) {
                    if(self.statuses_filter[i].selected) {
                        statuses.push(self.statuses_filter[i].id);
                    }
                }
                let payload = {
                    selected_filter_type: self.selected_filter_type,
                    date_from: self.date_from,
                    date_to: self.date_to,
                    month: self.month,
                    statuses: statuses,
                    selected_provider: self.selected_provider,
                    only_60_min_sessions: self.only_60_min_sessions
                };
                self.$store.dispatch('getCompletedAppointments', payload).then(response => {
                    if(response.status === 200) {
                        let data = response.data;
                        self.date_from = data.dateFrom;
                        self.date_to = data.dateTo;
                        self.month = data.month;
                        self.statuses_filter = data.statusesFilter;
                        self.appointments = data.appointments;
                        self.visit_inprogress_count = data.visitInprogressCount;
                    }
                    if(with_loading) {
                        this.loading.appointments = false;
                    }
                });
            },

            getDateTo() {
                let dateStep = 1;
                if(this.dateToStep !== null && this.dateToStep !== undefined) {
                    return this.date_from;
                }
                let tmp = this.date_from;
                let result = new Date(tmp.setMonth(tmp.getMonth() + dateStep));
                tmp.setMonth(tmp.getMonth() - dateStep);
                return result;
            },

            getInsuranceAuthorizationNumberData(appt) {
                if (!appt.patient || !appt.patient.insurance_plan) {
                    return null;
                }

                return {
                    insurance_requires_verification: appt.patient.insurance_plan.is_verification_required,
                    insurance_authorization_number: appt.patient.auth_number,
                    insurance_visits_auth: appt.patient.visits_auth,
                    insurance_visits_auth_left: appt.patient.visits_auth_left,
                    insurance_eff_start_date: appt.patient.eff_start_date,
                    insurance_eff_stop_date: appt.patient.eff_stop_date,
                    reauthorization_notification_visits_count: appt.patient.insurance_plan.reauthorization_notification_visits_count,
                    reauthorization_notification_days_count: appt.patient.insurance_plan.reauthorization_notification_days_count,
                };
            },

            highlightCpt(code) {
                return code == EAP_CPT_CODE;
            }
        },

        mounted() {
            this.$store.dispatch('getProviderList');

            if(this.propDateFrom) {
                this.date_from = this.propDateFrom;
            } else {
                this.date_from = new Date();
            }

            if(this.propFilterType) {
                this.selected_filter_type = this.propFilterType;
            }
            if(this.propMonth) {
                this.month = this.propMonth;
            } else {
                this.month = this.$moment().format('DD MMMM YYYY');
            }

            if(this.propDateTo) {
                this.date_to = this.propDateTo;
            } else {
                this.date_to = this.getDateTo();
            }

            if(this.visit_inprogress_count > 0) {
                let self = this;
                console.log('start interval');
                this.interval = window.setInterval(function() {
                    self.getCompletedAppointments(false);
                    if(self.visit_inprogress_count <= 0) {
                        console.log('clear interval');
                        window.clearInterval(self.interval);
                    }
                }, 15000)
            } else {

            }
            this.initDatatable();
        },
    }
</script>

<style scoped>
    #completed-appointments-table td {
        vertical-align: middle;
    }

    tr.with-errors {
        background-color: #f8d7dabd !important;
    }

    .date-filter-item {
        width: 170px;
        display: inline-block;
        margin-right: 10px;
    }

    .page-loader-wrapper {
        height: 80vh;
    }

    .page-loader-wrapper:before {
        display: inline-block;
        vertical-align: middle;
        content: " ";
        height: 100%;
    }

    .page-loader {
        max-width: 200px;
        max-height: 200px;
    }

    .hightlight-cpt {
        font-weight: bold;
        font-size: 16px;
        color: #d09b00;
    }
</style>