<template>
    <div class="documents-box">

        <div class="documents-box__controls">
            <div class="row row--flex">
                <div class="col justify-start">
                    <div class="form-group">
                        <div class="checkbox checkbox-toggler">
                            <label :for="`allDocuments-${unicID}`">
                                <input
                                        type="checkbox"
                                        name="all_docs"
                                        :id="`allDocuments-${unicID}`"
                                        v-model="allFormsToggler"
                                        @click="toggleAllForms"
                                />
                                Select All
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col justify-center">
                    <div
                        v-if="errors.all().length > 0"
                        class="alert alert-danger"
                        role="alert"
                    >
                        Please make sure you have filled all the required fields
                    </div>
                    <div
                        v-if="responseError"
                        class="alert alert-danger"
                        role="alert"
                    >
                        {{ responseError }}
                    </div>
                    <div
                        v-else-if="formsSent"
                        class="alert alert-success"
                        role="alert"
                    >
                        The documents have been sent successfully
                    </div>
                </div>
                <div class="col justify-end">
                    <div class="text-right">
                        <div class="dropdown dropdown-send">
                            <button
                                v-if="!forModal"
                                id="dLabel"
                                type="button"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                                class="btn btn-primary btn-dropdown"
                                :disabled="selectedForms.length < 1 || errors.all().length > 0"
                            >
                                <span class="btn__text">
                                    Send to
                                </span>
                                <span class="btn__caret">
                                    <img class="" src="/images/icons/icon-caret.svg"/>
                                </span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dLabel">
                                <li class="dropdown-menu__item">
                                    <a
                                        href="#"
                                        role="button"
                                        @click.prevent="validateAndOpenModal('email')"
                                    >
                                        <span class="menu-item__icon">
                                            <svg
                                                width="23"
                                                height="19"
                                                viewBox="0 0 23 19"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M2.19478 5.57028C2.48405 5.77559 3.356 6.38535 4.81068 7.39926C6.2654 8.41317 7.37982 9.19385 8.15398 9.74135C8.23903 9.80137 8.41973 9.93182 8.69617 10.1329C8.97265 10.3341 9.20238 10.4967 9.38519 10.6208C9.56812 10.7447 9.78927 10.8838 10.0489 11.0378C10.3084 11.1916 10.553 11.3073 10.7827 11.3839C11.0124 11.4612 11.225 11.4994 11.4207 11.4994H11.4335H11.4464C11.642 11.4994 11.8548 11.4611 12.0845 11.3839C12.3141 11.3073 12.5589 11.1915 12.8182 11.0378C13.0776 10.8836 13.2988 10.7447 13.4817 10.6208C13.6646 10.4967 13.8942 10.3341 14.1707 10.1329C14.4471 9.93164 14.6281 9.80137 14.7131 9.74135C15.4956 9.19385 17.4865 7.80336 20.685 5.57001C21.306 5.13382 21.8248 4.60749 22.2416 3.99139C22.6587 3.37555 22.867 2.72951 22.867 2.05362C22.867 1.48881 22.6649 1.00532 22.2607 0.603201C21.8566 0.200992 21.378 0 20.8253 0H2.04161C1.38661 0 0.882543 0.222436 0.529499 0.667307C0.1765 1.11227 0 1.66845 0 2.3358C0 2.87485 0.234022 3.45899 0.701842 4.08786C1.16962 4.71677 1.66743 5.21096 2.19478 5.57028Z"
                                                    fill="currentColor"
                                                />
                                                <path
                                                    d="M21.5908 6.94349C18.8008 8.84287 16.6822 10.319 15.2362 11.3716C14.7513 11.7308 14.358 12.0112 14.0559 12.2122C13.7538 12.4133 13.352 12.6186 12.85 12.8281C12.3482 13.038 11.8805 13.1426 11.4466 13.1426H11.4335H11.4207C10.9869 13.1426 10.5189 13.038 10.0171 12.8281C9.51525 12.6186 9.11322 12.4133 8.81122 12.2122C8.5093 12.0112 8.11581 11.7308 7.63096 11.3716C6.48244 10.5245 4.36847 9.04831 1.28895 6.94349C0.803925 6.61856 0.374319 6.24611 0 5.8269V16.0174C0 16.5825 0.19983 17.0657 0.599714 17.4679C0.999509 17.8702 1.48025 18.0713 2.04175 18.0713H20.8254C21.3868 18.0713 21.8675 17.8702 22.2673 17.4679C22.6673 17.0656 22.8671 16.5826 22.8671 16.0174V5.8269C22.5012 6.23743 22.076 6.60988 21.5908 6.94349Z"
                                                    fill="currentColor"
                                                />
                                            </svg>
                                        </span>
                                        Email
                                    </a>
                                </li>
                                <li class="dropdown-menu__item">
                                    <a
                                        href="#"
                                        role="button"
                                        @click.prevent="validateAndOpenModal('phone')"
                                    >
                                        <span class="menu-item__icon">
                                            <svg
                                                width="17"
                                                height="25"
                                                viewBox="0 0 17 25"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    clip-rule="evenodd"
                                                    d="M0.6875 3.125C0.6875 2.2962 1.01674 1.50134 1.60279 0.915291C2.18884 0.32924 2.9837 0 3.8125 0L13.1875 0C14.0163 0 14.8112 0.32924 15.3972 0.915291C15.9833 1.50134 16.3125 2.2962 16.3125 3.125V21.875C16.3125 22.7038 15.9833 23.4987 15.3972 24.0847C14.8112 24.6708 14.0163 25 13.1875 25H3.8125C2.9837 25 2.18884 24.6708 1.60279 24.0847C1.01674 23.4987 0.6875 22.7038 0.6875 21.875V3.125ZM10.0625 20.3125C10.0625 20.7269 9.89788 21.1243 9.60485 21.4174C9.31183 21.7104 8.9144 21.875 8.5 21.875C8.0856 21.875 7.68817 21.7104 7.39515 21.4174C7.10212 21.1243 6.9375 20.7269 6.9375 20.3125C6.9375 19.8981 7.10212 19.5007 7.39515 19.2076C7.68817 18.9146 8.0856 18.75 8.5 18.75C8.9144 18.75 9.31183 18.9146 9.60485 19.2076C9.89788 19.5007 10.0625 19.8981 10.0625 20.3125V20.3125Z"
                                                    fill="currentColor"
                                                />
                                            </svg>
                                        </span>
                                        Phone
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="appointment-documents">
            <el-collapse
                    v-model="activeCollapse"
                    class="collapse-document"
                    v-if="patientForms && patientForms.length > 0"
            >
                <template v-for="form in patientForms">
                    <el-collapse-item
                            v-if="(forModal && form.visible_in_modal) || !forModal"
                            class="collapse-document__item"
                            :class="{ invalid: errors.all(form.name).length > 0 }"
                            :key="form.name"
                            :name="form.name"
                            :disabled="
              forModal &&
              (form.name === 'credit_card_on_file' || form.name === 'telehealth' )
            "
                    >
                        <template slot="title">
                            <div class="collapse-document__item-title"
                                 :class="{'is-show-date': forModal && form.requests.length}">
                                <input
                                        type="checkbox"
                                        :name="form.name"
                                        :id="form.name"
                                        v-model="form.checked"
                                        @click="selectForm(form.name, $event)"
                                />
                                {{ form.title }}
                                <template v-if="forModal">
                                  <div v-if="form.requests.length" class="collapse-document__item-title__date">
                                    Sent At {{formDate(form.requests)}}
                                  </div>
                                  <div v-else class="collapse-document__item-title__date collapse-document__item-title__date_danger">
                                    Not Sent
                                  </div>
                                </template>

                            </div>
                        </template>
                        <form
                                v-if="form.name !== 'telehealth'"
                                class="collapse-document__item-form"
                                :class="{
                'form-horizontal':
                  form.name === 'payment_for_service' ||
                  form.name === 'confidential_information',
                'margin-bottom-30': form.name === 'payment_for_service',
                'margin-bottom-10': form.name !== 'payment_for_service',
              }"
                                :data-vv-scope="form.name"
                        >
                            <template v-if="form.name === 'payment_for_service'">
                                <div
                                        class="form-group"
                                        :class="{ 'has-error': errors.has(`${form.name}.co_pay`) }"
                                >
                                    <label for="coPay" class="col-sm-4 control-label">
                                        Co-pay and/or co-insurance for session
                                    </label>
                                    <div class="col-sm-8">
                                        <input
                                                type="text"
                                                class="form-control"
                                                id="coPay"
                                                placeholder=""
                                                name="co_pay"
                                                v-validate="
                        `${!form.formData.self_pay ? 'required' : ''}`
                      "
                                                data-vv-validate-on=""
                                                v-model="form.formData.co_pay"
                                                :disabled="true"
                                        />
                                    </div>
                                </div>

                                <div
                                    v-if="showDeductible"
                                    class="form-group"
                                    :class="{
                                        'has-error': errors.has(`${form.name}.for_session`),
                                    }"
                                >
                                    <label for="forSession" class="col-sm-4 control-label">
                                        Payment for session not covered due to deductible
                                    </label>
                                    <div class="col-sm-8">
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="forSession"
                                            placeholder=""
                                            name="for_session"
                                            v-model="form.formData.payment_for_session_not_converted"
                                            :disabled="true"
                                        />
                                    </div>
                                </div>

                                <div
                                        class="form-group"
                                        :class="{ 'has-error': errors.has(`${form.name}.self_pay`) }"
                                >
                                    <label for="selfPay" class="col-sm-4 control-label">
                                        Self-pay for session when paid out-of-pocket
                                    </label>
                                    <div class="col-sm-8">
                                        <input
                                                type="text"
                                                class="form-control"
                                                id="selfPay"
                                                placeholder=""
                                                name="self_pay"
                                                v-validate="`${!form.formData.co_pay ? 'required' : ''}`"
                                                data-vv-validate-on=""
                                                v-model="form.formData.self_pay"
                                                :disabled="true"
                                        />
                                    </div>
                                </div>
                                <div
                                        class="form-group"
                                        :class="{
                    'has-error': errors.has(`${form.name}.charge_cancellation`),
                  }"
                                >
                                    <label
                                            for="chargeCancellation"
                                            class="col-sm-4 control-label"
                                    >
                                        Charge for cancellation without 24 hours’ notice
                                    </label>
                                    <div class="col-sm-8">
                                        <input
                                                type="text"
                                                class="form-control"
                                                id="chargeCancellation"
                                                placeholder=""
                                                name="charge_cancellation"
                                                v-validate="'required'"
                                                data-vv-validate-on=""
                                                v-model="form.formData.charge_for_cancellation"
                                                disabled
                                        />
                                    </div>
                                </div>
                                <div
                                        class="form-group"
                                        :class="{
                    'has-error': errors.has(`${form.name}.other_charges_price`),
                  }"
                                >
                                    <label for="otherCharges" class="col-sm-4 control-label">
                                        Other charges (price)
                                    </label>
                                    <div class="col-sm-8">
                                        <input
                                                type="text"
                                                class="form-control"
                                                id="otherCharges"
                                                placeholder=""
                                                name="other_charges"
                                                v-model="form.formData.other_charges_price"
                                                :disabled="!form.checked || isPaymentForbidden"
                                                @change="clearSpecifyField(form)"
                                        />
                                    </div>
                                </div>
                                <div
                                        class="form-group"
                                        :class="{
                    'has-error': errors.has(`${form.name}.other_charges_price`),
                  }"
                                >
                                    <label for="otherPrice" class="col-sm-4 control-label">
                                        Other charges (specify)
                                    </label>
                                    <div class="col-sm-8">
                                        <input
                                                type="text"
                                                class="form-control long"
                                                id="otherPrice"
                                                placeholder=""
                                                name="other_price"
                                                v-model="form.formData.other_charges"
                                                v-validate="`${form.formData.other_charges_price && parseFloat(form.formData.other_charges_price) ? 'required' : ''}`"
                                                data-vv-validate-on=""
                                                :disabled="!form.checked || !form.formData.other_charges_price || !parseFloat(form.formData.other_charges_price) || isPaymentForbidden"
                                        />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment" class="col-sm-4 control-label">
                                        Comment
                                    </label>
                                    <div class="col-sm-8">
                                        <input
                                            type="text"
                                            class="form-control long"
                                            id="comment"
                                            placeholder=""
                                            name="comment"
                                            v-model="comment"
                                            :disabled="!form.checked"
                                        />
                                    </div>
                                </div>
                            </template>
                            <template v-else-if="form.name === 'confidential_information'">
                                <form-list-input
                                        id="exchange_with"
                                        name="exchange_with"
                                        placeholder="Add person"
                                        :list="form.formData.exchange_with"
                                        :disabled="!form.checked"
                                        :has-error="errors.all(form.name).length > 0"
                                        @change="
                    (data) => changeListData(form.formData.exchange_with, data)
                  "
                                />
                                <input
                                        type="hidden"
                                        class="form-control long"
                                        id="release"
                                        name="authorization_to_release"
                                        v-validate="
                    `${
                      form.formData.exchange_with.length < 1
                        ? 'required|minLength:1'
                        : ''
                    }`
                  "
                                        :disabled="!form.checked"
                                        v-model="form.formData.exchange_with"
                                />
                            </template>
                            <template v-else-if="form.name === 'supporting_documents'">
                                <div class="row--flex row-inputs">
                                    <div
                                            class="checkbox"
                                            :class="{ 'has-error': errors.all(form.name).length > 0 }"
                                    >
                                        <label>
                                            <input
                                                    type="checkbox"
                                                    name="insurance"
                                                    :disabled="!form.checked"
                                                    v-validate="
                          `${
                            supportingDocuments.length < 1
                              ? 'required|minLength:1'
                              : ''
                          }`
                        "
                                                    data-vv-validate-on=""
                                                    value="Insurance"
                                                    v-model="defaultDocuments"
                                            />
                                            Insurance
                                        </label>
                                    </div>
                                    <div
                                            class="checkbox"
                                            :class="{ 'has-error': errors.all(form.name).length > 0 }"
                                    >
                                        <label>
                                            <input
                                                    type="checkbox"
                                                    name="license"
                                                    :disabled="!form.checked"
                                                    :checked="true"
                                                    v-validate="
                          `${
                            supportingDocuments.length < 1
                              ? 'required|minLength:1'
                              : ''
                          }`
                        "
                                                    data-vv-validate-on=""
                                                    value="Driver's License"
                                                    v-model="defaultDocuments"
                                            />
                                            Driver's License
                                        </label>
                                    </div>
                                </div>
                                <form-list-input
                                        id="supporting-documents"
                                        name="supporting_document"
                                        placeholder="Add document"
                                        :list="customDocumentsList"
                                        :disabled="!form.checked"
                                        :has-error="errors.all(form.name).length > 0"
                                        @change="(data) => changeListData(customDocumentsList, data)"
                                />
                            </template>
                        </form>

                        <table-logs
                                v-if="!forModal"
                                :logs="form.requests"
                                :patient="patient"
                                :formName="form.name"
                                @open-send-modal="setLogForSend"
                        />
                    </el-collapse-item>
                </template>
            </el-collapse>
        </div>

        <template v-if="!forModal">
            <send-documents-modal
                    :is-sending="isSending"
                    :selected-forms="preparedForms"
                    :send-to="patient.email"
                    :method="method"
                    :error-messages="errorMessages"
                    :phone="patient.cell_phone"
                    :patient="patient"
                    @submit="(options) => sendDocuments(options)"
                    @close="closeSendDocumentsModal"
            />

            <div
                    id="sendToEmail"
                    class="modal modal-vertical-center fade"
                    data-backdrop="static"
                    data-keyboard="false"
                    tabindex="-1"
                    role="dialog"
            >
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Send Document</h4>
                        </div>
                        <form method="post" @submit.prevent="sendDocumentByEmail()">
                            <div class="modal-body">
                                <p class="">
                                    Document will be sent to <b>{{ patient.email }}</b>
                                </p>
                                <p class="">Are you sure you want to do this?</p>
                            </div>
                            <div class="modal-footer">
                                <button
                                        type="submit"
                                        class="btn btn-primary"
                                        :disabled="isSendingLog"
                                >
                                    Yes
                                </button>
                                <button
                                        class="btn btn-default"
                                        data-dismiss="modal"
                                        :disabled="isSendingLog"
                                >
                                    No
                                </button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
        </template>
    </div>
