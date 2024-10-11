<template>
    <div>
        <div class="page-loader-wrapper kaiser-appointments text-center" v-if="loading">
            <pageloader add-classes="page-loader" />
        </div>
        <div v-else class="panel">
            <div class="panel-body">

                <div class="form-group date-filter-item">
                    <label>
                        Therapist
                    </label>
                    <select name="provider_id" id="provider_id" v-model="filters.provider_id" class="form-control">
                        <option value="" selected>All</option>
                        <option 
                            v-for="provider in providerList" 
                            :key="provider.id" 
                            v-bind:value="provider.id">
                                {{ provider.provider_name }}
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
                                    v-model="filters.date_from"
                                    name="date_from"
                                    :format="date_format"
                                    :value-format="date_format"
                                    :editable="false"
                                    :clearable="false"/>
                    <div class="date-range-to" v-if="selected_filter_type == 2">
                        <label>To</label>
                        <ElDatePicker class="date-filter date-filter-2"
                                        v-model="filters.date_to"
                                        name="date_to"
                                        :format="date_format"
                                        :value-format="date_format"
                                        :editable="false"
                                        :clearable="false"/>
                    </div>
                </div>

                <div class="form-group date-filter-item" v-if="selected_filter_type == 3">
                    <label>Month</label>
                    <ElDatePicker class="date-filter date-filter-2"
                                    v-model="filters.month"
                                    name="month"
                                    format="MMMM yyyy"
                                    value-format="dd MMMM yyyy"
                                    type="month"
                                    :editable="false"
                                    :clearable="false"/>
                </div>

                <div class="form-group date-filter-item">

                    <label>
                        Status
                    </label>
                    <div class="radio">
                        <label for="status_appointments_new">
                            <input type="radio" v-model="filters.status" value="3" id="status_appointments_new">
                            New
                        </label>
                    </div>
                    <div class="radio">
                        <label for="status_appointments_created">
                            <input type="radio" v-model="filters.status" value="1" id="status_appointments_created">
                            Created
                        </label>
                    </div>
                    <div class="radio">
                        <label for="status_appointments_canceled">
                            <input type="radio" v-model="filters.status" value="2" id="status_appointments_canceled">
                            Canceled
                        </label>
                    </div>
                    <div class="radio">
                        <label for="status_appointments_all">
                            <input type="radio"  v-model="filters.status" value="" id="status_appointments_all">
                            All
                        </label>
                    </div>
                </div>
                <div class="form-group date-filter-item site-names">
                    <label>
                        Site
                    </label>
                    <div class="checkbox" :key="site.id" v-for="site in sites">
                        <label :for="site.tridiuum_site_id">
                            <input type="checkbox" v-model="filters.site" :value="site.id" :id="site.tridiuum_site_id">
                            {{ site.tridiuum_site_name }}
                        </label>
                    </div>
                </div>
                <div class="inline-block">
                  <label class="block">&nbsp;</label>
                  <el-button
                      :loading="isRestartingTridiuumParsers"
                      type="success"
                      @click.prevent="restartTridiuumAppointmentsParser"
                  >
                    Sync
                  </el-button>
                </div>
                
                    <div v-for="(dailyAppointments, index) in appointments" :key="index">
                        <h3 class="text-center">
                            {{ formatDate(index) }}
                        </h3>
                        <table class="table table-striped kaiser-appointments-table" :id="`kaiser-appointments-table-${index}`"  data-datatable="true">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Therapist</th>
                                    <th>Site</th>
                                    <th>Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Secretary</th>
                                    <th>Comment</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="appointment in dailyAppointments" 
                                    :key="appointment.id" 
                                    :class="{danger: appointment.status == 2, success: appointment.status == 1}"> 
                                    <td>
                                        <a :href="`/chart/${appointment.patient_id}`" v-if="appointment.patient_id">{{ appointment.first_name }} {{ appointment.last_name }}</a>
                                        <p v-else>{{ appointment.first_name }} {{ appointment.last_name }}</p>
                                    </td>
                                    <td>
                                        {{ appointment.provider_name }}
                                    </td>
                                    <td>
                                        {{ appointment.tridiuum_site_name }}
                                    </td>
                                    <td>
                                        {{ getFormattedDateTime(appointment.start_date) }}
                                    </td>
                                    <td>
                                        {{ appointment.duration }} min.
                                    </td>
                                    <td>
                                        <p v-if="appointment.patient_id">{{ appointment.status_label }}</p>
                                        <p style="color:red;" v-else>The patient has not been created in OA yet, but would be created automatically soon</p>
                                    </td>
                                    <td>
                                        <p v-if="appointment.secretary && appointment.secretary.meta">
                                            {{ appointment.secretary.meta.firstname }} {{ appointment.secretary.meta.lastname }}
                                        </p>
                                    </td>
                                    <td>
                                        {{ appointment.comment }}
                                    </td>
                                    <td>
                                        <button class="btn btn-success btn-sm"  @click="appointmentDetail(appointment)">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                
                <div v-if="appointments.length == 0">
                    <h2 class="text-center">No results</h2>
                </div>
            </div>
        </div>
        <kaiser-appointment-modal :loadingModal="loadingModal" />
    </div>
</template>

<script>
import {mapState} from 'vuex';
import DatetimeFormated from '../../mixins/datetime-formated';
import KaiserAppointmentModal from './KaiserAppointmentModal';

