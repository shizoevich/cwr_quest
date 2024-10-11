<template>
    <div id="createAppointmentModal" class="appointment-modal">
        <el-dialog :close-on-click-modal="false" :visible.sync="showAppointmentModal" @edit="openEditableAppointmentModal">
            <el-col v-loading="loading"></el-col>
            <div class="dialog-header">
                <span class="dialog-title">
                    {{ dialogTitle }}
                </span>
                <button type="button" aria-label="Close" class="dialog-headerbtn" @click.prevent="closeAppointmentModal">
                    <i class="el-dialog__close el-icon el-icon-close"></i>
                </button>
            </div>
            
            <CancellationFeeRestrictions v-if="ruleForm.patient_id && patientLateCancellationFeeInfo" :patientLateCancellationFeeInfo="patientLateCancellationFeeInfo" />

            <el-form :model="ruleForm" :rules="rules" ref="ruleForm" class="custom-form">
                <div class="row">
                    <el-form-item v-if="!isTherapist" class="col-12 col-md-6" label="Therapist" prop="provider_id">
                        <el-select filterable :filter-method="filterProviderHandler" v-model="ruleForm.provider_id"
                            :disabled="allTherapistDisabled" v-el-select-lazy="loadMoreProviders"
                            @change="handleChangeTherapist" name="provider_id" placeholder="">
                            <el-option v-for="therapist in providersList" :disabled="disabledTherapist(therapist.id)"
                                :key="therapist.id" :value="therapist.id" :label="therapist.provider_name">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item class="col-12" :class="{ 'col-md-6': !isTherapist }" label="Patient" prop="patient_id">
                        <el-select filterable :disabled="isPatientDisabled || !isCreated" :filter-method="filterPatientHandler"
                            @change="changePatient" v-model="ruleForm.patient_id" name="provider_id"
                            v-el-select-lazy="loadMorePatients" placeholder="">
                            <el-option v-for="patient in patientsList" :key="`patient-${patient.id}`" :value="patient.id"
                                :label="patient.full_name">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <!-- <el-form-item
                             class="col-12 col-md-4"
                             label="Status">
                         <el-select
                                 v-if="!isTherapist"
                                 v-model="ruleForm.status"
                                 name="status"
                                 :disabled="isStatusDisabled"
                                 placeholder="">
                             <el-option
                                     v-for="status in statusList"
                                     :key="status.id"
                                     :value="status.id"
                                     :label="status.status">
                             </el-option>
                         </el-select>
                     </el-form-item>-->
                </div>
                <div class="row">
                    <el-form-item class="col-12 col-md-6" label="Date" prop="date">
                        <el-date-picker
                            v-model="ruleForm.date"
                            type="date"
                            format="MM/dd/yyyy"
                            value-format="MM/dd/yyyy"
                            :picker-options="datePickerOptions"
                            :clearable="false"
                            :disabled="!isCreated"
                            :editable="false"
                            @change="calculateRepeatDates"
                        >
                        </el-date-picker>
                    </el-form-item>
                    <el-form-item class="col-12 col-md-6" label="Time" prop="time">
                        <el-select v-model="ruleForm.time" name="time" placeholder="" @change="calculateRepeatDates">
                            <el-option v-for="(hour, index) in workedHours" :key="index + hour" :value="hour">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </div>
                <div class="row">
                    <el-form-item class="col-12 col-md-6" label="Visit Type" prop="visit_type">
                        <el-radio-group
                            v-model="ruleForm.visit_type"
                            :disabled="isVirtualTypeDisabled"
                            @change="changeVisitType"
                        >
                            <el-radio :label="inPersonal">
                                <i class="fa fa-user"></i>
                                In person
                            </el-radio>
                            <el-radio :label="virtual">
                                <i class="fa fa-video-camera"></i>
                                Virtual
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                </div>
                <div class="row" v-if="ruleForm.visit_type">
                    <el-form-item
                        id="reason_for_visit"
                        class="col-12 col-md-6"
                        label="Visit Reason"
                        prop="reason_for_visit"
                    >
                        <el-select
                            v-if="!filteredVisitReasonList || !filteredVisitReasonList.length"
                            value=""
                            name="reason_for_visit"
                            placeholder=""
                            :disabled="true"
                        ></el-select>
                        <el-select
                            v-else
                            v-model="ruleForm.reason_for_visit"
                            name="reason_for_visit"
                            placeholder=""
                        >
                            <el-option v-for="reason in filteredVisitReasonList" :key="reason.name" :value="reason.id" :label="reason.name">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </div>

                <!-- <div v-if="feePerVisit" class="alert alert-info form-group">
                    You will be paid: {{ feePerVisit }}$
                </div> -->

                <div class="row">
                    <div class="row-virtual-data col-12" v-if="ruleForm.visit_type === virtual" :key="isUpdateVirtualData">
                        <div class="row-virtual-data__checked">
                            <el-checkbox v-model="isInvite">Invite to join a Telehealth session</el-checkbox>
                        </div>
                        <template v-if="isInvite">
                            <div class="row-virtual-data__description">
                                Please provide an email address or phone number to invite
                                patient to join in a Telehealth session
                            </div>
                            <div class="row-virtual-data-wrapper">
                                <el-form-item
                                    v-if="currentProvider && currentProvider.upheal_user_id"
                                    style="margin-bottom: 10px"
                                    class="col-12 col-md-6 col-md-offset-right-6 form-item-virtual-data"
                                    label="Telehealth provider"
                                    prop="telehealth_provider"
                                    id="telehealth_provider"
                                >
                                    <el-select
                                        v-model="ruleForm.telehealth_provider"
                                        name="telehealth_provider"
                                        placeholder=""
                                        @change="changeTelehealthProvider"
                                    >
                                        <el-option
                                            v-for="telehealthProvider in telehealthProviderList"
                                            :key="telehealthProvider.id"
                                            :value="telehealthProvider.id"
                                            :label="telehealthProvider.name"
                                        >
                                        </el-option>
                                    </el-select>
                                </el-form-item>

                                <el-form-item style="margin-bottom: 10px" class="col-12 col-md-6 form-item-virtual-data"
                                    label="Send Invitation via Email" prop="email">
                                    <el-checkbox v-model="ruleForm.send_telehealth_link_via_email" :disabled="isUphealProviderSelected" @change="
                                        changeCheckVirtualData(
                                            ruleForm.send_telehealth_link_via_email,
                                            'email'
                                        )
                                    " />
                                    <el-input type="email" :disabled="!ruleForm.send_telehealth_link_via_email"
                                        v-model="ruleForm.email" name="email" id="email" placeholder="Email">
                                    </el-input>
                                </el-form-item>
                                <el-form-item style="margin-bottom: 10px" class="col-12 col-md-6 form-item-virtual-data"
                                    label="Send Invitation via Add Email" prop="secondary_email">
                                    <el-checkbox v-model="ruleForm.send_telehealth_link_via_secondary_email" @change="
                                        changeCheckVirtualData(
                                            ruleForm.send_telehealth_link_via_secondary_email,
                                            'secondary_email'
                                        )
                                    " />
                                    <el-input type="email" :disabled="
                                        !ruleForm.send_telehealth_link_via_secondary_email
                                    " v-model="ruleForm.secondary_email" name="secondary_email" id="secondary_email"
                                        placeholder=" Add Email">
                                    </el-input>
                                </el-form-item>
                                <el-form-item style="margin-bottom: 10px"
                                    class="col-12 col-md-6 form-item-virtual-data form-item-virtual-data--phone"
                                    label="Send Invitation via SMS" prop="phone">
                                    <el-checkbox v-model="ruleForm.send_telehealth_link_via_sms" @change="
                                        changeCheckVirtualData(
                                            ruleForm.send_telehealth_link_via_sms,
                                            'phone'
                                        )
                                    " />
                                    <el-input type="tel" :disabled="!ruleForm.send_telehealth_link_via_sms"
                                        v-model="ruleForm.phone" name="phone" v-mask="'(###)-###-####'" :masked="true"
                                        id="phone" placeholder="Phone">
                                    </el-input>
                                </el-form-item>
                                <el-form-item
                                    style="margin-bottom: 10px"
                                    class="col-12 col-md-6 form-item-virtual-data form-item-virtual-data--date"
                                    label="Schedule Invitation"
                                    prop="telehealth_notification_date"
                                >
                                    <el-date-picker
                                        ref="notificationPicker"
                                        v-model="ruleForm.telehealth_notification_date"
                                        popper-class="appointment-date-picker"
                                        format="MM/dd/yyyy hh:mm A"
                                        value-format="MM/dd/yyyy hh:mm A"
                                        :disabled="!ruleForm.time"
                                        :picker-options="notificationPickerOptions"
                                        type="datetime"
                                        placeholder="Select date and time"
                                        :editable="false"
                                    />
                                </el-form-item>
                                <div class="col-md-12" v-if="isShowJoinByPhone">
                                    <el-checkbox style="margin-top: 8px" v-model="ruleForm.allow_to_join_by_phone">
                                      Allow to Join by Phone
                                    </el-checkbox>
                                    <p class="help-block" style="margin-top: 0; font-weight: normal; font-size: 11px">
                                        Checking the "Allow to Join by Phone" box above will allow
                                        patients to join this Telehealth session by dialing a phone
                                        number provided and entering a PIN code included in this
                                        invitation.
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="row d-flex align-items-center" v-if="!isUpdate && repeatAvailable">
                    <el-form-item class="col-12 col-md-6" label="Repeat" prop="repeat">
                        <el-select
                            v-model="ruleForm.repeat"
                            id="repeat"
                            name="repeat"
                            placeholder=""
                            @change="changeRepeat"
                        >
                            <el-option v-for="room in repeatList" :key="room.id" :value="room.id" :label="room.label">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-tooltip v-if="appointmentsRepeatDates.length > 0" style="margin-top: 12px;" effect="dark" placement="bottom">
                        <template #content>
                            <div>
                                Next appointments dates:
                                <ul style="padding-left:15px;padding-top:5px;margin:0;">
                                    <li v-for="(date, index) in appointmentsRepeatDates" :key="index">
                                        {{ date }}
                                    </li>
                                </ul>
                            </div>
                        </template>
                        <help :icon-font-size="16"/>
                    </el-tooltip>
                </div>
                <div class="row">
                    <el-form-item class="col-12" label="Notes">
                        <el-input type="textarea" v-model="ruleForm.notes" :autosize="{ minRows: 2, maxRows: 5 }"
                            maxlength="255" show-word-limit>
                        </el-input>
                    </el-form-item>
                </div>
            </el-form>
            <div class="dialog-footer">
                <div class="w-100 d-flex flex-column gap-2">
                    <div v-if="errorMessage" class="text-red validation-error-msg error-message">{{ errorMessage }}</div>

                    <div class="d-flex justify-content-end">
                        <div class="dialog-footer-item dialog-footer-item--start">
                            <el-button v-if="isCompleted" type="success" @click.prevent="openCompleteDialog">
                                Complete
                            </el-button>
                            <el-button v-if="isRescheduled" type="warning" @click.prevent="openRescheduleDialog">
                                Reschedule
                            </el-button>
                            <el-button v-if="isCancel" type="danger" @click.prevent="openCancelDialog">
                                Cancel
                            </el-button>
                        </div>
                        <el-button v-if="editable && (isUserAdmin || isUserSecretary)" class="remove-appointments-button"
                            type="danger" plain @click.prevent="removeAppointments()">
                            <span> Delete </span>
                        </el-button>
                        <el-button type="primary" @click.prevent="createAppointment('ruleForm')" :disabled="buttonIsDisabled">
                            <span>
                                {{ isCreated ? "Schedule" : "Update" }}
                            </span>
                        </el-button>
                        <el-button type="secondary" @click.prevent="closeAppointmentModal">
                            Close
                        </el-button>
                    </div>
                </div>
            </div>
        </el-dialog>
        
        <email-unsubscribed-dialog
            :patient-id="ruleForm.patient_id"
            :email="restoreEmail"
            :is-admin="!!isUserAdmin"
            :show="showEmailUnsubscribedDialog"
            :close="closeEmailUnsubscribedDialog"
            @emailRemovedFromRejectList="onEmailRemovedFromRejectList"
        />
    </div>