</template>

<script>
    import FormListInput from "./forms/partials/FormListInput";
    import SendDocumentsModal from "./appointments/SendDocumentsModal";
    import DocumentAlertModal from "./appointments/DocumentAlertModal";
    import {eventBus} from "../app";
    import { NEW_PATIENT_FORM_NAME, PAYMENT_FOR_SERVICE_FORM_NAME, PAYMENT_FOR_SERVICE_FORM_TYPE_ID, DEFAULT_CANCELLATION_FEE } from '../settings';

    export default {
        name: "PatientFormsCollapse",
        components: {
            FormListInput,
            SendDocumentsModal,
            DocumentAlertModal,
        },
        props: {
            forModal: {
                type: Boolean,
            },
            email: {
                validator: (prop) => typeof prop === "string" || prop === null,
            },
            patient: {
                validator: (prop) => typeof prop === "object" || prop === null,
            },
            documentsSent: {
                type: Boolean,
            },
            validate: {
                type: Boolean,
                default: false
            },
        },
        data: () => ({
            activeCollapse: null,
            allFormsToggler: false,
            formsSent: false,
            responseError: '',
            isSending: false,
            isSendingLog: false,
            defaultDocuments: [],
            customDocumentsList: [],
            method: "email",
            errorMessages: null,
            logForSend: null,
            unicID: 0,
            comment: "",
            newPatientFormName: NEW_PATIENT_FORM_NAME,
            paymentForServiceFormName: PAYMENT_FOR_SERVICE_FORM_NAME,
        }),
        methods: {
            clearSpecifyField(form) {
                if (!form.formData.other_charges_price) {
                    form.formData.other_charges = '';
                }
            },

            setLogForSend(data) {
                this.logForSend = data;
            },
            sendDocumentByEmail() {
                this.isSendingLog = true;
                this.$store
                    .dispatch("sendPatientDocumentsByEmail", this.logForSend)
                    .finally(() => {
                        $("#sendToEmail").modal("hide");
                        this.logForSend = null;
                        this.isSendingLog = false;
                    });
            },
            changeListData(list, data) {
                list = data;
            },
            closeSendDocumentsModal() {
                $("#sendDocuments").modal("hide");
            },

            emitSendDocumentsModal(method) {
                this.$emit("modal-open", {method: method});

                this.openSendDocumentsModal();
            },

            openSendDocumentsModal() {
                window.setTimeout(() => {
                    $("#sendDocuments").modal("show");
                }, 500);
            },

            toggleAllForms() {
                this.allFormsToggler = !this.allFormsToggler;
                for (let form of this.patientForms) {
                    if ((this.forModal && form.visible_in_modal) || !this.forModal) {
                        form.checked = this.allFormsToggler;
                        if (!this.allFormsToggler) {
                            this.errors.clear(form.name);
                        }
                    }
                }
            },

            selectForm(formName, event) {
                event.stopPropagation();
                this.errors.clear(formName);
            },

            getLogs(key) {
                return this.patientForms[key].requests;
            },

            validateForms() {
                let formsFilled = true;

                let newActiveCollapse = [];

                return new Promise((resolve) => {
                    let validatorResponses = [];
                    for (let form of this.patientForms) {
                        if (form.checked) {
                            validatorResponses.push(this.$validator.validateAll(form.name).then(valid => {
                                if (!valid) {
                                    newActiveCollapse.push(form.name);
                                }
                                return valid;
                            }));
                        }
                    }
                    Promise.all(validatorResponses).then((response) => {
                        this.activeCollapse = newActiveCollapse;
                        for (let result of response) {
                            if (!result) {
                                formsFilled = false;
                                break;
                            }
                        }
                        eventBus.$emit('patientFormsValid', formsFilled)
                        resolve(formsFilled);
                    });
                });
            },

            prepareData() {
                let forms = [];

                for (let form of this.selectedForms) {
                    forms.push({name: form.name, metadata: form.formData});
                }

                return {
                    email: this.patient.email,
                    forms: forms,
                };
            },

            clearFormsData() {
                this.allFormsToggler = false;
                this.activeCollapse = null;
                this.comment = "";
                this.responseError = '';
                for (let form of this.patientForms) {
                    this.$set(form, "checked", false);
                    if (form.name === "supporting_documents") {
                        this.$set(form, "formData", {
                            documents: [],
                        });
                        this.defaultDocuments = ["Insurance", "Driver's License"];
                    } else if (form.name === "confidential_information") {
                        if (this.patient && this.patient.primary_insurance) {
                            this.$set(form, "formData", {
                                exchange_with: [{id: 1, text: this.patient.primary_insurance}],
                            });
                        } else {
                            this.$set(form, "formData", {
                                exchange_with: [],
                            });
                        }
                    } else if (form.name === this.paymentForServiceFormName) {
                        //
                    } else {
                        this.$set(form, "formData", {});
                    }
                }
                this.customDocumentsList = [];
            },

            sendDocuments(options) {
                this.isSending = true;
                let data = _.clone(this.preparedFormData);
                data[options.method] = options[options.method];
                data.send_via_email = false;
                data.send_via_sms = false;
                options.method === 'email' ? data.send_via_email = true : data.send_via_sms = true;
                this.errorMessages = null;

                return this.$store.dispatch('sendPatientForms', {patient_id: this.patient.id, data})
                    .then(() => {
                        this.clearFormsData();
                        this.closeSendDocumentsModal();
                        this.errors.clear();
                        this.$nextTick(() => {
                            this.formsSent = true;
                        });
                    })
                    .catch((error) => {
                        let response = error.response.data;
                        
                        if (response.errors.forms && response.errors.forms.length) {
                            this.responseError = response.errors.forms[0];
                            this.closeSendDocumentsModal();
                            return;
                        }

                        if ("error" in response) {
                            if (options.method === 'phone') {
                                let message = response.error;
                                if (response.error.search("'To'") !== -1) {
                                    message = message.replace("'To' ", "");
                                }
                                this.errorMessages = {
                                    phone: message,
                                };
                            } else if (options.method === 'email') {
                                this.errorMessages = {
                                    email: response.error,
                                };
                            }
                            this.$emit("twilio-error", this.errorMessages);
                        }
                    })
                    .finally(() => {
                        this.isSending = false;
                    });
            },

            openModal() {
                this.emitPreparedForms();
                if (this.forModal) {
                    this.emitSendDocumentsModal(this.method);
                } else {
                    this.openSendDocumentsModal();
                }
            },

            validateAndOpenModal(method) {
                this.method = method;
                this.validateForms().then((valid) => {
                    if (valid) {
                        this.emitPreparedForms();
                        if (this.forModal) {
                            this.emitSendDocumentsModal(method);
                        } else {
                            this.openSendDocumentsModal();
                        }
                    }
                });
            },

            emitPreparedForms() {
                this.$emit("prepared-forms", this.preparedForms);
                this.formsSent = false;
            },

            initFormsSelection() {
                for (let form of this.patientForms) {
                    if (
                        (this.forModal && !form.visible_in_modal) ||
                        (!this.forModal && !form.visible_in_tab)
                    ) {
                        continue;
                    }
                    if (form.is_required) {
                        this.$set(form, "checked", true);
                    } else {
                        this.$set(form, "checked", false);
                    }
                }
            },

            initUnicID() {
                this.$store.dispatch('getUnicPatientFormId').then(id => {
                    this.unicID = id;
                });
            },

            formDate(requests) {
                return moment(requests[requests.length - 1].sent_at).format('MM/DD/YYYY hh:mm A');
            },
        },
        computed: {
            is_kaiser_patient() {
                return this.patient && this.patient.primary_insurance && this.patient.primary_insurance.search('Kaiser') !== -1;
            },

            requiredNotFilledForms() {
                return this.patientForms.filter(function (form) {
                    if (!form.is_required || (this.forModal && !form.visible_in_modal) || (!this.forModal && !form.visible_in_tab)) {
                        return false;
                    }

                    return true;
                }.bind(this));
            },

            selectedForms() {
                return this.patientForms.filter((form) => form.checked);
            },

            patientForms() {
                let forms = [];
                if (this.forModal) {
                    forms = this.$store.state.patient_forms_modal;
                } else {
                    forms = this.$store.state.patient_forms;
                }

                for (let form of forms) {
                    if (
                        (this.forModal && !form.visible_in_modal) ||
                        (!this.forModal && !form.visible_in_tab)
                    ) {
                        continue;
                    }

                    if (form.name === this.paymentForServiceFormName) {
                        this.$set(form, "disabledInModal", true);

                        let formData = {
                            co_pay: 0,
                            payment_for_session_not_converted: 0,
                            self_pay: 0,
                            charge_for_cancellation: 0,
                            other_charges_price: 0,
                            other_charges: ''
                        };

                        if (this.patient && !this.isPaymentForbidden) {
                            const { is_self_pay, visit_copay, self_pay, insurance_pay } = this.patient;
                                    
                            formData.co_pay = is_self_pay ? 0 : visit_copay;
                            formData.payment_for_session_not_converted = is_self_pay ? 0 : insurance_pay;
                            formData.self_pay = is_self_pay ? self_pay : 0;
                            formData.charge_for_cancellation = DEFAULT_CANCELLATION_FEE;
                        }
                        
                        this.$set(form, "formData", formData);
                    } else if (form.name === "telehealth") {
                        this.$set(form, "disabledInModal", true);
                        this.$set(form, "formData", {});
                    } else if (form.name === "supporting_documents") {
                        this.$set(form, "formData", {
                            documents: [],
                        });
                        this.defaultDocuments = ["Insurance", "Driver's License"];
                    } else if (form.name === "confidential_information") {
                        if (this.patient && this.patient.primary_insurance) {
                            this.$set(form, "formData", {
                                exchange_with: [{id: 1, text: this.patient.primary_insurance}],
                            });
                        } else {
                            this.$set(form, "formData", {
                                exchange_with: [],
                            });
                        }
                    } else {
                        this.$set(form, "formData", {});
                    }
                }

                return forms;
            },

            preparedFormData() {
                let forms = [];

                let hasConfidentialInformation = false;
                let hasTelehealth = false;
                for (let form of this.selectedForms) {
                    let formData = null;
                    if (form.name === "telehealth") {
                        hasTelehealth = true;
                    }
                    if (form.name === "supporting_documents") {
                        formData = _.clone(form.formData);
                        formData.documents = this.supportingDocuments;
                    } else if (form.name === "confidential_information") {
                        hasConfidentialInformation = true;
                        formData = {
                            exchange_with: form.formData.exchange_with.map((item) => item.text),
                        };
                    } else {
                        formData = form.formData;
                    }
                    forms.push({
                        name: form.name,
                        title: form.title,
                        metadata: formData,
                        comment: this.comment
                    });
                }

                return {
                    forms: forms,
                };
            },

            preparedForms() {
                return this.preparedFormData.forms;
            },

            supportingDocuments() {
                let checkboxDocuments = _.clone(this.defaultDocuments);

                let customDocuments = _.clone(
                    this.customDocumentsList.map((doc) => doc.text)
                );
                let result = checkboxDocuments.concat(customDocuments);
                return result;
            },

            visitCoPay() {
                return this.$store.state.selectPatient ? this.$store.state.selectPatient.visit_copay || 0 : 0;
            },

            isPaymentForbidden() {
                return this.$store.state.selectPatient && !!this.$store.state.selectPatient.is_payment_forbidden;
            },

            provider() {
                return this.$store.state.currentProvider;
            },

            isUserAdmin() {
                return this.$store.state.isUserAdmin;
            },

            showDeductible() {
                return this.isUserAdmin || (this.provider && this.provider.is_collect_payment_available);
            },
        },
        watch: {
            selectedForms() {
                this.$emit("selected-forms", this.selectedForms);
                this.formsSent = false;
            },
            preparedForms() {
                this.emitPreparedForms();
            },
            validate() {
                this.validateForms();
            }
        },
        mounted() {
            this.initFormsSelection();
            
            this.$emit("forms-created");
            this.initUnicID();
        },
    };
</script>

<style scoped>
    .margin-bottom-30 {
        margin-bottom: 30px;
    }

    .margin-bottom-10 {
        margin-bottom: 10px;
    }
</style>