const CHECK_TRIDIUUM_APPOINTMENTS_DATA_TIMEOUT = 60000; // timeout 1 minute (milliseconds)

export default {
    mixins: [
        DatetimeFormated,
    ],
    components: {
        KaiserAppointmentModal
    },
    data() {
        return {
            loading: true,
            loadingModal: true,
            selected_filter_type: 1,
            date_format: 'MM/dd/yyyy',
            filters: {
                date_from: null,
                date_to: null,
                month: null,
                provider_id: "",
                site: [],
                status: "",
            },
        }
    },
    mounted() {
        window.Echo.private('tridiuum-appointments')
          .listen('.appointments.tridiuum.updated', () => {
            this.getAppointments(false);
          });
        this.$store.dispatch('getProviderList');
        this.filters.date_from = moment().format('MM/DD/YYYY');
        this.getAppointments();
        this.initDatatable();
        this.$store.dispatch('getKaiserSites');
        this.loadTridiuumAppointmentsData();
    },
    computed: {
        providerList() {
            return this.$store.state.provider_list;
        },
        appointments() {
            return this.$store.state.kaiserAppointments;
        },
        sites() {
            return this.$store.state.kaiserSites;
        },
        isRestartingTridiuumParsers() {
            return this.$store.state.tridiuumAppointmentsData.isRestartingTridiuumParsers;
        },
    },
    
    methods: {
        getAppointments(withLoader = true) {
            if(withLoader) {
               this.loading = true;
            }
            this.$store.dispatch('getKaizerAppointments', this.filters)
                .then(() => {
                    this.loading = false;
                })
            ;
        },

        formatDate(dateItem) {
            return moment(dateItem).format('MM/DD/YYYY');
        },

        formatTime(timeItem) {
            return moment(timeItem).format('LT');
        },

        initDatatable() {
            
            window.setTimeout(() => {
                $('table.kaiser-appointments-table').DataTable({
                    'retrieve': true,
                    'paging': false,
                    'lengthChange': false,
                    'searching': false,
                    'ordering': true,
                    order: [3, 'asc'],
                    'info': false,
                    'autoWidth': false,
                    columns: [
                        {searchable: false, sortable: true},
                        {searchable: false, sortable: true},
                        {searchable: false, sortable: true},
                        {searchable: false, sortable: true, width:'80px'},
                        {searchable: false, sortable: true},
                        {searchable: false, sortable: true},
                        {searchable: false, sortable: true},
                        {searchable: false, sortable: false, width:'280px'},
                        {searchable: false, sortable: false},
                    ]
                });
            }, 500);
        },

        appointmentDetail(appointment) {
            this.loadingModal = true;
            let appointmentDetails = [];
            appointmentDetails.push(this.$store.dispatch('getKaiserAppointmentDetail', appointment.id));
            appointmentDetails.push(this.$store.dispatch('getFormsByPatientForModal', appointment.patient_id));
            Promise.all(appointmentDetails)
            .then(() => {
                this.loadingModal = false;
            });
            $('#appointmentDetail').modal('show');
        },

        loadTridiuumAppointmentsData() {
          this.$store.dispatch('getTridiuumAppointmentsData')
              .then(() => {
                  if (this.isRestartingTridiuumParsers) {
                      this.checkTridiuumAppointmentsDataWithTimeout();
                  }
              });
        },

        checkTridiuumAppointmentsDataWithTimeout() {
            window.setTimeout(() => {
                this.loadTridiuumAppointmentsData();
            }, CHECK_TRIDIUUM_APPOINTMENTS_DATA_TIMEOUT);
        },

        restartTridiuumAppointmentsParser() {
            this.$store.dispatch('restartTridiuumParsers');
        },
    },
    watch: {
        selected_filter_type() {
            
            if(this.selected_filter_type == 1) {
                this.filters.date_from = moment().format('MM/DD/YYYY');
                this.filters.date_to = null;
                this.filters.month = null;
            } else if(this.selected_filter_type == 2) {
                this.filters.date_from = moment().format('MM/DD/YYYY');
                this.filters.date_to = moment().format('MM/DD/YYYY');
                this.filters.month = null;
            } else if(this.selected_filter_type == 3) {
                this.filters.date_from = null;
                this.filters.date_to = null;
                this.filters.month = moment().format('MMMM YYYY');
            }
        },
        'filters.site'() {
            this.getAppointments();
        },
        'filters.date_from'() {
            this.getAppointments();
        },
        'filters.date_to'() {
            this.getAppointments();
        },
        'filters.month'() {
            this.getAppointments();
        },
        'filters.status'() {
            this.getAppointments();
        },
        'filters.provider_id'() {
            this.getAppointments();
        },
        loading() {
            if(!this.loading) {
                this.initDatatable();
            }
        }
    }
}
</script>
<style scoped>
    .date-filter-item {
        width: 170px;
        display: inline-block;
        margin-right: 10px;
        vertical-align: top;
    }

    .date-filter-item.site-names {
        width: 250px;
    }

    .block {
        display: block;
    }

    .el-button {
        width: 92px;
        background-color: #2ab27b;
        border-color: #259d6d;
    }

    .kaiser-appointments__btn-sync:disabled {
        opacity: 0.65;
    }

    .kaiser-appointments.page-loader-wrapper img {
        height: 30vh;
    }

    .date-range-to {
        margin-top: 10px;
    }
</style>