</template>

<script>
import { Notification } from "element-ui";
import CancellationFeeRestrictions from "./CancellationFeeRestrictions";
import { COFFEE_BEANS_OFFICE_ROOM_ID, ENCINO_OFFICE_ID, IN_PERSON_VISIT_TYPE, VIRTUAL_VISIT_TYPE, WEEKLY_VISIT_FREQUENCY_ID, BIWEEKLY_VISIT_FREQUENCY_ID, INDIVIDUAL_60_MIN_INSURANCE_PROCEDURE_ID, INITIAL_EVALUATION_PROCEDURE_ID } from '../../settings';
import NotificationPicker from "../../mixins/notification-picker";

export default {
    name: "CreateAppointmentModal",

    components: {
        CancellationFeeRestrictions
    },

    mixins: [NotificationPicker],

    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        visibleAppointmentModal: {
            type: Boolean,
            required: true,
        },
        isEditable: {
            type: Boolean,
            required: true,
        },
        appointments: {
            type: Object,
            default: () => {
                return {};
            },
        },
        selectTime: {
            type: Object,
            default: () => {
                return {};
            },
        },
        fields: {
            type: Object,
            default: () => {
                return {};
            },
        },
        tableAppointments: {
            type: Object,
            default: function () {
                return {};
            },
        },
        scheduleAppointments: {
            type: Object,
            default: function () {
                return {};
            },
        },
        patientAppointment: {
            type: Object,
            default: function () {
                return {};
            },
        },
        createdPatient: {
            type: Object,
            default: function () {
                return {};
            },
        },
        dialogTitle: {
            type: String,
            default: "Schedule appointment",
        },
        isCreated: {
            type: Boolean,
            default: true,
        },
        googleMeet: {
            type: Object,
            default() {
                return {};
            },
        },
        isTherapist: {
            type: Boolean,
            default: false,
        },
    },
    data: () => ({
        datePickerOptions: {
            disabledDate(time) {
                return time.getTime() < Date.now() - 8.64e7;
            },
        },
        isDisable: false,
        isTableDisable: false,
        isVirtualTypeDisabled: false,
        allTherapistDisabled: false,
        officeList: [],
        repeatList: [],
        patientLateCancellationFeeInfo: null,
        visitFrequencyId: null,
        appointmentsRepeatDates: [],
        ruleForm: {
            date: "",
            time: "",
            provider_id: "",
            patient_id: "",
            visit_type: "",
            repeat: null,
            reason_for_visit: "",
            notes: "",
            office_id: ENCINO_OFFICE_ID,
            office_room: null,
            status: 8,

            telehealth_provider: "google_meet",
            send_telehealth_link_via_email: false,
            send_telehealth_link_via_secondary_email: false,
            send_telehealth_link_via_sms: false,
            email: "",
            secondary_email: "",
            phone: "",
            telehealth_notification_date: "",
            allow_to_join_by_phone: false,
        },
        rules: {
            office_id: [
                {
                    required: true,
                    message: "The office field is required",
                    trigger: "change",
                },
            ],
            reason_for_visit: [
                {
                    required: true,
                    message: "The visit reason field is required",
                    trigger: "blur",
                },
            ],
            office_room: [
                {
                    required: true,
                    message: "The room field is required",
                    trigger: "change",
                },
            ],
            date: [
                {
                    required: true,
                    message: "The date field is required",
                    trigger: "change",
                },
            ],
            time: [
                {
                    required: true,
                    message: "The time field is required",
                    trigger: "change",
                },
            ],
            provider_id: [
                {
                    required: true,
                    message: "The therapist field is required",
                    trigger: "change",
                },
            ],
            patient_id: [
                {
                    required: true,
                    message: "The patient field is required",
                    trigger: "change",
                },
            ],
            visit_type: [
                {
                    required: true,
                    message: "The visit type field is required",
                    trigger: "change",
                },
            ],
            notes: [
                {
                    required: true,
                    message: "The notes field is required",
                    trigger: "blur",
                },
            ],
            telehealth_notification_date: [
                {
                    required: true,
                    message: "The schedule invitation field is required",
                    trigger: "change",
                },
            ],
            email: [
                {
                    required: false,
                }
            ],
        },
        patientsData: {
            pageIndex: 1,
            pageSize: 20,
            lastPageIndex: 20,
            list: [],
        },
        providersData: {
            pageIndex: 1,
            pageSize: 20,
            lastPageIndex: 20,
            list: [],
        },
        tableDataAppointments: this.tableAppointments,
        providerList: [],
        inPersonal: IN_PERSON_VISIT_TYPE,
        virtual: VIRTUAL_VISIT_TYPE,
        isInvite: false,
        isWithoutInitial: false,
        isStatusDisabled: true,
        isUpdateVirtualData: false,
        isPatientDisabled: false,
        statusList: [
            {
                id: 1,
                name: "Active",
            },
            {
                id: 2,
                name: "Canceled by Patient",
            },
            {
                id: 3,
                name: "Canceled by Provider",
            },
            {
                id: 4,
                name: "Canceled by Office",
            },
            {
                id: 5,
                name: "Last Minute Canceled by Patient",
            },
            {
                id: 6,
                name: "Last Minute Reschedule",
            },
            {
                id: 7,
                name: "Rescheduled",
            },
            {
                id: 8,
                name: "Patient did not Come",
            },
        ],
        telehealthProviderList: [
            {
                id: "google_meet",
                name: "Google meet",
            },
            {
                id: "upheal",
                name: "Upheal",
            },
        ],
        isCompleted: false,
        isRescheduled: false,
        isCancel: false,
        appointmentId: null,
        initPatientId: null,
        buttonIsDisabled: false,
        loading: false,
        showEmailUnsubscribedDialog: false,
        restoreEmail: null,
        // feePerVisit: null,
        errorMessage: null,
        notificationPickerOptions: {},
        showRepeat: false,
    }),
    directives: {
        elSelectLazy: {
            bind(el, binding) {
                const SELECTWRAP_DOM = el.querySelector(
                    ".el-select-dropdown .el-select-dropdown__wrap"
                );
                SELECTWRAP_DOM.addEventListener("scroll", function () {
                    const condition =
                        this.scrollHeight - this.scrollTop <= this.clientHeight;
                    if (condition) {
                        binding.value();
                    }
                });
            },
        },
    },
    computed: {
        isUserAdmin() {
            return this.$store.state.isUserAdmin;
        },
        isUserSecretary() {
            return this.$store.state.isUserSecretary;
        },
        offices() {
            return this.$store.state.offices;
        },
        currentProvider() {
            return this.$store.state.currentProvider;
        },
        visitReasonList() {
            return this.$store.state.treatmentModalities;
        },
        filteredVisitReasonList() {
            const isTelehealth = this.ruleForm.visit_type === this.virtual ? 1 : 0;

            return this.visitReasonList.filter(treatmentModality => Number(treatmentModality.is_telehealth) === isTelehealth);
        },
        isNotAppointments() {
            return this.isEditable && !Object.keys(this.appointments).length;
        },
        showAppointmentModal: {
            get() {
                return this.visibleAppointmentModal;
            },
            set(value) {
                if (!value) {
                    this.$emit("close");
                }
            },
        },
        workedHours() {
            const items = [];
            new Array(24).fill("", 7, 22).forEach((acc, index) => {
                items.push(moment({ hour: index }).format("h:mm A"));
                items.push(moment({ hour: index, minute: 15 }).format("h:mm A"));
                items.push(moment({ hour: index, minute: 30 }).format("h:mm A"));
                items.push(moment({ hour: index, minute: 45 }).format("h:mm A"));
            });
            return items;
        },
        patientsList() {
            let num = this.patientsData.pageIndex * this.patientsData.pageSize;
            return this.patientsData.list.filter((ele, index) => {
                return index < num;
            });
        },
        providersList() {
            let num = this.providersData.pageIndex * this.providersData.pageSize;
            return this.providersData.list.filter((ele, index) => {
                return index < num;
            });
        },
        isUpdate() {
            if (this.isTherapist) {
                if (Object.keys(this.patientAppointment).length) {
                    return !this.isCreated;
                } else {
                    return this.editable && Object.keys(this.fields).length;
                }
            }
            return true;
        },
        isPatient() {
            return Object.keys(this.patientAppointment).length;
        },
        isTimeFieldDisabled() {
            if (!this.isTherapist) {
                return (
                    !this.ruleForm.provider_id || this.isDisable || this.isTableDisable
                );
            } else if (this.isPatient) {
                return false;
            } else if (this.isCreated) {
                return true;
            }
            return false;
        },
        isShowJoinByPhone() {
            return !this.isUphealProviderSelected;
        },
        isUphealProviderSelected() {
            return this.ruleForm.telehealth_provider === 'upheal';
        },
        repeatAvailable() {
            return this.visitFrequencyId && [WEEKLY_VISIT_FREQUENCY_ID, BIWEEKLY_VISIT_FREQUENCY_ID].includes(this.visitFrequencyId) && this.showRepeat;
        }
    },
    methods: {
        createAppointment(formName) {
            this.errorMessage = null;

            this.$refs[formName].validate((valid) => {
                if (!valid) {
                    console.log("error submit!!");
                    return false;
                }

                this.loading = true;
                let formData = JSON.parse(JSON.stringify(this.ruleForm)),
                    dispatchName = "",
                    notificationMessage = "";
                formData.time = moment(this.ruleForm.time, "h:mm A").format("HH:mm");
                formData.date = moment(this.ruleForm.date, "MM/DD/YYYY").format("YYYY-MM-DD");
                formData.allow_to_join_by_phone = this.ruleForm.allow_to_join_by_phone;

                if (this.isCreated) {
                    dispatchName = "createAppointment";
                    notificationMessage = "Appointment was created successfully";
                } else {
                    dispatchName = "updateAppointmentModal";
                    notificationMessage = "Appointment was updated successfully";
                    if (Object.keys(this.tableAppointments).length) {
                        formData.id = this.tableAppointments.id;
                    }
                    if (Object.keys(this.scheduleAppointments).length) {
                        formData.id = this.scheduleAppointments.id;
                    }
                    if (Object.keys(this.patientAppointment).length) {
                        formData.id = this.patientAppointment.id;
                    }
                    if (Object.keys(this.fields).length) {
                        formData.id = this.fields.item_source.id;
                    }
                }
                this.$store
                    .dispatch(dispatchName, formData)
                    .then((response) => {
                        this.$emit("startUpdateAppointment");
                        if (
                            response.status === 422 ||
                            response.status === 409 ||
                            response.status === 500
                        ) {
                            this.loading = false;
                            this.$emit("canceledUpdateAppointment");
                            if (response.status === 422) {
                                const errors = response.data.errors
                                    ? Object.values(response.data.errors).reduce((prev, curr) => prev.concat(curr))
                                    : [];
                                if (errors.length) {
                                    this.errorMessage = errors[0];
                                }
                            } else if (response.status === 409) {
                                if (response.data.error.exception_type === 'EmailInRejectListException') {
                                    this.restoreEmail = response.data.error.email;
                                    this.showEmailUnsubscribedDialog = true;
                                } else {
                                    this.errorMessage = response.data.error.message;
                                }
                            } else {
                                this.errorMessage = "Oops, something went wrong!";
                            }
                        } else {
                            if (this.$route.name === 'patient-chart' && this.$route.params.id === String(this.ruleForm.patient_id)) {
                                this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {
                                    id: this.ruleForm.patient_id,
                                });
                            }

                            this.$store.dispatch("getProviderMessages");
                            this.$store.dispatch('getProviderTodayPatients');
                            this.$emit("createAppointment");
                            this.$emit("updateAppointments");
                            setTimeout(() => {
                                this.loading = false;
                                this.showAppointmentModal = false;
                            }, 0);
                            Notification.success({
                                title: "Success",
                                message: notificationMessage,
                                type: "success",
                            });
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            });
        },
        removeAppointments() {
            this.loading = true;
            this.$confirm(
                "Are you sure you want to delete? Please notice that you can edit a created appointment",
                "Warning",
                {
                    confirmButtonText: "OK",
                    cancelButtonText: "Cancel",
                    type: "warning",
                }
            )
                .then(() => {
                    let appointmentID = this.isPatient
                        ? this.patientAppointment.id
                        : this.fields.item_source.id;
                    this.$store
                        .dispatch("removeEHRAppointments", appointmentID)
                        .then(() => {
                            this.$emit("startRemoveAppointment");
                            setTimeout(() => {
                                Notification.success({
                                    title: "Success",
                                    message: "Appointment was deleted successfully",
                                    type: "success",
                                });
                            }, 200);
                            this.showAppointmentModal = false;
                            this.loading = false;
                            this.$emit("removeAppointment");
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                })
                .catch(() => {
                    this.loading = false;
                });
        },
        closeAppointmentModal() {
            this.showAppointmentModal = false;
        },
        setNowDate() {
            this.ruleForm.date = moment().format("MM/DD/YYYY");
        },
        initRepeatList() {
            this.repeatList = [];
            for (let i = 2; i <= 20; i++) {
                this.repeatList.push({ id: i, label: `${i} Times` });
            }
            this.repeatList.unshift({ id: 1, label: "1 Time" });
            this.repeatList.unshift({ id: null, label: "0" });
        },
        changeRepeat() {
            if (Boolean(this.ruleForm.repeat)) {
                this.rules.repeat = [
                    {
                        required: true,
                        message: "The repeat field is required",
                        trigger: "change",
                    },
                ];
            }
            
            if (this.ruleForm.repeat === null) {
                this.rules.repeat = [{ required: false }];
            }

            this.calculateRepeatDates();
        },
        calculateRepeatDates() {
            this.appointmentsRepeatDates = [];

            if (
                !this.ruleForm.repeat 
                || !this.ruleForm.date 
                || ![WEEKLY_VISIT_FREQUENCY_ID, BIWEEKLY_VISIT_FREQUENCY_ID].includes(this.visitFrequencyId)
            ) {
                return;
            }

            let date = moment(this.ruleForm.date, "MM/DD/yyyy");
            for (let i = 0; i < this.ruleForm.repeat; i++) {
                const daysBetweenVisits = this.getDaysBetweenVisits();
                date = date.clone().add(daysBetweenVisits, 'days');

                let dateString = date.format('MM/DD/yyyy');
                if (this.ruleForm.time) {
                    dateString += " " + this.ruleForm.time;
                }

                this.appointmentsRepeatDates.push(dateString);
            }
        },
        getDaysBetweenVisits() {
            switch (this.visitFrequencyId) {
                case WEEKLY_VISIT_FREQUENCY_ID:
                    return 7;
                case BIWEEKLY_VISIT_FREQUENCY_ID:
                    return 14;
                default:
                    return 0;
            }
        },
        openEditableAppointmentModal() {
            this.editAppointmentModal = true;
        },
        disabledTherapist(therapistId) {
            if (this.isDisable) {
                let overlap = true;
                
                for (let i = 0; i < this.appointments.info.length; i++) {
                    if (Number(this.appointments.info[i].provider_id) === Number(therapistId)) {
                        overlap = false;
                        return false;
                    }
                }
                    
                return overlap;
            }
        },
        handleChangeTherapist(therapistId) {
            if (this.isEditable && this.isDisable) {
                for (let i = 0; i < this.appointments.info.length; i++) {
                    if (
                        Number(this.appointments.info[i].provider_id) ===
                        Number(therapistId)
                    ) {
                        this.ruleForm.office_id = this.appointments.info[i].office_id;
                    }
                }  
            }
            if (this.isEditable && this.isTableDisable) {
                this.isTableDisable = false;
            }
        },
        initProviderList() {
            this.$store
                .dispatch("getProviderListForAppointments", {
                    page: this.providersData.pageIndex,
                    limit: this.providersData.pageSize,
                })
                .then(({ data }) => {
                    this.providersData.list = data.providers.data;
                    this.providersData.lastPageIndex = data.providers.last_page;
                });
        },
        updateProviderList() {
            this.$store
                .dispatch("getProviderListForAppointments", {
                    page: this.providersData.pageIndex,
                    limit: this.providersData.pageSize,
                })
                .then(({ data }) => {
                    this.providersData.list = this.providersData.list.concat(
                        data.providers.data
                    );
                });
        },
        loadMoreProviders() {
            this.providersData.pageIndex++;
            if (this.providersData.pageIndex <= this.providersData.lastPageIndex) {
                this.updateProviderList();
            }
        },
        filterProviderHandler(query) {
            if (query !== "") {
                this.$store
                    .dispatch("getProviderListForAppointments", {
                        limit: this.providersData.pageSize,
                        search_query: query,
                    })
                    .then(({ data }) => {
                        this.providersData.list = data.providers.data;
                    });
            } else {
                this.initPatientList();
            }
        },
        initPatientList() {
            this.$store
                .dispatch("getPatientForAppointments", {
                    page: this.patientsData.pageIndex,
                    limit: this.patientsData.pageSize,
                })
                .then(({ data }) => {
                    this.patientsData.list = data.patients.data;
                    this.patientsData.lastPageIndex = data.patients.last_page;
                });
        },
        updatePatientList() {
            this.$store
                .dispatch("getPatientForAppointments", {
                    page: this.patientsData.pageIndex,
                    limit: this.patientsData.pageSize,
                })
                .then(({ data }) => {
                    this.patientsData.list = this.patientsData.list.concat(
                        data.patients.data
                    );
                });
        },
        loadMorePatients() {
            this.patientsData.pageIndex++;
            if (this.patientsData.pageIndex <= this.patientsData.lastPageIndex) {
                this.updatePatientList();
            }
        },
        filterPatientHandler(query) {
            if (query !== "") {
                this.$store
                    .dispatch("getPatientForAppointments", {
                        limit: this.patientsData.pageSize,
                        search_query: query,
                    })
                    .then(({ data }) => {
                        this.patientsData.list = data.patients.data;
                    });
            } else {
                this.initPatientList();
            }
        },
        initOfficeList() {
            this.$store.dispatch("getOfficeListForAppointments").then(({ data }) => {
                this.officeList = data.offices;
            });
        },
        initStatusList() {
            this.$store.dispatch("getStatusListForAppointments").then(({ data }) => {
                this.statusList = data.statuses;
            });
        },
        changeVisitType(value) {
            let officeRoomValue = null

            if (value === IN_PERSON_VISIT_TYPE) {
                officeRoomValue = COFFEE_BEANS_OFFICE_ROOM_ID;
            }

            this.$set(this.ruleForm, "office_room", officeRoomValue);
        },
        changeTelehealthProvider() {
            this.$set(this.ruleForm, "allow_to_join_by_phone", false);

            if (this.isUphealProviderSelected) {
                this.$set(this.ruleForm, "send_telehealth_link_via_email", true);
                this.changeCheckVirtualData(true, 'email');
            }
        },
        changePatient() {
            this.buttonIsDisabled = true

            let currentPatient = this.patientsList.find(
                (item) => item.id === this.ruleForm.patient_id
            );

            if (currentPatient) {
                this.ruleForm.email = currentPatient.email;
                this.ruleForm.secondary_email = currentPatient.secondary_email;
                this.ruleForm.phone = currentPatient.cell_phone;
                this.visitFrequencyId = currentPatient.visit_frequency;

                this.$store.dispatch('checkPatientLateCancellationPayment', this.ruleForm.patient_id)
                .then(res => {this.patientLateCancellationFeeInfo = res.data})
                .finally(() => {this.buttonIsDisabled = false})
            } else {
                this.buttonIsDisabled = false
            }
            this.isUpdateVirtualData = !this.isUpdateVirtualData;
        },
        changeCheckVirtualData(check, field) {
            if (check) {
                this.rules[field] = [
                    {
                        required: true,
                        message: `The ${field} field is required`,
                        trigger: "blur",
                    },
                ];
                if (field === "email") {
                    this.rules[field].push({
                        type: "email",
                        message: "Invalid email address",
                        trigger: "blur",
                    });
                }
            } else {
                this.$refs.ruleForm.clearValidate(field);
                this.rules[field] = [{ required: false }];
            }
        },
        initAppointmentData(tableAppointments) {
            this.loading = true;
            if (!this.isWithoutInitial) {
                this.$store.dispatch("getAppointmentDialogData", tableAppointments.id)
                    .then(({ data }) => {
                        this.isStatusDisabled = false;
                        this.isCompleted = data.appointment.can_complete;
                        this.isRescheduled = data.appointment.can_reschedule
                        this.isCancel = data.appointment.can_cancel;
                        this.appointmentId = data.appointment.id;
                        this.initPatientId = data.appointment.patients_id;
                        this.providersData.list = [
                            {
                                id: data.appointment.providers_id,
                                provider_name: data.appointment.provider.provider_name,
                            },
                        ];
                        this.patientsData.list = [
                            {
                                id: data.appointment.patient.id,
                                full_name:
                                    data.appointment.patient.first_name +
                                    " " +
                                    data.appointment.patient.last_name +
                                    " " +
                                    data.appointment.patient.middle_initial,
                                email: data.appointment.patient.email,
                                secondary_email: data.appointment.patient.secondary_email,
                                cell_phone: data.appointment.patient.cell_phone,
                            },
                        ];
                        this.ruleForm = {
                            provider_id: data.appointment.providers_id || "",
                            patient_id: data.appointment.patient.id,
                            date: data.appointment.date_of_service.date,
                            time: data.appointment.date_of_service.time,
                            reason_for_visit: data.appointment.treatment_modality_id,
                            visit_type: data.appointment.office_room_id
                                ? this.inPersonal
                                : this.virtual,
                            office_id: data.appointment.offices_id || ENCINO_OFFICE_ID,
                            office_room: data.appointment.office_room_id,
                            notes: data.appointment.notes || "",
                            status: data.appointment.status.id || 8,
                            email: data.appointment.patient.email,
                            secondary_email: data.appointment.patient.secondary_email,
                            phone: data.appointment.patient.phone,
                            telehealth_provider: "google_meet",
                        };
                        if (this.patientAppointment && this.patientAppointment.patient_id) {
                            this.ruleForm.patient_id = this.patientAppointment.patient_id;
                        }
                        if (
                            data.appointment.status.status === "Completed" ||
                            data.appointment.status.status === "Visit Created"
                        ) {
                            this.isStatusDisabled = true;
                        }
                        this.changePatient();
                        this.loading = false;
                    });
            } else {
                this.isTableDisable = true;
                this.ruleForm = {
                    provider_id: tableAppointments.providers_id || "",
                    date: tableAppointments.date,
                    time: tableAppointments.time,
                    reason_for_visit: tableAppointments.treatment_modality_id,
                    visit_type: tableAppointments.office_room_name
                        ? this.inPersonal
                        : this.virtual,
                    office_id: tableAppointments.offices_id || ENCINO_OFFICE_ID,
                    office_room: tableAppointments.office_room_id,
                    status: 8,
                    email: "",
                    secondary_email: "",
                    phone: "",
                    telehealth_provider: "google_meet",
                };
                this.changePatient();
                this.loading = false;
            }
        },
        setFields() {
            if (!this.appointments || !this.appointments.info || !this.appointments.info.length) {
                return;
            }

            this.isDisable = true;

            let info = this.appointments.info.find((item) => item.id === this.appointments.selectedAvailabilityId);
            if (!info) {
                info = this.appointments.info[0];
            }
            
            this.allTherapistDisabled = Object.keys(this.appointments.info).length < 2;
           
            this.providersData.list = [
                { id: info.provider.id, provider_name: info.provider.provider_name },
            ];
            this.ruleForm = {
                provider_id: info.provider_id,
                date: moment(info.date.date).format("MM/DD/yyyy"),
                time: moment(this.appointments.start._a).format("h:mm A"),
                visit_type: info.virtual ? this.virtual : this.inPersonal,
                office_id: info.office_id,
                office_room: !info.virtual && info.office_room_id ? info.office_room_id : null,
                status: 8,
                email: "",
                secondary_email: "",
                phone: "",
                telehealth_provider: "google_meet",
                send_telehealth_link_via_email: false,
                send_telehealth_link_via_secondary_email: false,
                send_telehealth_link_via_sms: false,
                allow_to_join_by_phone: false,
            };
        },
        setFieldsForTable() {
            this.providersData.list = [
                {
                    id: this.tableAppointments.providers_id,
                    provider_name: this.tableAppointments.provider_name,
                },
            ];
            if (this.tableAppointments.patient_name === "") {
                this.isWithoutInitial = true;
            }
            this.initAppointmentData(this.tableAppointments);
        },
        setFieldsForSchedule() {
            this.providersData.list = [
                {
                    id: this.scheduleAppointments.providers_id,
                    provider_name: this.scheduleAppointments.provider_name,
                },
            ];
            this.ruleForm = {
                provider_id: this.scheduleAppointments.providers_id || null,
                date: this.scheduleAppointments.date,
                visit_type:
                    this.scheduleAppointments.visit_type.indexOf(IN_PERSON_VISIT_TYPE) !== -1 &&
                        this.scheduleAppointments.visit_type.length === 1
                        ? this.inPersonal
                        : this.virtual,
                office_id: ENCINO_OFFICE_ID,
                office_room: null,
                status: 8,
                email: "",
                secondary_email: "",
                phone: "",
                telehealth_provider: "google_meet",
                send_telehealth_link_via_email: false,
                send_telehealth_link_via_secondary_email: false,
                send_telehealth_link_via_sms: false,
                allow_to_join_by_phone: false,
            };
        },
        setFieldsForPatient() {
            this.isPatientDisabled = true;
            if (this.patientAppointment.providers_id) {
                this.providersData.list = [
                    {
                        id: this.patientAppointment.providers_id,
                        provider_name: this.patientAppointment.provider_name,
                    },
                ];
            }
            if (this.patientAppointment.patient_id) {
                this.patientsData.list = [
                    {
                        id: this.patientAppointment.patient_id,
                        full_name: this.patientAppointment.patient_name,
                        email: this.patientAppointment.patient_email,
                        secondary_email: this.patientAppointment.patient_secondary_email,
                        cell_phone: this.patientAppointment.patient_cell_phone,
                        visit_frequency: this.patientAppointment.patient_visit_frequency
                    },
                ];
            }
            if (!this.patientAppointment.id) {
                this.ruleForm = {
                    provider_id: this.patientAppointment.providers_id || null,
                    patient_id: this.patientAppointment.patient_id || null,
                    date: this.patientAppointment.date || moment().format("MM/DD/YYYY"),
                    visit_type: this.patientAppointment.offices_id
                        ? this.inPersonal
                        : this.virtual,
                    office_id: ENCINO_OFFICE_ID,
                    office_room: null,
                    status: "Active",
                    email: "",
                    secondary_email: "",
                    phone: "",
                    telehealth_provider: "google_meet",
                    send_telehealth_link_via_email: false,
                    send_telehealth_link_via_secondary_email: false,
                    send_telehealth_link_via_sms: false,
                    allow_to_join_by_phone: false,
                    reason_for_visit: "",
                };
            } else {
                this.initAppointmentData(this.patientAppointment);
            }
        },
        setFieldTime() {
            this.ruleForm.time = moment(this.selectTime).format("h:mm A");
            this.ruleForm.date = moment(this.selectTime).format("MM/DD/yyyy");
        },
        setField() {
            this.patientsData.list = [
                {
                    id: this.fields.item_source.patient.id,
                    full_name:
                        this.fields.item_source.patient.first_name +
                        " " +
                        this.fields.item_source.patient.last_name +
                        " " +
                        this.fields.item_source.patient.middle_initial,
                    email: this.fields.item_source.patient.email,
                    secondary_email: this.fields.item_source.patient.secondary_email,
                    cell_phone: this.fields.item_source.patient.cell_phone,
                },
            ];
            this.initAppointmentData(this.fields.item_source);
        },
        setFieldPatient() {
            this.isPatientDisabled = true;
            if (this.patientAppointment.patient_id && !this.isCreated) {
                this.patientsData.list = [
                    {
                        id: this.patientAppointment.patient_id,
                        full_name: this.patientAppointment.patient_name,
                        email: this.patientAppointment.patient_email,
                        secondary_email: this.patientAppointment.patient_secondary_email,
                        cell_phone: this.patientAppointment.patient_cell_phone,
                        visit_frequency: this.patientAppointment.patient_visit_frequency
                    },
                ];
                this.visitFrequencyId = this.patientAppointment.patient_visit_frequency;
                this.initAppointmentData(this.patientAppointment);
            } else {
                this.ruleForm = {
                    date: this.patientAppointment.date || moment().format("MM/DD/YYYY"),
                    time: this.patientAppointment.time
                        ? moment(this.patientAppointment.time * 1000).format("h:mm A")
                        : null,
                    patient_id: this.patientAppointment.patient_id || null,
                    office_id: ENCINO_OFFICE_ID,
                    visit_type: this.patientAppointment.office_room_id
                        ? this.inPersonal
                        : this.virtual,
                    office_room: this.patientAppointment.office_room_id || null,
                    reason_for_visit: this.patientAppointment.treatment_modality_id,
                    repeat: null,
                    email: "",
                    secondary_email: "",
                    phone: "",
                    telehealth_provider: "google_meet",
                    send_telehealth_link_via_email: false,
                    send_telehealth_link_via_secondary_email: false,
                    send_telehealth_link_via_sms: false,
                    telehealth_notification_date: "",
                    allow_to_join_by_phone: false,
                };
                if (this.patientAppointment.patient_id) {
                    this.patientsData.list = [
                        {
                            id: this.patientAppointment.patient_id,
                            full_name: this.patientAppointment.patient_name,
                            email: this.patientAppointment.patient_email,
                            secondary_email: this.patientAppointment.patient_secondary_email,
                            cell_phone: this.patientAppointment.patient_cell_phone,
                            visit_frequency: this.patientAppointment.patient_visit_frequency
                        },
                    ];
                    this.changePatient();
                }
            }
        },
        setFieldsForGoogleMeet() {
            this.isPatientDisabled = true;
            this.isVirtualTypeDisabled = true;
            this.patientsData.list = [
                {
                    id: this.googleMeet.patient.id,
                    full_name:
                        this.googleMeet.patient.first_name +
                        " " +
                        this.googleMeet.patient.last_name +
                        " " +
                        this.googleMeet.patient.middle_initial,
                    email: this.googleMeet.patient.email,
                    secondary_email: this.googleMeet.patient.secondary_email,
                    cell_phone: this.googleMeet.patient.cell_phone,
                },
            ];
            this.ruleForm = {
                patient_id: this.googleMeet.patient.id || null,
                date: moment().format("MM/DD/YYYY"),
                visit_type: this.virtual,
                status: "Active",
                email: this.googleMeet.patient.email,
                secondary_email: this.googleMeet.patient.secondary_email,
                cell_phone: this.googleMeet.patient.cell_phone,
                telehealth_provider: "google_meet",
                send_telehealth_link_via_email: false,
                send_telehealth_link_via_secondary_email: false,
                send_telehealth_link_via_sms: false,
                allow_to_join_by_phone: false,
            };
        },
        setFieldForCreatedPatient() {
            this.isPatientDisabled = true;
            this.patientsData.list = [
                {
                    id: this.createdPatient.patient_id,
                    full_name: this.createdPatient.full_name,
                    email: this.createdPatient.email,
                    secondary_email: this.createdPatient.secondary_email,
                    cell_phone: this.createdPatient.phone,
                },
            ];
            this.ruleForm.patient_id = this.createdPatient.patient_id || null;
            this.ruleForm.email = this.createdPatient.email || null;
            this.ruleForm.secondary_email = this.createdPatient.secondary_email || null;
            this.ruleForm.phone = this.createdPatient.phone || null;
        },
        openCompleteDialog() {
            this.loading = true;
            this.$store
                .dispatch("getCompleteAppointmentData", {
                    appointment_id: this.appointmentId,
                    patient_id: this.initPatientId,
                    action: 'complete'
                })
                .then(() => {
                    $("#confirm-telehealth").modal("show");
                    this.showAppointmentModal = false;
                })
                .finally(() => (this.loading = false));
        },
        openRescheduleDialog() {
            this.loading = true;
            this.$store
                .dispatch("getCompleteAppointmentData", {
                    appointment_id: this.appointmentId,
                    patient_id: this.initPatientId,
                    action: 'reschedule'
                })
                .then((response) => {
                    this.showAppointmentModal = false;
                    if (response.status === 200) {
                        $("#reschedule-appointment").modal("show");
                    }
                })
                .finally(() => (this.loading = false));
        },
        openCancelDialog() {
            this.loading = true;
            this.$store
                .dispatch("getCompleteAppointmentData", {
                    appointment_id: this.appointmentId,
                    patient_id: this.initPatientId,
                    action: 'cancel'
                })
                .then((response) => {
                    this.showAppointmentModal = false;
                    if (response.status === 200) {
                        $("#cancel-appointment").modal("show");
                    }
                })
                .finally(() => (this.loading = false));
        },
        closeEmailUnsubscribedDialog() {
            this.showEmailUnsubscribedDialog = false;
        },
        onEmailRemovedFromRejectList() {
            if (!this.$route.name === 'patient-chart' || !this.$route.params.id) {
                return;
            }

            this.$store.dispatch('getPatient', {patientId: this.$route.params.id})
                .catch(() => {
                    //
                });
            this.$store.dispatch("getPatientNotesWithDocumentsPaginated", {id: this.$route.params.id})
                .catch(() => {
                    //
                });
        },
        
        // getFeePerVisit(patientId, treatmentModalityId) {
        //     if (!patientId || !treatmentModalityId) {
        //         this.feePerVisit = null;
        //         return;
        //     }

        //     const payload = { patientId, treatmentModalityId };

        //     this.$store.dispatch("getFeePerVisitForProvider", payload)
        //         .then(({ data }) => {
        //             this.feePerVisit = data.fee_per_visit;
        //         })
        //         .catch(() => {
        //             this.feePerVisit = null;
        //         });
        // },

        fetchTreatmentModalitiesAndSetDefaultReason() {
            const params = {
                patient_id: this.ruleForm.patient_id,
                appointment_id: this.appointmentId,
            };
            this.$store.dispatch('getTreatmentModalities', params)
                .then(this.setDefaultReasonForVisit);
        },

        setDefaultReasonForVisit() {
            if (this.ruleForm.reason_for_visit) {
                // reset value if current reason is not in the list
                const currentVisitReason = this.visitReasonList.find(el => el.id === this.ruleForm.reason_for_visit);
                if (!currentVisitReason) {
                    this.ruleForm.reason_for_visit = null;
                }
                return;
            }

            if (!this.ruleForm.visit_type) {
                return;
            }

            const isTelehealth = this.ruleForm.visit_type === this.virtual ? 1 : 0;
            let defaultVisitReason = this.visitReasonList.find(el => el.insurance_procedure_id === INDIVIDUAL_60_MIN_INSURANCE_PROCEDURE_ID && Number(el.is_telehealth) === isTelehealth);
            if (!defaultVisitReason) {
                defaultVisitReason = this.visitReasonList.find(el => el.insurance_procedure_id === INITIAL_EVALUATION_PROCEDURE_ID && Number(el.is_telehealth) === isTelehealth);
            }
            this.ruleForm.reason_for_visit = defaultVisitReason && defaultVisitReason.id;
        },

        updateReasonForVisit() {
            if (!this.ruleForm.visit_type || !this.ruleForm.reason_for_visit) {
                return;
            }

            let visitReasonId = null;
            const currentVisitReason = this.visitReasonList.find(el => el.id === this.ruleForm.reason_for_visit);
            if (currentVisitReason) {
                const isTelehealth = this.ruleForm.visit_type === this.virtual ? 1 : 0;
                const newVisitReason = this.visitReasonList.find(el => el.insurance_procedure_id === currentVisitReason.insurance_procedure_id && Number(el.is_telehealth) === isTelehealth);
                visitReasonId = newVisitReason && newVisitReason.id;
            }
            
            this.ruleForm.reason_for_visit = visitReasonId;
        },

        checkRepeatVisibility() {
            const currentVisitReason = this.visitReasonList.find(el => el.id === this.ruleForm.reason_for_visit);
            if (currentVisitReason && currentVisitReason.insurance_procedure_id === INITIAL_EVALUATION_PROCEDURE_ID) {
                this.ruleForm.repeat = 0;
                this.showRepeat = false;
                this.appointmentsRepeatDates = [];
                return;
            }

            this.showRepeat = true;
        }
    },
    watch: {
        office_room(value) {
            if (value.length > 0) {
                value.map((item) => (this.ruleForm.office_room = item.office_id));
            }
        },
        patientAppointment() {
            if (!this.isTherapist && this.isPatient) {
                this.setFieldsForPatient();
            }
            if (this.isTherapist && this.isPatient) {
                this.setFieldPatient();
            }
        },
        googleMeet() {
            if (Object.keys(this.googleMeet).length) {
                this.setFieldsForGoogleMeet();
            }
        },
        "ruleForm.visit_type"(newValue, oldValue) {
            if (!oldValue) {
                return;
            }

            this.updateReasonForVisit();
        },
        "ruleForm.patient_id"(value) {
            this.fetchTreatmentModalitiesAndSetDefaultReason();
            // this.getFeePerVisit(value, this.ruleForm.reason_for_visit);
        },
        "ruleForm.reason_for_visit"(value) {
            // if (!this.isUserAdmin) {
            //     this.getFeePerVisit(this.ruleForm.patient_id, value);
            // }

            this.checkRepeatVisibility();

            setTimeout(() => {
                this.$refs.ruleForm.clearValidate('reason_for_visit');
            }, 10);
        },
    },
    mounted() {
        this.initOfficeList();
        this.initStatusList();
        this.initRepeatList();
        
        if (!this.isTherapist) {
            this.initProviderList();
        }
        this.initPatientList();
        this.setNowDate();
        if (this.isEditable && Object.keys(this.appointments).length) {
            this.setFields();
        }
        if (this.isEditable && Object.keys(this.tableAppointments).length) {
            this.setFieldsForTable();
        }
        if (this.isEditable && Object.keys(this.scheduleAppointments).length) {
            this.setFieldsForSchedule();
        }
        if (this.isTherapist && this.selectTime) {
            this.setFieldTime();
        }
        if (this.editable && Object.keys(this.fields).length) {
            this.setField();
        }
        if (!this.isTherapist && this.isPatient) {
            this.setFieldsForPatient();
        }
        if (this.isTherapist && this.isPatient) {
            this.setFieldPatient();
        }
        if (!this.editable) {
            this.rules.patient_id = [
                {
                    required: true,
                    message: "The patient field is required",
                    trigger: "change",
                },
            ];
        }
        if (Object.keys(this.createdPatient).length) {
            this.$nextTick(() => {
                this.setFieldForCreatedPatient();
            });
        }
        if (Object.keys(this.googleMeet).length) {
            this.setFieldsForGoogleMeet();
        }
        this.changePatient();
    },
};
</script>

<style lang="scss">
.appointment-modal {
    .col-12 {
        padding: 0 15px;
    }

    .dialog-footer {
        display: flex;
        justify-content: flex-end;

        &-item {
            &--start {
                margin-right: auto;
            }
        }
    }

    .error-message {
        font-size: 14px; 
        word-break: break-word; 
        text-align: left;
    }
}

.saving-loader {
    position: absolute;
    left: calc(50% - 150px);
    top: calc(50% - 150px);
    z-index: 5000;
}

.row-virtual-data {
    width: 100%;
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;

    &__checked {
        width: 100%;
    }

    &__description {
        margin: 15px 0;
        padding-left: 15px;
    }

    &__wrapper {
        width: 100%;
        display: flex;
        justify-content: space-between;
    }
}

.form-item-virtual-data {
    .el-form-item__content {
        display: flex;
        width: 100%;
    }

    .el-checkbox {
        margin-right: 15px;
    }

    .el-form-item__error {
        padding-top: 0;
    }
}

.remove-appointments-button {
    float: left;
}

@media (min-width: 992px) {
    .col-md-offset-right-6 {
        margin-right: 50%;
    }
}

</style>
