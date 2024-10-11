<template>
    <div>
        <div id="appointmentDetail" class="modal modal-vertical-center fade modal-appointment-detail" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" @click="closeAppointmentModal()"  aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Appointment Info</h4>
                </div>
                <div class="modal-body">
                    <div class="page-loader-wrapper kaiser-appointments text-center" v-if="loadingModal">
                        <pageloader add-classes="page-loader" />
                    </div>
                    <template v-else>
                        <table  class="table-patient">
                            <tbody>
                                <tr>
                                    <th class="text-right table-label">Patient Name:</th>
                                    <td class="info-patient info-patient--name">
                                        <a v-if="appointment.patient_id" :href="`/chart/${appointment.patient_id}`">{{ appointment.first_name }} {{ appointment.last_name }}</a>
                                        <p v-else>{{ appointment.first_name }} {{ appointment.last_name }}</p>
                                    </td>
                                    <th class="text-right table-label">Date:</th>
                                    <td>{{ getFormattedDateTime(appointment.start_date) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-right table-label">Sex:</th>
                                    <td class="info-patient">{{ appointment.gender }}</td>
                                    <th class="text-right table-label">Duration:</th>
                                    <td>{{ appointment.duration }} min.</td>
                                </tr>
                                <tr>
                                    <th class="text-right table-label">Date of Birth:</th>
                                    <td class="info-patient">{{ getFormattedDateSimple(appointment.date_of_birth) }}</td>
                                    <th class="text-right table-label">Therapist name:</th>
                                    <td>{{ appointment.provider.provider_name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-right table-label">Phone number:</th>
                                    <td class="info-patient">{{ getUsFormat(appointment.cell_phone ? appointment.cell_phone : (appointment.patient ? appointment.patient.cell_phone : '')) }}</td>
                                    <th class="text-right table-label">Site:</th>
                                    <td>{{ appointment.tridiuum_site_name }}</td>
                                </tr>
                                <tr>
                                    <th class="text-right table-label">MRN:</th>
                                    <td class="info-patient">{{ appointment.mrn }}</td>
                                    <th class="text-right table-label">Notes:</th>
                                    <td>{{ appointment.notes }}</td>
                                </tr>
                                <tr>
                                    <th class="text-right table-label">Email:</th>
                                    <td class="info-patient">
                                        <div class="form-group update-email" :class="{'has-error': errors.has('patient_email')}" v-if="showEmailInput">
                                            <input class="form-control" name="patient_email" v-validate.immediate="'email'" data-vv-as="email" data-vv-validate-on="" v-model="email">
                                            <button style="z-index:10;" class="fa fa-floppy-o fa-relationship-button" @click.prevent="updatePatientEmail"></button>
                                        </div>
                                        <span class="" v-else>
                                            {{ appointment.patient && appointment.patient.email ? appointment.patient.email : '' }}
                                            <i style="margin-top:2px;z-index:10;" class="fa fa-pencil fa-relationship-button" @click="openEmailForm"></i>
                                        </span>
                                    </td>
                                    <th class="text-right table-label">Comment:</th>
                                    <td>{{ appointment.comment }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p style="color:red;" v-if="!appointment.patient">The patient has not been created in OA yet, but would be created automatically soon.</p>
                        <hr>
                        <form @submit.prevent="call('call-form')" v-if="appointment.patient">
                            <div class="row">
                                <div class="col-xs-12">
                                    <h4>Call Patient</h4>
                                </div>
                                <div class="col-xs-5">
                                    <div class="form-group" :class="{'has-error': errors.has('From Number')}">
                                        <label>From:</label>
                                        <select name="From Number" :disabled="submitDisabled || disabledActions" class="form-control" v-model="phone_from" v-validate="'required'" id="caller_number">
                                            <option value="" disabled>Select number</option>
                                            <option 
                                                v-for="(number, index) in ringCentralNumbers"
                                                :key="index"
                                                :value="number.phoneNumber">
                                                    {{ getUsFormat(number.phoneNumber) }}
                                            </option>
                                            <option value="custom_number">Custom number</option>
                                        </select>
                                        <div v-if="errors.first('From Number')" class="text-danger">{{ errors.first('From Number') }}</div>
                                    </div>
                                </div>
                                <div class="col-xs-5">
                                    <div class="form-group">
                                        <label>To:</label>
                                        <input type="text" name="phone_to" id="patient_number_input" v-validate="'required'" disabled class="form-control" 
                                            v-bind:value="phoneToFormatted"
                                            v-on:input="phone_to = getFullUsFormat($event.target.value)"
                                        >
                                    </div>
                                </div>
                                <div class="col-xs-2">
                                    <div class="text-right">
                                        <div class="form-group p3">
                                            <button class="btn btn-success" type="submit" :disabled="submitDisabled || disabledActions">Call</button>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="row">
                                <div class="col-xs-4" v-if="phone_from=='custom_number'">
                                    <div class="form-group" :class="{'has-error': errors.has('Custom Number')}">
                                        <input type="text" class="form-control"  v-validate="'required'" name="Custom Number" placeholder="(000) 000-0000" 
                                            v-bind:value="phoneFromFormatted"
                                            v-on:input="custom_from = getFullUsFormat($event.target.value)" id="custom_number_input">
                                        <div v-if="errors.first('Custom Number')" class="text-danger">{{ errors.first('Custom Number') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <div class="checkbox prompt-checkbox">
                                            <label for="prompt_call">
                                                <input type="checkbox" v-model="play_prompt" name="prompt" id="prompt_call">
                                                Prompt me to press 1 before connecting the call
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <call-logs-table :logs="appointment.call_logs" />
                    </template>
                </div>
                <div class="modal-footer">
                    <div class="row row--flex align-center justify-between no-pseudo no-margin">
                        <div class="info-appointment">
                            <template v-if="selectedForms.length > 0">
                                Please, send forms or remove checkboxes to confirm or cancel the appointment.
                            </template>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" @click="confirmAppointment()" :disabled="disabledActions || selectedForms.length > 0 || loadingModal">Confirm</button>
                            <button type="button" class="btn btn-danger" @click="cancelAppointment()" :disabled="disabledActions || selectedForms.length > 0 || loadingModal">Cancel Appointment</button>
                        </div>
                    </div>                
                </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <call-result-dialog :isVisible="callResultDialogIsVisible" :handleClose="closeCallResultDialog" />

        <div id="cancelAppointment" class="modal modal-vertical-center fade" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- <button type="button" class="close" @click="closeAppointmentModal()" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
                        <h4 class="modal-title">Cancel Appointment</h4>
                    </div>
                        <form method="post" @submit.prevent="cancelReason()">
                            <div class="modal-body">
                                <template v-if="sendedData">
                                    These documents have been sent to <b>{{ sendedData.to }}</b>
                                    <ul class="">
                                        <template v-for="form in sendedData.forms">
                                            <li :key="form.title">
                                                {{ form.title }}
                                            </li>
                                        </template>
                                    </ul>
                                    <p class="">
                                        <b>Are you sure you want to cancel appointment?</b>
                                    </p>
                                </template>
                                <div class="form-group">
                                    <label for="comment">Please specify appointment cancellation reason*</label>
                                    <textarea name="comment" required v-model="cancel.comment" id="comment" rows="4" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" :disabled="submitApptDisabled" class="btn btn-primary">Submit</button>
                                <button class="btn btn-default" data-dismiss="modal" @click="openAppointmentModal">Close</button>
                            </div>
                        </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <div id="confirmAppointment" class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- <button type="button" class="close" @click="closeAppointmentModal()" aria-label="Close"><span aria-hidden="true">&times;</span></button> -->
                        <h4 class="modal-title">Confirm Appointment in Office Ally</h4>
                    </div>
                        <form method="post" @submit.prevent="confirmAppointmentSave()">
                        <div class="modal-body">
                            <template v-if="sendedData">
                                <p class="">These documents have been sent to <b>{{ sendedData.to }}</b></p>
                                <ul class="">
                                    <template v-for="form in sendedData.forms">
                                        <li :key="form.title">
                                            {{ form.title }}
                                        </li>
                                    </template>
                                </ul>
                            </template>
                            <div class="form-group">
                                <label for="comment">Please confirm the appointment has been created in Office Ally. Post any relevant information to the section below</label>
                                <textarea name="comment" required v-model="save.comment" id="comment" rows="4" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" :disabled="submitApptDisabled" class="btn btn-primary">Submit</button>
                            <button class="btn btn-default" data-dismiss="modal" @click="openAppointmentModal">Close</button>
                        </div>
                        </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

<!--        <send-documents-modal-->
<!--            :send-to="appointment.patient && appointment.patient.email ? appointment.patient.email : ''"-->
<!--            :phone="appointment.cell_phone"-->
<!--            :selected-forms="selectedForms"-->
<!--            :is-sending="isSending"-->
<!--            :method="sendMethod"-->
<!--            :error-messages="twilioErrors"-->
<!--            @close="openAppointmentModal"-->
<!--            @submit="sendDocuments"-->
<!--        />-->
    </div>
</template>

<script>
import DatetimeFormated from '../../mixins/datetime-formated';
import PhoneFormated from '../../mixins/phone-format';
import SendDocumentsModal from "../appointments/SendDocumentsModal";
import DocumentAlertModal from "../appointments/DocumentAlertModal";

export default {
    components: {
        SendDocumentsModal,
        DocumentAlertModal,
    },
    props: ['loadingModal'],
    data() {
        return {
            callDetail: {
                comment: ''
            },
            cancel: {
                comment: '',
                status: 2
            },
            save: {
                comment: '',
                status: 1
            },
            custom_from: "",
            phone_from: "",
            phone_to: "",
            submitDisabled: false,
            submitApptDisabled: false,
            play_prompt: true,
            loading: true,
            selectedForms: [],
            documentsSent: false,
            isSending: false,
            showEmailInput: false,
            email: null,
            sendMethod: 'email',
            twilioErrors: null,
            sendedData: null,
            callResultDialogIsVisible: false
        }
    },
    mounted() {
        this.getRingCentralNumbers();
        this.email = this.appointment.patient.email;
    },
    mixins: [
        DatetimeFormated,
        PhoneFormated
    ],
    methods: {
        setTwilioError(errorBag) {
            this.twilioErrors = errorBag;
        },
        openEmailForm() {
            this.showEmailInput = true;
        },
        updatePatientEmail() {
            if(this.errors.has('patient_email')) {
              return;
            }
            this.$store.dispatch('updatePatient', {patientId: this.appointment.patient.id, email: this.email})
            .then(() => {
              this.$store.dispatch('getKaiserAppointmentDetail', this.appointment.id)
              .then(() => {
                this.showEmailInput = false;
              })
            })
        },
        // sendDocuments(data) {
        //     this.isSending = true;
        //     this.setTwilioError(null);
        //     this.$refs.patientForm.sendDocuments(data)
        //     .finally(() => {
        //         this.isSending = false;
        //     });
        // },

        handleSendDocumentsSuccess(sendedData) {
            this.sendedData = sendedData;
            window.setTimeout(() => {
                this.documentsSent = true;
                $('#appointmentDetail').modal('show');
            }, 500);
        },

        getRingCentralNumbers() {
            this.$store.dispatch('getRingCentralNumbers');
        },

        closeAppointmentModal(data) {
            if(data) {
              this.sendMethod = data.method;
            } else {
              this.sendMethod = 'email';
            }

            this.custom_from = "";
            $('#appointmentDetail').modal('hide');
        },

        clearPatientFormData() {
            this.documentsSent = false;
            this.selectedForms = [];
            this.sendedData = null;
        },

        openAppointmentModal() {
            // this.$refs.patientForm.closeSendDocumentsModal();
            window.setTimeout(() => {
                $('#appointmentDetail').modal('show');
            }, 500);
        },

        openSendDocumentsModal() {
          window.setTimeout(() => {
            $("#sendDocuments").modal("show");
          }, 500);
        },

        call() {
            this.submitDisabled = true;
            let payload = {
                appointment_id: this.appointment.id,
                appointment_type: 'tridiuum_appointment',
                phone_from: this.phone_from,
                phone_to: this.phone_to,
                play_prompt: this.play_prompt,
            };
            if(this.phone_from == 'custom_number') {
                payload.phone_from = this.custom_from
            }
            this.$validator.validateAll().then((result) => {
                if(result) {
                    this.$store.dispatch('appointmentRingOutCall', payload).then((response) => {
                        this.submitDisabled = false;
                        this.showCallResultDialog();
                    });
                } else {
                    this.submitDisabled = false;
                }
            });
        },

        showCallResultDialog() {
            this.closeAppointmentModal();
            this.callResultDialogIsVisible = true;
        },

        closeCallResultDialog() {
            this.callResultDialogIsVisible = false;
            this.$store.dispatch('getKaiserAppointmentDetail', this.appointment.id);
            this.openAppointmentModal();
        },

        confirmAppointment() {
            this.closeAppointmentModal();
            window.setTimeout(() => {
                $('#confirmAppointment').modal('show');
            }, 500);
        },

        confirmAppointmentSave() {
            this.submitApptDisabled = true;
            this.save.start_date = this.appointment.start_date;

            let confirmRequests = [];

            confirmRequests.push(this.$store.dispatch('updateAppointment', this.save));
            // if (!this.documentsSent) {
            //     confirmRequests.push(this.$refs.patientForm.sendDocuments(false));
            // }
            Promise.all(confirmRequests)
            .then((response) => {
                this.submitApptDisabled = false;
                this.$emit('fullUpdate');
                $('#confirmAppointment').modal('hide');
            });
        },

        cancelAppointment() {
            this.closeAppointmentModal();
            window.setTimeout(() => {
                $('#cancelAppointment').modal('show');
            }, 500);
        },

        cancelReason() {
            this.submitApptDisabled = true;
            this.cancel.start_date = this.appointment.start_date;
            this.$store.dispatch('updateAppointment', this.cancel)
                .then(response => {
                    this.submitApptDisabled = false;
                    this.$emit('fullUpdate');
                    $('#cancelAppointment').modal('hide');
                });
        },
    },
    computed: {
        appointment() {
            let appointment = this.$store.state.kaiserAppointmentDetail;
            this.$validator.reset();
            this.phone_from = "";
            this.phone_to = appointment.cell_phone ? appointment.cell_phone : (appointment.patient ? appointment.patient.cell_phone : '');
            if(appointment.patient) {
                if(this.phone_to && this.phone_to.length > 0) {
                    this.submitDisabled = false;
                }
                else {
                    this.submitDisabled = true;
                }
            } else {
                this.phone_to = "";
            }
            this.save.comment = this.cancel.comment = appointment.comment;
            
            return appointment;
        },
        callLog() {
            return this.$store.state.callLog;
        },
        ringCentralNumbers() {
            let numbers = this.$store.state.ringCentralNumbers;
            return numbers;
        },
        disabledActions() {
            if(this.appointment.status == 1 || this.appointment.status == 2) {
                return true;
            } else {
                return false;
            }
        },
        phoneToFormatted() {
            return this.getUsFormat(this.phone_to);
        },
        
        phoneFromFormatted() {
            return this.getUsFormat(this.custom_from);
        }
    },
    watch: {
        phone_from() {
            if(this.phone_from == 'custom_number') {
                setTimeout(() => {
                    $('#custom_number_input').focus();
                }, 200);
            }
        },
        selectedForms() {
            this.documentsSent = false;
        },
        appointment() {
          this.email = this.appointment.patient.email;
        }
    }
}
</script>

<style lang="scss">
    .p3 {
        padding-top: 26px;
    }

    .modal textarea {
        resize: none;
    }
    .select2.select2-container {
        display: block;
    }
    .table-patient th {
        padding-right: 15px;
    }
    .table-patient td.info-patient {
        width: 28%;
    }
    .table-patient tr {
        padding-bottom: 5px;
    }
    #appointmentDetail .form-group {
        margin-bottom: 5px;
    }
    #appointmentDetail .checkbox {
        margin-top: 0;
    }
    .kaiser-appointments.page-loader-wrapper img {
        height: 30vh;
    }
    .table-label {
        min-width: 130px;
        vertical-align: top;
    }

    .info-patient {
        .update-email {
            display: flex;
            align-items: center;

            .form-control {
                height: 23px;
                font-weight: normal;
            }

            .fa {
                margin-left: 10px;
            }
        }
    }

    .info-appointment {
        font-size: 14px;
        color: #a94442;
    }
</style>

