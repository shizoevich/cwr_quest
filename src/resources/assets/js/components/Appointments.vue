<template>
    <div class="appointments-table-container">
        <div v-for="(type, index) in appointments" :key="index">
            <div class="appointments-wrapper">
                <div class="appointments-wrapper-header">
                    <h5 class="table-title">{{type.title}} Appointments ({{type.appointments.length}}):</h5>
                    <el-button
                        v-if="type.title === 'Upcoming'"
                        type="primary"
                        @click.prevent="openDialog(false, createAppointmentData)"
                    >
                        Schedule Appointment
                    </el-button>
                </div>
                <div class="table-responsive" v-if="type.appointments.length">
                    <el-table
                            v-loading="isLoading"
                            border
                            :data="type.appointments"
                            style="width: 100%"
                            row-class-name="border-top-row"
                            class="appointments-table"
                    >
                        <el-table-column
                                prop="date"
                                label="Date"
                                width="100">
                            <template slot-scope="scope">
                                <div v-html="scope.row.date"></div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="formatted_time"
                                label="Time"
                                width="100">
                        </el-table-column>
                        <el-table-column
                                prop="created_at"
                                label="Created at"
                                width="100">
                            <template slot-scope="scope">
                                <div>
                                    {{ getFormattedDateTime(scope.row.created_at) }}
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="provider_name"
                                label="Provider/Staff">
                        </el-table-column>
                        <el-table-column
                                prop="reason_for_visit"
                                label="Reason For Visit">
                        </el-table-column>
                        <el-table-column label="Status">
                            <template slot-scope="scope">
                                <div>
                                    {{ getAppointmentStatusName(scope.row) }}
                                    <el-tooltip v-if="scope.row.start_completing_date || scope.row.patient_requested_cancellation_at || scope.row.custom_notes" class="item" effect="dark" placement="bottom">
                                        <template #content>
                                            <p v-if="scope.row.start_completing_date" style="margin:0;">
                                                Status changed at: {{ getFormattedDateTime(scope.row.start_completing_date) }}
                                            </p>
                                            <p v-if="scope.row.patient_requested_cancellation_at" style="margin:0;">
                                                Patient Requested Cancellation At: {{ getFormattedDateTime(scope.row.patient_requested_cancellation_at) }}
                                            </p>
                                            <p v-if="scope.row.custom_notes" style="margin:0;">
                                                Comment: {{ scope.row.custom_notes }}
                                            </p>
                                        </template>
                                        <help />
                                    </el-tooltip>
                                </div>
                                <div v-if="scope.row.rescheduled_appointment_date">
                                    ({{ scope.row.rescheduled_appointment_date }})
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column
                            v-if="show_payment_column"
                            prop="payment"
                            label="Payment"
                        >
                            <template slot-scope="scope">
                                <payment-value
                                    :transaction-value="scope.row.late_cancellation_transaction ? scope.row.cancellation_fee : scope.row.copay"
                                    :square-transaction-value="scope.row.square_transaction && (scope.row.square_transaction.amount_money / 100)"
                                />
                            </template>
                        </el-table-column>

                        <el-table-column
                            v-else
                            prop="payment"
                            label="Cancellation fee"
                        >
                            <template slot-scope="scope">
                                <div
                                    v-if="scope.row.cancellation_fee"
                                    class="text-center"
                                    :class="{
                                       'text-red': !scope.row.square_transaction,
                                       'text-green': scope.row.square_transaction,
                                    }"
                                >
                                    ${{ scope.row.cancellation_fee }}
                                </div>
                                <div v-else class="text-center">-</div>
                            </template>
                        </el-table-column>

                        <el-table-column
                                prop="address"
                                label="Actions"
                                width="150">
                            <div slot-scope="scope" class="d-flex justify-content-center">
                               <template v-if="scope.row.has_video_session_button">
                                    <span
                                        class="patient-list-btn patient-list-btn-primary"
                                        title="Start Video Session"
                                        @click.prevent="showTelehealthChannelModal"
                                    >
                                        <i class="fa fa-phone" style="font-size:14px;"></i>
                                    </span>
                                    <ChooseTelehealthSessionChannelModal
                                        :showChooseTelehealthSessionModal="isShowChooseTelehealthSessionModal"
                                        :appointment="scope.row"
                                        :patient="patient"
                                        :provider="provider"
                                        @telehealth-confirmed="telehealthConfirmed"
                                        @closeTelehealthChannelModal="closeTelehealthChannelModal"
                                    />
                               </template>
                                <span class="patient-list-btn patient-list-btn-success"
                                      v-if="scope.row.has_complete_appointment_button"
                                      @click.prevent="setCompleteStatus(scope.row)"
                                      :class="{disabled: (loading_appointments_tab || scope.row.start_completing_date !== null)}">
                                      <i class="fa fa-check"></i>
                                      </span>
                                <span class="patient-list-btn patient-list-btn-warning"
                                      v-if="scope.row.has_reschedule_appointment_button"
                                      @click.prevent="setRescheduleStatus(scope.row)"
                                      :class="{disabled: (loading_appointments_tab || scope.row.start_completing_date !== null)}">
                                      <i class="fa fa-refresh"></i>
                                      </span>
                                <span class="patient-list-btn patient-list-btn-danger"
                                      v-if="scope.row.has_cancel_appointment_button"
                                      @click.prevent="setCancelStatus(scope.row)"
                                      :class="{disabled: (loading_appointments_tab || scope.row.start_completing_date !== null)}">
                                          <i class="fa fa-times"></i>
                                      </span>
                                <span class="patient-list-btn patient-list-btn-success"
                                      v-if="scope.row.has_collect_payment_button"
                                      @click.prevent="startCollectPayment(scope.row)"
                                      :class="{disabled: loading_appointments_tab || loading_collect_payment_data}">
                                          <i class="fa fa-dollar"></i>
                                      </span>
                            </div>
                        </el-table-column>
                        <el-table-column
                                v-if="type.title === 'Upcoming'"
                                prop="address"
                                :label="is_admin || is_secretary ? 'Edit / Delete' : 'Edit'"
                                width="150">
                            <template slot-scope="scope">
                                <div v-if="scope.row.has_control_buttons" class="text-center">
                                    <el-button size="small" @click="openUpdateDialog(scope.row)" type="primary"
                                               icon="el-icon-edit"
                                               plain/>
                                    <el-button v-if="is_admin || is_secretary" size="small" @click="openRemoveDialog(scope.row)" type="danger"
                                               icon="el-icon-delete"
                                               plain/>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <div class="no-appointments" v-else>
                    No <span>{{type.title}}</span> appointment records for this patient.
                </div>
            </div>
        </div>
        <CreateAppointmentModal
                v-if="isShowDialog"
                @updateAppointments="updateAppointmentsList"
                :dialog-title="dialogTitle"
                :visibleAppointmentModal="isShowDialog"
                :patient-appointment="appointmentData"
                :isEditable="isEditableDialog"
                :isCreated="isCreated"
                @close="closeDialog"/>
        <CreateAppointmentModal
                v-if="isShowDialogTherapist"
                :is-therapist="true"
                @updateAppointments="updateAppointmentsList"
                :dialog-title="dialogTitle"
                :visibleAppointmentModal="isShowDialogTherapist"
                :patient-appointment="appointmentData"
                @startRemoveAppointment="startRemoveAppointment"
                @removeAppointment="removeAppointment"
                :isEditable="isEditableDialog"
                :isCreated="isCreated"
                @close="closeDialog"/>
    </div>
