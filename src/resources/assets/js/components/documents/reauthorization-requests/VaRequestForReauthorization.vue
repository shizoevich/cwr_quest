<template>
    <div>
        <div
            v-if="statuses && statuses.saving"
            class="black-layer"
            style="position: fixed; top: 0"
        >
            <pageloader add-classes="saving-loader" image-alt="Saving..."></pageloader>
        </div>

        <div
            :id="document_name"
            class="modal modal-vertical-center fade"
            data-backdrop="static"
            data-keyboard="false"
        >
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <button
                                    type="button"
                                    class="close"
                                    data-dismiss="modal"
                                    @click.prevent="closeDocument"
                                >
                                    &times;
                                </button>
                                <h4
                                    class="modal-title"
                                    v-html="computed_modal_title()"
                                ></h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document" novalidate>
                                <div class="row input-row">
                                    <document-textarea
                                        name="va_facility_name"
                                        label="VA FACILITY NAME"
                                        size="col-md-3"
                                        :maxlength="60"
                                        v-model="form_data.va_facility_name"
                                    ></document-textarea>
                                    <document-textarea
                                        name="va_facility_location"
                                        label="VA FACILITY LOCATION"
                                        size="col-md-3"
                                        :maxlength="60"
                                        v-model="form_data.va_facility_location"
                                    ></document-textarea>
                                    <document-textarea
                                        name="va_authorization_number"
                                        :label="`VA AUTHORIZATION / REFERRAL NUMBER ${requiredIndicator}`"
                                        size="col-md-3"
                                        validateRules="required"
                                        :maxlength="30"
                                        v-model="form_data.va_authorization_number"
                                    ></document-textarea>
                                    <div
                                        class="form-group col-md-3 input-container document-date">
                                        <label class="control-label input-label">
                                            TODAY'S DATE (mm/dd/yyyy)
                                        </label>
                                        <el-date-picker
                                            v-model="form_data.today_date"
                                            :editable="false"
                                            :format="datePickerFormat"
                                            :value-format="datePickerValueFormat"
                                            :disabled="statuses.editingDisabled"
                                            :picker-options="datePickerBirthDateOptions"
                                            type="date"
                                            name="today_date"
                                            @focus="pickerFocus('today_date')"
                                            @blur="pickerBlur('today_date')"
                                        ></el-date-picker>
                                    </div>
                                </div>

                                <br />
                                <div class="row text-center">
                                    <label>VETERAN INFORMATION</label>
                                </div>

                                <div class="row input-row">
                                    <document-input
                                        name="veteran_name"
                                        :label="`VETERAN'S NAME ${requiredIndicator} (Last, First, MI)`"
                                        size="col-md-9"
                                        validateRules="required"
                                        :maxlength="110"
                                        v-model="form_data.veteran_name"
                                    ></document-input>
                                    <div
                                        id="birth-date"
                                        class="form-group col-md-6 input-container document-date"
                                    >
                                        <label
                                            class="control-label input-label"
                                            v-html="`DATE OF BIRTH ${requiredIndicator} (mm/dd/yyyy)`"
                                        ></label>
                                        <el-date-picker
                                            v-model="form_data.birth_date"
                                            name="birth_date"
                                            :editable="false"
                                            :format="datePickerFormat"
                                            :value-format="datePickerValueFormat"
                                            :disabled="statuses.editingDisabled"
                                            :picker-options="datePickerBirthDateOptions"
                                            type="date"
                                            @focus="pickerFocus('birth_date')"
                                            @blur="pickerBlur('birth_date')"
                                        ></el-date-picker>
                                    </div>
                                </div>

                                <br />
                                <div class="row text-center">
                                    <label>ORDERING PROVIDER INFORMATION</label>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                        name="ordering_provider_name"
                                        :label="`ORDERING PROVIDERS NAME ${requiredIndicator}`"
                                        size="col-md-4"
                                        validateRules="required"
                                        :maxlength="60"
                                        v-model="form_data.ordering_provider_name"
                                    ></document-textarea>
                                    <document-textarea
                                        name="ordering_provider_npi"
                                        :label="`ORDERING PROVIDERS NPI ${requiredIndicator}`"
                                        size="col-md-4"
                                        validateRules="required"
                                        :maxlength="50"
                                        v-model="form_data.ordering_provider_npi"
                                    ></document-textarea>
                                    <document-textarea
                                        name="ordering_provider_contact_number"
                                        :label="`ORDERING PROVIDERS 24-HR EMERGENCY CONTACT NUMBER ${requiredIndicator} (for abnormal/critical findings)`"
                                        validateRules="required"
                                        size="col-md-4"
                                        :maxlength="50"
                                        v-model="form_data.ordering_provider_contact_number"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-input
                                        name="ordering_provider_office_phone"
                                        validateRules="required"
                                        :label="`ORDERING PROVIDERS OFFICE PHONE ${requiredIndicator}`"
                                        size="col-md-4"
                                        :maxlength="40"
                                        v-model="form_data.ordering_provider_office_phone"
                                    ></document-input>
                                    <document-input
                                        name="ordering_provider_fax_number"
                                        validateRules="required"
                                        :label="`ORDERING PROVIDERS FAX NUMBER ${requiredIndicator}`"
                                        size="col-md-4"
                                        :maxlength="15"
                                        v-model="form_data.ordering_provider_fax_number"
                                    ></document-input>
                                    <document-input
                                        name="ordering_provider_secure_email"
                                        validateRules="required"
                                        :label="`ORDERING PROVIDERS SECURE EMAIL ADDRESS ${requiredIndicator}`"
                                        size="col-md-4"
                                        :maxlength="50"
                                        v-model="form_data.ordering_provider_secure_email"
                                    ></document-input>
                                </div>

                                <br />
                                <div class="row text-center">
                                    <label>
                                        REQUESTED SERVICE - ONE SERVICE PER FORM
                                    </label>
                                </div>

                                <div class="row d-flex">
                                    <div
                                        id="new-request"
                                        class="input-container form-group col-md-7"
                                        style="padding: 0"
                                    >
                                        <div style="padding: 0 15px">
                                            <div
                                                class="group-title"
                                                v-html="`NEW REQUEST: ${requiredIndicator} (Each request must be entered on a separate form)`"
                                            ></div>
                                            <div class="d-flex" style="position: relative">
                                                <div class="radio-group d-flex flex-column">
                                                    <label
                                                        class="form-check-label"
                                                        v-for="option in newRequestOptions"
                                                        :key="option.key"
                                                    >
                                                        <input
                                                            type="radio"
                                                            class="form-check-input"
                                                            name="new-request"
                                                            :checked="form_data[option.key]"
                                                            @change="
                                                                changeRadioOption(
                                                                    option.key,
                                                                    'newRequestOptions',
                                                                )
                                                            " />
                                                        {{ option.title }}
                                                    </label>
                                                </div>
                                                <div class="new-request-text-block">
                                                    <label class="form-label d-flex">
                                                        <div>PROCEDURE:</div>
                                                        <input
                                                            class="input"
                                                            v-model="form_data.new_request_procedure"
                                                            :maxlength="40"
                                                        />
                                                    </label>
                                                    <label class="form-label d-flex">
                                                        <div>ICD 10:</div>
                                                        <input
                                                            class="input"
                                                            v-model="form_data.new_request_icd"
                                                            :maxlength="20"
                                                        />
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="input-container col-md-5" style="padding: 0">
                                        <div style="padding: 0 15px">
                                            <div class="group-title">
                                                ADDITIONAL REQUESTS WITH CURRENT PROVIDER:
                                            </div>
                                            <div class="radio-group d-flex flex-column">
                                                <label
                                                    class="form-check-label"
                                                    v-for="option in additionalRequestOptions"
                                                    :key="option.key"
                                                >
                                                    <input
                                                        type="radio"
                                                        class="form-check-input"
                                                        name="additional-request"
                                                        :checked="form_data[option.key]"
                                                        @change="
                                                            changeRadioOption(
                                                                option.key,
                                                                'additionalRequestOptions',
                                                            )
                                                        " />
                                                    {{ option.title }}
                                                </label>
                                            </div>
                                        </div>
                                        <hr style="margin: 0" />
                                        <div style="padding: 0 15px">
                                            <div class="group-title">
                                                SERVICE TYPE (Select one):
                                            </div>
                                            <div class="radio-group d-flex flex-column">
                                                <label
                                                    class="form-check-label"
                                                    v-for="option in serviceTypeOptions"
                                                    :key="option.key"
                                                >
                                                    <input
                                                        type="radio"
                                                        class="form-check-input"
                                                        name="service-type"
                                                        :checked="form_data[option.key]"
                                                        @change="
                                                            changeRadioOption(
                                                                option.key,
                                                                'serviceTypeOptions',
                                                            )
                                                        " />
                                                    {{ option.title }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                        name="additional_information"
                                        label="ADDITIONAL INFORMATION:"
                                        size="col-md-12"
                                        rows="5"
                                        :maxlength="600"
                                        v-model="form_data.additional_information"
                                    ></document-textarea>
                                </div>

                                <div
                                    class="row input-container preferred-location-service">
                                    <div class="col-md-12">
                                        <div class="group-title">
                                            VETERAN PREFERRED LOCATION OF SERVICE (Location Name):
                                        </div>
                                        <div class="d-flex">
                                            <label class="checkbox-inline">
                                                <input
                                                    type="checkbox"
                                                    class="checkbox-form-control"
                                                    v-model="form_data.preferred_location_va_facility_cb"
                                                />
                                                VA FACILITY
                                            </label>
                                            <input
                                                class="input"
                                                :maxlength="55"
                                                v-model="form_data.preferred_location_va_facility"
                                            />
                                        </div>
                                        <div class="d-flex">
                                            <label class="checkbox-inline">
                                                <input
                                                    type="checkbox"
                                                    class="checkbox-form-control"
                                                    v-model="form_data.preferred_location_community_facility_cb"
                                                />
                                                COMMUNITY FACILITY
                                            </label>
                                            <input
                                                class="input"
                                                :maxlength="55"
                                                v-model="form_data.preferred_location_community_facility"
                                            />
                                        </div>
                                        <div class="d-flex">
                                            <label class="checkbox-inline">
                                                <input
                                                    type="checkbox"
                                                    class="checkbox-form-control"
                                                    v-model="form_data.preferred_location_community_provider_cb"
                                                />
                                                COMMUNITY PROVIDER
                                            </label>
                                            <input
                                                class="input"
                                                :maxlength="55"
                                                v-model="form_data.preferred_location_community_provider"
                                            />
                                        </div>
                                        <div class="d-flex">
                                            <label class="checkbox-inline">
                                                <input
                                                    type="checkbox"
                                                    class="checkbox-form-control"
                                                    v-model="form_data.preferred_location_no_preference_cb"
                                                />
                                                NO PREFERENCE
                                            </label>
                                            <input
                                                class="input"
                                                :maxlength="55"
                                                v-model="form_data.preferred_location_no_preference"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="row input-container">
                                    <div class="col-md-12">
                                        <label>*ATTESTATION:</label>
                                        <div>
                                            I do hereby attest that the forgoing
                                            information is true, accurate, and
                                            complete to the best of my knowledge
                                            and I understand that any
                                            falsification, omission, or
                                            concealment of material fact may
                                            subject me to administrative, civil,
                                            or criminal liability. I do hereby
                                            acknowledge that VA reserves the
                                            right to perform the requested
                                            service(s) if the following criteria
                                            are met: (1) The patient agrees to
                                            receive services from VA (2)
                                            Service(s) are available at VA
                                            facility and are able to be provided
                                            by the clinically indicated date (3)
                                            It is determined to be within the
                                            patients best interest. Upon
                                            completion of the requested
                                            service(s), VA will provide all
                                            resulting medical documentation to
                                            the ordering provider. If all
                                            criteria listed are not true and VA
                                            agrees the service(s) are clinically
                                            indicated, VA will provide a
                                            referral for services to be
                                            performed in the community. I do
                                            hereby attest that upon receipt of
                                            order/consult results, I will assume
                                            responsibility for reviewing said
                                            results, addressing significant
                                            findings, and providing continued
                                            care.
                                        </div>
                                    </div>
                                </div>

                                <br />
                                <div class="row text-center">
                                    <label>RECOMMENDED PROGRESS REPORT AND TREATMENT PLAN</label>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                        name="description_specific_treatment_targets"
                                        :label="`BRIEFLY DESCRIBE SPECIFIC TREATMENT TARGETS ${requiredIndicator}`"
                                        validateRules="required"
                                        size="col-md-12"
                                        rows="5"
                                        v-model="form_data.description_specific_treatment_targets"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                        name="description_patient_progress"
                                        :label="`DESCRIBE PATIENT PROGRESS SINCE THE INITIATION OF THERAPY COURSE AND SINCE THE LAST AUTHORIZATION <br />PLEASE USE BEHAVIORAL DESCRIPTIONS ${requiredIndicator}`"
                                        validateRules="required"
                                        size="col-md-12"
                                        rows="5"
                                        v-model="form_data.description_patient_progress"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                        name="specific_treatment_plans_list"
                                        :label="`PLEASE LIST SPECIFIC EVIDENCE-BASED TREATMENT PLANS THAT ARE BEING USED ${requiredIndicator}`"
                                        validateRules="required"
                                        size="col-md-12"
                                        rows="5"
                                        v-model="form_data.specific_treatment_plans_list"
                                    ></document-textarea>
                                </div>

                                <div class="row input-row">
                                    <document-textarea
                                        name="anticipated_duration_of_therapy_plans_and_discharge"
                                        :label="`PLEASE SHARE ANTICIPATED DURATION OF THERAPY, PLANS AND DISCHARGE, AND RECOMMENDATIONS FOR AFTERCARE ${requiredIndicator}`"
                                        validateRules="required"
                                        size="col-md-12"
                                        rows="5"
                                        v-model="form_data.anticipated_duration_of_therapy_plans_and_discharge"
                                    ></document-textarea>
                                </div>

                                <div class="row">
                                    <div
                                        id="date"
                                        class="form-group col-md-12 input-container document-date"
                                    >
                                        <label
                                            class="control-label input-label"
                                            v-html="`DATE ${requiredIndicator} (mm/dd/yyyy)`"
                                        ></label>
                                        <el-date-picker
                                            v-model="form_data.date"
                                            name="date"
                                            :editable="false"
                                            :format="datePickerFormat"
                                            :value-format="datePickerValueFormat"
                                            :disabled="statuses.editingDisabled"
                                            :picker-options="datePickerBirthDateOptions"
                                            type="date"
                                            @focus="pickerFocus('date')"
                                            @blur="pickerBlur('date')"
                                        ></el-date-picker>
                                    </div>
                                </div>

                                <div class="form-note-button-block">
                                    <div class="row">
                                        <div
                                            class="col-lg-12 text-center"
                                            style="padding-right: 0; margin-bottom: 15px;"
                                        >
                                            <span
                                                v-if="statuses.noErrors === false && !validation_message"
                                                class="text-red validation-error-msg"
                                            >
                                                Please make sure you have filled all the required fields.
                                            </span>
                                            <span
                                                v-if="validation_message"
                                                class="text-red validation-error-msg"
                                            >
                                                {{ validation_message }}
                                            </span>
                                        </div>

                                        <div class="col-lg-12 text-right" style="padding-right: 0">
                                            <div class="col-lg-12" style="padding-right: 0">
                                                <button
                                                    v-if="!statuses.editingDisabled"
                                                    type="submit"
                                                    class="btn btn-primary document-button"
                                                    @click.prevent="saveDocument"
                                                >
                                                    Save
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-default document-button"
                                                    @click.prevent="closeDocument"
                                                >
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!--/.modal-content-->
            </div>
        </div>
    </div>
</template>

<script>
import validate from "../../../mixins/validate";
import save from "../../../mixins/save-document-if-valid";
import methods from "../../../mixins/document-methods";
import dateOfService from "../../../mixins/date-of-service";
import DiagnosesMultiselect from "../../../mixins/diagnoses-multiselect";

export default {
    mixins: [validate, save, methods, dateOfService, DiagnosesMultiselect],
    data() {
        return {
            document_name: "va_request_for_reauthorization",
            document_title: "Request for Reauthorization - PGBA TriWest VA CCN",
            statuses: {
                noErrors: true,
            },
            validation_message: null,
            requiredIndicator: '<b style="color: #F56C6C">*</b>',
            newRequestOptions: [
                {
                    title: "PRIMARY CARE",
                    key: "new_request_primary_care_cb",
                },
                {
                    title: "SPECIALTY CARE",
                    key: "new_request_specialty_care_cb",
                },
                {
                    title: "MENTAL HEALTH",
                    key: "new_request_mental_health_cb",
                },
                {
                    title: "DURABLE MEDICAL EQUIPMENT (DME)",
                    key: "new_request_durable_medical_equipment_cb",
                },
                {
                    title: "LABORATORY/RADIOLOGY",
                    key: "new_request_laboratory_cb",
                },
            ],
            additionalRequestOptions: [
                {
                    title: "ADDITIONAL TIME WITH CURRENT PROVIDER",
                    key: "additional_time_with_current_provider_cb",
                },
                {
                    title: "ADDITIONAL VISITS WITH CURRENT PROVIDER",
                    key: "additional_visits_with_current_provider_cb",
                },
            ],
            serviceTypeOptions: [
                {
                    title: "DIAGNOSTIC TEST",
                    key: "service_type_diagnostic_test_cb",
                },
                {
                    title: "RADIOLOGY",
                    key: "service_type_radiology_cb",
                },
                {
                    title: "VISITS",
                    key: "service_type_visits_cb",
                },
            ],
        };
    },
    beforeMount() {
        if (!this.form_data && this.$store.state.currentDocument) {
            const currentPatient = this.$store.state.currentPatient;
            const currentProvider = this.$store.state.currentProvider;

            this.patient_id = currentPatient.id;
            this.form_data = {
                va_facility_name: "",
                va_facility_location: "",
                va_authorization_number: currentPatient.auth_number,
                today_date: this.formatDate(new Date(), this.momentDateFormat),
                veteran_name: this.getPatientFullName(currentPatient),
                birth_date: currentPatient.date_of_birth,
                ordering_provider_name: currentProvider.provider_name,
                ordering_provider_npi: currentProvider.individual_npi,
                ordering_provider_contact_number: "",
                ordering_provider_office_phone: "",
                ordering_provider_fax_number: "",
                ordering_provider_secure_email: currentProvider.user ? currentProvider.user.email : '',
                new_request_primary_care_cb: false,
                new_request_specialty_care_cb: false,
                new_request_mental_health_cb: false,
                new_request_durable_medical_equipment_cb: false,
                new_request_laboratory_cb: false,
                new_request_procedure: "",
                new_request_icd: "",
                additional_time_with_current_provider_cb: false,
                additional_visits_with_current_provider_cb: false,
                service_type_diagnostic_test_cb: false,
                service_type_radiology_cb: false,
                service_type_visits_cb: false,
                additional_information: "",
                preferred_location_va_facility_cb: false,
                preferred_location_va_facility: "",
                preferred_location_community_facility_cb: false,
                preferred_location_community_facility: "",
                preferred_location_community_provider_cb: false,
                preferred_location_community_provider: "",
                preferred_location_no_preference_cb: false,
                preferred_location_no_preference: "",
                description_specific_treatment_targets: "",
                description_patient_progress: "",
                specific_treatment_plans_list: "",
                anticipated_duration_of_therapy_plans_and_discharge: "",
                date: this.formatDate(new Date(), this.momentDateFormat),
            };
        }
    },
    mounted() {
        let self = this;
        let menu_item_selector = "va-request-for-reauthorization";
        let document_name = self.getFormName(menu_item_selector);
        self.document_name = document_name;
        self.document_title = self.getFormTitle(menu_item_selector);

        window.setTimeout(() => {
            $("#" + this.document_name)
                .on("shown.bs.modal", function () {
                    $("body").addClass("custom-modal");

                    autosize($("#" + document_name).find("textarea"));

                    $(".input-container").on("click", function () {
                        $(this).find(".input-element").focus();
                    });

                    $("#" + document_name)
                        .find("input.el-input__inner")
                        .addClass("input-element");

                    self.initDateOfService();
                })
                .on("hidden.bs.modal", function () {
                    $("body").removeClass("custom-modal");
                });
        }, 500);
    },

    watch: {
        form_data: {
            handler() {
                if (!this.validateNewRequest()) {
                    $("#new-request").removeClass("error-focus");
                }
            },
            deep: true,
        },
    },

    methods: {
        changeRadioOption(key, options) {
            if (this.form_data[key]) {
                return;
            }
            this.form_data[key] = true;
            this[options]
                .filter((option) => option.key !== key)
                .forEach((option) => (this.form_data[option.key] = false));
        },

        validateBirthDate() {
            if (!this.form_data.birth_date) {
                $("#birth-date").addClass("error-focus");
                return true;
            }
            return false;
        },

        validateDate() {
            if (!this.form_data.date) {
                $("#date").addClass("error-focus");
                return true;
            }
            return false;
        },

        validateNewRequest() {
            const hasSelectedOption = this.newRequestOptions.some(
                (option) => this.form_data[option.key],
            );

            if (
                !hasSelectedOption ||
                !this.form_data.new_request_procedure ||
                !this.form_data.new_request_icd
            ) {
                return true;
            }

            return false;
        },

        validateRequiredFields() {
            let error = false;

            $(".input-container input[type=text], .input-container textarea").each((i, element) => {
                if (
                    $(element).attr("aria-required") === "true" &&
                    !$(element).val().trim()
                ) {
                    $(element).parents(".input-container").removeClass("focus");
                    $(element)
                        .parents(".input-container")
                        .addClass("error-focus");
                    error = true;
                } else {
                    $(element)
                        .parents(".input-container")
                        .removeClass("error-focus");
                }
            });

            return error;
        },

        getCustomValidation() {
            let error = false;

            if (this.validateRequiredFields()) {
                error = true;
            }

            if (this.validateBirthDate()) {
                error = true;
            }

            if (this.validateNewRequest()) {
                $("#new-request").addClass("error-focus");
                error = true;
            }

            if (this.validateDate()) {
                error = true;
            }

            if (error) {
                this.statuses.noErrors = false;
            }

            return error;
        },

        getPatientFullName(patient) {
            if (!patient) {
                return '';
            }

            let fullName = `${patient.last_name}, ${patient.first_name}`;
            if (patient.middle_initial) {
                fullName += `, ${patient.middle_initial}`;
            }

            return fullName;
        },
    },
};
</script>

<style lang="scss" scoped>
hr {
    height: 1px;
    background-color: rgb(201, 201, 201);
}

.radio-group {
    .form-check-label {
        font-weight: normal;
    }
}

.group-title {
    color: rgb(153, 153, 153);
    margin-bottom: 10px;
}

.new-request-text-block {
    position: absolute;
    right: 0;
    display: flex;
    flex-direction: column;

    .form-label {
        font-weight: normal;

        div {
            width: 85px;
            text-align: right;
            margin-right: 5px;
        }

        input {
            width: 200px;
        }

        textarea {
            width: 200px;
        }
    }
}

.preferred-location-service {
    padding-bottom: 10px;

    label {
        width: 190px;
        flex-shrink: 0;
    }
}

.input {
    border: none;
    border-bottom: 1px solid #c9c9c9;
    width: 100%;
}

.input:focus {
    border: none;
    border-bottom: 2px solid #4ac0fb;
    outline: none;
}

.textarea {
    border-radius: 3px;
    border: 1px solid #c9c9c9;
    padding: 5px;
    width: 100%;
    max-height: 50px;
    overflow: auto;
}

.textarea:focus {
    border: 2px solid #4ac0fb;
    outline: none;
}
</style>