</template>

<script>
    import CreateAppointmentModal from "./appointments/CreateAppointmentModal";
    import PaymentValue from "./appointments/PaymentValue";
    import {Notification} from "element-ui";
    import DatetimeFormated from '../mixins/datetime-formated';
    import AppointmentStatus from "../mixins/appointment-status";
    
    export default {
        mixins: [DatetimeFormated, AppointmentStatus],

        data() {
            return {
                isShowDialog: false,
                isShowDialogTherapist: false,
                isEditableDialog: false,
                dialogTitle: 'Schedule appointment',
                appointmentData: {},
                createAppointmentData: {},
                isCreated: false,
                isLoading: false,
                isShowChooseTelehealthSessionModal: false,
            }
        },
        components: {CreateAppointmentModal, PaymentValue},
        computed: {
            appointments() {
                return this.$store.state.patient_appointments;
            },

            is_admin() {
                return this.$store.state.isUserAdmin;
            },
            
            is_secretary() {
                return this.$store.state.isUserSecretary;
            },

            is_read_only_mode() {
                return this.$store.state.is_read_only_mode;
            },

            patient() {
                return this.$store.state.currentPatient;
            },

            provider() {
                return this.$store.state.currentProvider;
            },

            loading_appointments_tab() {
                return this.$store.state.loading_appointments_tab;
            },

            loading_collect_payment_data() {
                return this.$store.state.collectPaymentDataLoading;
            },

            show_payment_column() {
                return this.is_admin || (this.provider && this.provider.is_collect_payment_available);
            }
        },
        watch: {
            patient() {
                this.initialCreateAppointmentData();
            }
        },
        methods: {
            telehealthConfirmed(status) {
              this.$emit('telehealth-confirmed', status);
            },
            closeTelehealthChannelModal() {
              this.isShowChooseTelehealthSessionModal = false;
            },
            showTelehealthChannelModal() {
              this.isShowChooseTelehealthSessionModal = true;
            },
            
            startCollectPayment(appointment) {
                if (this.loading_appointments_tab || this.loading_collect_payment_data) {
                    return;
                }

                this.$store.dispatch('setCollectPaymentAppointment', {
                    id: appointment.id,
                    late_cancellation_transaction: appointment.late_cancellation_transaction,
                    officeally_transaction: appointment.officeally_transaction,
                    cancellation_fee: appointment.cancellation_fee,
                    copay: appointment.copay,
                });
            },

            setCompleteStatus(appointment) {
                if (this.loading_appointments_tab) {
                    return false;
                }
                this.$store.dispatch('getCompleteAppointmentData', {
                    appointment_id: appointment.id,
                    patient_id: this.patient.id,
                    action: 'complete'
                }).then(response => {
                    if (response.status === 200) {
                        $('#confirm-telehealth').modal('show');
                    }
                });
            },

            setRescheduleStatus(appointment) {
                if (this.loading_appointments_tab) {
                    return false;
                }
                this.$store.dispatch('getCompleteAppointmentData', {
                    appointment_id: appointment.id,
                    patient_id: this.patient.id,
                    action: 'reschedule'
                }).then(response => {
                    if (response.status === 200) {
                        $('#reschedule-appointment').modal('show');
                    }
                });
            },

            setCancelStatus(appointment) {
                if (this.loading_appointments_tab) {
                    return false;
                }
                this.$store.dispatch('getCompleteAppointmentData', {
                    appointment_id: appointment.id,
                    patient_id: this.patient.id,
                    action: 'cancel'
                }).then(response => {
                    if (response.status === 200) {
                        $('#cancel-appointment').modal('show');
                    }
                });
            },

            openDialog(isEditable = false, appointmentData = {}) {
                if (this.is_admin) {
                    this.isShowDialog = true;
                } else {
                    this.isShowDialogTherapist = true;
                }
                this.isEditable = isEditable;
                this.appointmentData = appointmentData;
                if (isEditable) {
                    this.dialogTitle = 'Update appointment';
                    this.isCreated = false;
                } else {
                    this.dialogTitle = 'Schedule appointment';
                    this.isCreated = true;
                }
            },

            closeDialog() {
                this.is_admin ? this.isShowDialog = false : this.isShowDialogTherapist = false;
            },

            openUpdateDialog(data) {
                if (this.is_admin) {
                    this.isShowDialog = true;
                } else {
                    this.isShowDialogTherapist = true;
                }
                this.isEditable = true;
                data.patient_name = this.patient.first_name + ' ' + this.patient.last_name + ' ' + this.patient.middle_initial;
                data.patient_id = this.patient.id;
                data.patient_email = this.patient.email;
                data.patient_cell_phone = this.patient.cell_phone;
                this.appointmentData = data;
                this.dialogTitle = 'Update appointment';
                this.isCreated = false;
            },

            openRemoveDialog(data) {
                this.isLoading = true;
                this.$confirm('Are you sure you want to delete? Please notice that you can edit a created appointment', 'Warning', {
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$store.dispatch('removeEHRAppointments', data.id)
                        .then(() => {
                            Notification.success({
                                title: 'Success',
                                message: 'Appointment was deleted successfully',
                                type: 'success'
                            });
                            this.$emit('updateAppointments');
                            this.isLoading = false;
                        })
                        .catch(() => {
                            this.isLoading = false;
                        })
                })
                    .catch(() => {
                        this.isLoading = false;
                    })
            },

            updateAppointmentsList() {
                this.$emit('updateAppointments');
            },
            initialCreateAppointmentData() {
                if (!this.patient) {
                    return;
                }
                
                this.createAppointmentData = {
                    patient_id: this.patient.id,
                    patient_name: this.patient.first_name + ' ' + this.patient.last_name + ' ' + this.patient.middle_initial,
                    patient_email: this.patient.email,
                    patient_secondary_email: this.patient.secondary_email,
                    patient_cell_phone: this.patient.cell_phone,
                    patient_visit_frequency: this.patient.visit_frequency_id
                }
                if (this.patient.providers.length === 1) {
                    this.createAppointmentData.providers_id = this.patient.providers[0].id;
                    this.createAppointmentData.provider_name = this.patient.providers[0].provider_name;
                }
            },
            startRemoveAppointment() {
                this.isLoading = true;
            },
            removeAppointment() {
                this.isLoading = false;
            },

            getShowStatusTooltip(appointment) {
                return appointment.custom_notes || appointment.patient_requested_cancellation_at;
            }
        },
        mounted() {
            this.initialCreateAppointmentData();
        }
    }
</script>

<style lang="scss" scoped>
    .appointments-wrapper {
        &-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
    }
</style>