<template>
    <div class="patient-dialog-wrapper">
        <el-dialog
                :title="dialogTitle"
                :visible.sync="showDialog"
                :close-on-click-modal="false"
                v-loading.fullscreen.lock="isLoading"
                class="patient-dialog bootstrap-modal">
            <el-form :rules="formRule" ref="patientForm" :model="formData">
                <div class="form-group">
                    <div class="form-group__title">Patient Data</div>
                    <div class="form-row">
                        <div class="form-col form-col-4">
                            <el-form-item label="First name" prop="first_name">
                                <el-input v-model="formData.first_name"></el-input>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Last name" prop="last_name">
                                <el-input v-model="formData.last_name"></el-input>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Middle initial" prop="middle_initial">
                                <el-input v-model="formData.middle_initial"></el-input>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-4">
                            <el-form-item label="Date of Birth" prop="date_of_birth">
                                <el-date-picker
                                        :picker-options="birthdayPickerOptions"
                                        v-model="formData.date_of_birth"
                                        format="MM/dd/yyyy"
                                        value-format="yyyy-MM-dd"
                                        type="date">
                                </el-date-picker>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Sex" prop="sex">
                                <el-select v-model="formData.sex" placeholder="">
                                    <el-option
                                            v-for="item in sexList"
                                            :key="item.id"
                                            :label="item.text"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Preferred Language" prop="preferred_language_id">
                                <el-select v-if="preferredLanguageList.length" class="form-field form-field-select" v-model="formData.preferred_language_id"
                                           placeholder="" filterable :clearable="true">
                                    <el-option
                                            v-for="item in preferredLanguageList"
                                            :key="item.id"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-12">
                            <el-form-item label="Primary Care Provider" prop="provider_id">
                                <el-select
                                        v-model="formData.provider_id"
                                        :filter-method="filterProviderHandler"
                                        v-el-select-lazy:providersData.getProviderListForAppointments="loadMoreList"
                                        filterable
                                        placeholder="">
                                    <el-option
                                            v-for="item in providersList"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="item.provider_name">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-4">
                            <el-form-item label="Email" prop="email">
                                <el-input v-model="formData.email" type="email"></el-input>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Add Email" prop="secondary_email">
                                <el-input v-model="formData.secondary_email" type="email"></el-input>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Cell phone" prop="cell_phone">
                                <el-input
                                        type="tel"
                                        v-model="formData.cell_phone"
                                        v-mask="'(###)-###-####'"
                                        :masked="true"/>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-4">
                            <el-form-item label="Home phone" prop="home_phone">
                                <el-input
                                        type="tel"
                                        v-model="formData.home_phone"
                                        v-mask="'(###)-###-####'"
                                        :masked="true"/>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Work phone" prop="work_phone">
                                <el-input
                                        type="tel"
                                        v-model="formData.work_phone"
                                        v-mask="'(###)-###-####'"
                                        :masked="true"/>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Preferred phone" prop="preferred_phone">
                                <el-select v-model="formData.preferred_phone" placeholder="">
                                    <el-option
                                            v-for="item in preferredPhoneList"
                                            :key="item.id"
                                            :label="item.text"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-6">
                            <el-form-item label="Address line 1" prop="address">
                                <el-input v-model="formData.address"></el-input>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-6">
                            <el-form-item label="Address line 2" prop="address_2">
                                <el-input v-model="formData.address_2"></el-input>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-4">
                            <el-form-item label="City" prop="city">
                                <el-input v-model="formData.city"></el-input>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="State" prop="state">
                                <el-select v-model="formData.state" filterable placeholder="">
                                    <el-option
                                            v-for="(item, index) in stateList"
                                            :key="index"
                                            :label="item"
                                            :value="item">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Zip" prop="zip">
                                <el-input v-model="formData.zip"></el-input>
                            </el-form-item>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col form-col-12">
                            <el-form-item label="Therapy Type" prop="therapy_type_id">
                                <el-select v-model="formData.therapy_type_id" placeholder="">
                                    <el-option
                                        v-for="type in therapyTypeList"
                                        :key="type.id"
                                        :value="type.id"
                                        :label="type.name"
                                    >
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-group__title">Insurance</div>
                    <div class="form-row">
                        <div class="form-col form-col-6">
                            <el-form-item label="Insurance Co" prop="insurance_id">
                                <el-select
                                        v-model="formData.insurance_id"
                                        :filter-method="filterInsuranceHandler"
                                        v-el-select-lazy:insuranceData.getInsuranceList="loadMoreList"
                                        filterable
                                        placeholder="">
                                    <el-option
                                            style="height:40px;line-height:normal;"
                                            v-for="item in insuranceList"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="item.insurance">
                                        <span>{{ item.insurance }}</span>
                                        <span style="font-size:11px;color:#8492a6;display:block;line-height:normal;">{{ getFormattedAddress(item) }}</span>
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-6">
                            <el-form-item label="Subscriber ID" prop="subscriber_id">
                                <el-input v-model="formData.subscriber_id"></el-input>
                            </el-form-item>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col form-col-12">
                            <el-checkbox
                                v-model="formData.is_self_pay"
                                @change="isSelfPayChange"
                                style="margin-bottom: 15px"
                            >
                                Pays out-of-pocket
                            </el-checkbox>
                        </div>
                    </div>
                    <template v-if="formData.is_self_pay">
                        <div class=form-row>
                            <div class="form-col form-col-4">
                                <el-form-item label="Self-Pay" prop="self_pay">
                                    <input
                                        type="number"
                                        v-model="formData.self_pay"
                                        min="0"
                                        step="0.01"
                                        class="input-number form-control"
                                        :controls="false"
                                        @blur="handleBlur('self_pay')"
                                        @wheel="handleWheel"
                                    />
                                </el-form-item>
                            </div>
                            <div class="form-col form-col-4"></div>
                            <div class="form-col form-col-4"></div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="form-row">
                            <div class="form-col form-col-4">
                                <el-form-item label="Co-Pay" prop="visit_copay">
                                    <input
                                        type="number"
                                        v-model="formData.visit_copay"
                                        min="0"
                                        step="0.01"
                                        class="input-number form-control"
                                        :disabled="true"
                                        :controls="false"
                                        @blur="handleBlur('visit_copay')"
                                        @wheel="handleWheel"
                                    />
                                </el-form-item>
                            </div>
                            <div class="form-col form-col-4">
                                <el-form-item label="Insurance Pay" prop="insurance_pay">
                                    <input
                                        type="number"
                                        v-model="formData.insurance_pay"
                                        min="0"
                                        step="0.01"
                                        class="input-number form-control"
                                        :disabled="true"
                                        :controls="false"
                                        @blur="handleBlur('insurance_pay')"
                                        @wheel="handleWheel"
                                    />
                                </el-form-item>
                            </div>
                            <div class="form-col form-col-4"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-col form-col-4">
                                <el-form-item label="Deductible" prop="deductible">
                                    <input
                                        type="number"
                                        v-model="formData.deductible"
                                        min="0"
                                        step="0.01"
                                        class="input-number form-control"
                                        :disabled="true"
                                        :controls="false"
                                        @blur="handleBlur('deductible')"
                                        @wheel="handleWheel"
                                    />
                                </el-form-item>
                            </div>
                            <div class="form-col form-col-4">
                                <el-form-item label="Deductible Met" prop="deductible_met">
                                    <input
                                        type="number"
                                        v-model="formData.deductible_met"
                                        min="0"
                                        step="0.01"
                                        class="input-number form-control"
                                        :disabled="true"
                                        :controls="false"
                                        @blur="handleBlur('deductible_met')"
                                        @wheel="handleWheel"
                                    />
                                </el-form-item>
                            </div>
                            <div class="form-col form-col-4">
                                <el-form-item label="Remaining Deductible" prop="deductible_remaining">
                                    <input
                                        type="number"
                                        v-model="formData.deductible_remaining"
                                        min="0"
                                        step="0.01"
                                        class="input-number form-control"
                                        :disabled="true"
                                        :controls="false"
                                        @blur="handleBlur('deductible_remaining')"
                                        @wheel="handleWheel"
                                    />
                                </el-form-item>
                            </div>
                        </div>
                    </template>
                    <div class="form-row">
                        <div class="form-col form-col-4">
                            <el-form-item label="Health Plan Elig. Benefit Co. ID" prop="eligibility_payer_id">
                                <el-select
                                        v-model="formData.eligibility_payer_id"
                                        :filter-method="filterPayersHandler"
                                        v-el-select-lazy:payersData.getPayersList="loadMoreList"
                                        filterable
                                        placeholder="">
                                    <el-option
                                            v-for="item in payersList"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="item.name">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Plan Name" prop="plan_name">
                                <el-input v-model="formData.plan_name"></el-input>
                            </el-form-item>
                        </div>

                        <div class="form-col form-col-4">
                            <el-form-item label="Authorization Number" prop="auth_number">
                                <el-input v-model="formData.auth_number"></el-input>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="No. of Visits Authorized" prop="visits_auth">
                                <el-input-number 
                                    v-model="formData.visits_auth" 
                                    :min="0"
                                    :step="1"
                                    :controls="false"
                                    @change="inputVisitsAuth"
                                    class="form-field form-field-number"
                                />
                            </el-form-item>
                        </div> 

                        <div class="form-col form-col-4">
                            <el-form-item label="Eff. Start Date" prop="eff_start_date">
                                <el-date-picker
                                        v-model="formData.eff_start_date"
                                        format="MM/dd/yyyy"
                                        value-format="yyyy-MM-dd"
                                        type="date">
                                </el-date-picker>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-4">
                            <el-form-item label="Eff. Stop Date" prop="eff_stop_date">
                                <el-date-picker
                                        v-model="formData.eff_stop_date"
                                        format="MM/dd/yyyy"
                                        value-format="yyyy-MM-dd"
                                        type="date">
                                </el-date-picker>
                            </el-form-item>
                        </div>
                        <div class="form-col form-col-12">
                            <el-checkbox
                                v-model="formData.is_payment_forbidden"
                                :disabled="formData.is_self_pay"
                                @change="paymentForbiddenChange"
                            >
                                Payment Forbidden
                            </el-checkbox>
                        </div>
                    </div>
                </div>
                <div class="form-group form-group--diagnosis">
                    <div class="form-group__title">Diagnosis Codes</div>
                    <div class="form-row">
                        <div class="form-col form-col-12">
                            <el-form-item label="ICD Codes" prop="diagnoses">
                                <diagnoses-multiselect
                                        id="diagnoseMultipleSelect"
                                        v-if="formData.diagnoses"
                                        :maxLength="12"
                                        :selectedDiagnoses="formData.diagnoses"
                                        customClass="multiselect-blue diagnoses-multiselect document-diagnoses-multiselect"
                                        @setDiagnoses="setDiagnoses"
                                ></diagnoses-multiselect>
                            </el-form-item>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-group__title">Template Billable Line Items</div>
                    <div class="form-row form-row--table">
                        <div class="form-col form-col-12">
                            <el-table
                                    class="table-templates"
                                    :border="true"
                                    :data="formData.templates"
                                    style="width: 100%">
                                <el-table-column
                                        prop="pos"
                                        label="POS"
                                        header-align="center"
                                        width="150">
                                    <template slot-scope="scope">
                                        <el-form-item prop="pos">
                                            <el-input
                                                    v-model="formData.templates[scope.$index].pos"
                                                    :controls="false"
                                                    v-mask="'##########'"
                                                    masked="true"
                                                    class="form-field form-field-number">
                                            </el-input>
                                        </el-form-item>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="cpt"
                                        label="CPT"
                                        header-align="center"
                                        width="150">
                                    <template slot-scope="scope">
                                        <el-form-item prop="cpt">
                                            <el-select v-model="formData.templates[scope.$index].cpt"
                                                       @change="changeCPT(formData.templates[scope.$index])"
                                                       filterable
                                                       placeholder="">
                                                <el-option
                                                        v-for="item in cptOptionList"
                                                        :key="item.id"
                                                        :label="item.code"
                                                        :value="item.code">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Modifier" header-align="center" width="250">
                                    <el-table-column
                                            prop="modifier_a"
                                            label="A"
                                            header-align="center">
                                        <template slot-scope="scope">
                                            <div class="modifier-form-row">
                                                <el-form-item prop="modifier_a">
                                                    <el-input
                                                            v-model="formData.templates[scope.$index].modifier_a"
                                                            @input="inputTemplateField(formData.templates[scope.$index], 'isUserChangesModifierA')"
                                                            :controls="false"
                                                            v-mask="'XX'"
                                                            masked="true"
                                                            class="form-field form-field-number">
                                                    </el-input>
                                                </el-form-item>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                            prop="modifier_b"
                                            label="B"
                                            header-align="center">
                                        <template slot-scope="scope">
                                            <div class="modifier-form-row">
                                                <el-form-item prop="modifier_b">
                                                    <el-input
                                                            v-model="formData.templates[scope.$index].modifier_b"
                                                            :controls="false"
                                                            v-mask="'##'"
                                                            masked="true"
                                                            class="form-field form-field-number">
                                                    </el-input>
                                                </el-form-item>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                            prop="modifier_c"
                                            label="C"
                                            header-align="center">
                                        <template slot-scope="scope">
                                            <div class="modifier-form-row">
                                                <el-form-item prop="modifier_c">
                                                    <el-input
                                                            v-model="formData.templates[scope.$index].modifier_c"
                                                            :controls="false"
                                                            v-mask="'##'"
                                                            masked="true"
                                                            class="form-field form-field-number">
                                                    </el-input>
                                                </el-form-item>
                                            </div>
                                        </template>
                                    </el-table-column>
                                    <el-table-column
                                            prop="modifier_d"
                                            label="D"
                                            header-align="center">
                                        <template slot-scope="scope">
                                            <div class="modifier-form-row">
                                                <el-form-item prop="modifier_d">
                                                    <el-input
                                                            v-model="formData.templates[scope.$index].modifier_d"
                                                            :controls="false"
                                                            v-mask="'##'"
                                                            masked="true"
                                                            class="form-field form-field-number">
                                                    </el-input>
                                                </el-form-item>
                                            </div>
                                        </template>
                                    </el-table-column>
                                </el-table-column>
                                <el-table-column
                                        prop="diagnose_pointer"
                                        label="Diag. pointer"
                                        header-align="center"
                                        width="120">
                                    <template slot-scope="scope">
                                        <el-form-item prop="diagnose_pointer"
                                                      :class="{'is-error': formData.templates[scope.$index].isDiagnosePointerError}">
                                            <el-input v-model="formData.templates[scope.$index].diagnose_pointer"
                                                      @input="inputDiagnosePointer(formData.templates[scope.$index])"></el-input>
                                        </el-form-item>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="charge"
                                        label="Line Charges"
                                        header-align="center"
                                        width="150">
                                    <template slot-scope="scope">
                                        <el-form-item prop="charge">
                                            <el-input-number
                                                    v-model="formData.templates[scope.$index].charge"
                                                    @change="inputTemplateField(formData.templates[scope.$index], 'isUserChangesCharge')"
                                                    :min="0"
                                                    :step="1"
                                                    :precision="2"
                                                    :controls="false"
                                                    class="form-field form-field-number">
                                            </el-input-number>
                                        </el-form-item>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        prop="days_or_units"
                                        label="Days or Units"
                                        header-align="center"
                                        width="150">
                                    <template slot-scope="scope">
                                        <el-form-item prop="days_or_units">
                                            <el-input-number
                                                    v-model="formData.templates[scope.$index].days_or_units"
                                                    :min="1"
                                                    :controls="false"
                                                    class="form-field form-field-number">
                                            </el-input-number>
                                        </el-form-item>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        fixed="right"
                                        label="Remove"
                                        header-align="center"
                                        width="100">
                                    <template slot-scope="scope">
                                        <div class="column-remove-line">
                                            <el-button type="danger" :disabled="isRemoveRowDisabled"
                                                       @click.prevent="removeTemplateRow(scope.$index)" plain
                                                       icon="el-icon-delete" circle></el-button>
                                        </div>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div class="table-templates-error" v-if="Boolean(tableErrorMessage)">
                                {{tableErrorMessage}}
                            </div>
                            <div class="added-button-wrapper">
                                <el-button type="primary" @click.prevent="addedTemplateRow" icon="el-icon-plus" plain
                                           circle></el-button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row" v-if="isCreated">
                    <div class="form-col form-col-12">
                        <el-checkbox v-model="scheduleAppointment">Schedule Appointment</el-checkbox>
                    </div>
                </div>
                <div class="form-footer">
                    <div class="form-footer-control">
                        <el-button type="primary" @click="sendForm">{{textButtonSubmit}}</el-button>
                        <el-button @click="closeDialog">Cancel</el-button>
                    </div>
                </div>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    const defaultTemplatesRow = {
        pos: undefined,
        cpt: '',
        modifier_a: undefined,
        modifier_b: undefined,
        modifier_c: undefined,
        modifier_d: undefined,
        diagnose_pointer: '',
        isUserChangesDiagnosePointer: false,
        isUserChangesModifierA: false,
        isUserChangesCharge: false,
        isDiagnosePointerError: false,
        charge: undefined,
        days_or_units: undefined,
        patient_insurances_procedure_id: undefined,
    };

    import {Notification} from "element-ui";
    import debounce from "../helpers/debounce";
    import {isMoneyRoundString, parseMoney} from "../helpers/parseMoney";
    import InsuranceInput from "../mixins/insurance-input";

    export default {
        name: 'ManagementPatientDialog',
        props: {
            isShowDialog: {
                type: Boolean,
                default: false,
            },
            isCreated: {
                type: Boolean,
                default: false
            },
            patientId: {
                type: Number || null,
                default: null
            }
        },
        mixins: [InsuranceInput],
        data() {
            return {
                isLoading: false,
                formData: {
                    first_name: '', 
                    last_name: '',
                    middle_initial: '',
                    date_of_birth: '',
                    sex: '',
                    preferred_language_id: '',
                    email: '',
                    secondary_email: '',
                    cell_phone: '',
                    home_phone: '',
                    work_phone: '',
                    preferred_phone: '',
                    address: '',
                    address_2: '',
                    city: '',
                    state: '',
                    zip: '',
                    provider_id: '',
                    insurance_id: '',
                    subscriber_id: '',
                    is_self_pay: false,
                    self_pay: 0,
                    visit_copay: 0,
                    deductible: 0,
                    deductible_met: 0,
                    deductible_remaining: 0,
                    insurance_pay: 0,
                    therapy_type_id: '',
                    eligibility_payer_id: '',
                    plan_name: '',
                    auth_number: '',
                    visits_auth: 0,
                    visits_auth_left: 0,
                    eff_start_date: '',
                    eff_stop_date: '',
                    diagnoses: [],
                    templates: [_.cloneDeep(defaultTemplatesRow)],
                    therapist_manage_timesheet: false,
                    is_payment_forbidden: false
                },
                formRule: {
                    first_name: [
                        {required: true, message: 'The first name field is required', trigger: 'change'},
                    ],
                    last_name: [
                        {required: true, message: 'The last name field is required', trigger: 'change'},
                    ],
                    sex: [
                        {required: true, message: 'The sex is required', trigger: 'change'},
                    ],
                    email: [
                        {type: 'email', message: 'Invalid email address', trigger: 'blur'},
                    ],
                    secondary_email: [
                        {type: 'email', message: 'Invalid additional email address', trigger: 'blur'}
                    ],
                    cell_phone: [{
                        len: 14,
                        message: 'The phone field must be at least 14 characters',
                        trigger: 'blur'
                    }],
                    home_phone: [{
                        len: 14,
                        message: 'The phone field must be at least 14 characters',
                        trigger: 'blur'
                    }],
                    work_phone: [{
                        len: 14,
                        message: 'The phone field must be at least 14 characters',
                        trigger: 'blur'
                    }],
                    subscriber_id: [
                        {max: 50, message: 'The subscriber id may not be greater than 50 characters.', trigger: 'blur'},
                    ],
                },
                birthdayPickerOptions: {
                    disabledDate(time) {
                        return time.getTime() > Date.now();
                    },
                },
                scheduleAppointment: false,
                sexList: [
                    {id: 0, value: 'M', text: 'Male'},
                    {id: 1, value: 'F', text: 'Female'},
                    {id: 2, value: 'U', text: 'Unknown'},
                ],
                preferredPhoneList: [
                    {id: 0, value: 'C', text: 'Cell Phone'},
                    {id: 1, value: 'H', text: 'Home Phone'},
                    {id: 2, value: 'W', text: 'Work Phone'},
                    {id: 3, value: 'D', text: 'DO NOT CALL'},
                ],
                stateList: ["AA", "AE", "AP", "AK", "AL", "AR", "AS", "AZ", "CA", "CO", "CT", "DC", "DE", "FL", "FM", "GA", "GU", "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME", "MH", "MI", "MN", "MO", "MP", "MS", "MT", "NC", "ND", "NE", "NH", "NJ", "NM", "NV", "NY", "OH", "OK", "OR", "PA", "PR", "PW", "RI", "SC", "SD", "TN", "TX", "UT", "VA", "VI", "VT", "WA", "WI", "WV", "WY"],
                providersData: {
                    pageIndex: 1,
                    pageSize: 20,
                    lastPageIndex: 20,
                    list: [],
                },
                insuranceData: {
                    pageIndex: 1,
                    pageSize: 10,
                    lastPageIndex: 10,
                    list: [],
                },
                payersData: {
                    pageIndex: 1,
                    pageSize: 20,
                    lastPageIndex: 20,
                    list: [],
                },
                cptOptionList: [],
                isRemoveRowDisabled: false,
                tableErrorMessage: '',
                preferredLanguageList: [],
                therapyTypeList: []
            }
        },
        directives: {
            elSelectLazy: {
                bind(el, binding) {
                    const SELECTWRAP_DOM = el.querySelector('.el-select-dropdown .el-select-dropdown__wrap');
                    SELECTWRAP_DOM.addEventListener('scroll', function () {
                        const condition = this.scrollHeight - this.scrollTop <= this.clientHeight;
                        if (condition) {
                            binding.value(Object.keys(binding.modifiers)[0], binding.arg);
                        }
                    });
                }
            }
        },
        watch: {
            'formData.templates'(value) {
                this.disabledButtonRemoveRow(value);
                this.setDiagnosePointer();
                this.debounceValidationDiagnosePointer();
            },
            'formData.diagnoses'() {
                this.setDiagnosePointer();
                this.debounceValidationDiagnosePointer();
                this.reloadProviderList();
            }
        },
        computed: {
            showDialog: {
                get() {
                    return this.isShowDialog;
                },
                set(value) {
                    if (!value) {
                        this.$emit('closeDialog');
                    }
                }
            },
            dialogTitle() {
                return this.isCreated ? 'Create patient' : 'Update Patient';
            },
            textButtonSubmit() {
                return this.isCreated ? 'Create' : 'Update';
            },
            providersList() {
                let num = this.providersData.pageIndex * this.providersData.pageSize;
                return this.providersData.list.filter((ele, index) => {
                    return index < num;
                })
            },
            insuranceList() {
                let num = this.insuranceData.pageIndex * this.providersData.pageSize;
                return this.insuranceData.list.filter((ele, index) => {
                    return index < num;
                })
            },
            payersList() {
                let num = this.payersData.pageIndex * this.providersData.pageSize;
                return this.payersData.list.filter((ele, index) => {
                    return index < num;
                })
            },
            selectedDiagnosesId() {
                return this.formData.diagnoses.map(item => item.id);
            }
        },
        methods: {
            isSelfPayChange(val) {
                if (!val) {
                    return;
                }

                this.formData.is_payment_forbidden = false;
            },

            paymentForbiddenChange(val) {
                if (!val) {
                    return;
                }

                this.formData.self_pay = 0;
                this.formData.visit_copay = 0;
                this.formData.deductible = 0;
                this.formData.deductible_met = 0;
                this.formData.deductible_remaining = 0;
                this.formData.insurance_pay = 0;
            },
            getFormattedAddress(item) {
                let address = [];
                if (item.address_line_1) {
                    address.push(item.address_line_1);
                }
                if (item.city) {
                    address.push(item.city);
                }
                if (item.state) {
                    address.push(item.state);
                }
                if (item.zip) {
                    address.push(item.zip);
                }
                if (address.length) {
                    return address.join(', ');
                }

                return '-';
            },
            initProviderList() {
                this.$store.dispatch('getProviderListForAppointments', {
                    page: this.providersData.pageIndex,
                    limit: this.providersData.pageSize,
                    diagnoses: this.selectedDiagnosesId
                }).then(({data}) => {
                    this.providersData.list = data.providers.data;
                    this.providersData.lastPageIndex = data.providers.last_page;
                })
            },
            initInsuranceList() {
                this.$store.dispatch('getInsuranceList', {
                    page: this.insuranceData.pageIndex,
                    limit: this.insuranceData.pageSize
                }).then(({data}) => {
                    this.insuranceData.list = data.data;
                    this.insuranceData.lastPageIndex = data.last_page;
                })
            },
            initPayersList() {
                this.$store.dispatch('getPayersList', {
                    page: this.payersData.pageIndex,
                    limit: this.payersData.pageSize
                }).then(({data}) => {
                    this.payersData.list = data.data;
                    this.payersData.lastPageIndex = data.last_page;
                })
            },
            initCPTList() {
                this.$store.dispatch('getCPTList').then(({data}) => {
                    this.cptOptionList = data.insurance_procedures;
                })
            },
            initPreferredLanguageList() {
                this.$store.dispatch('getPatientPreferredLanguageList').then(({data}) => {
                    this.preferredLanguageList = data.languages;
                })
            },
            initTherapyTypeList() {
                this.$store.dispatch('getTherapyTypeList').then(({data}) => {
                    this.therapyTypeList = data.therapy_types;
                })
            },
            initSelectFieldsData() {
                this.initProviderList();
                this.initInsuranceList();
                this.initPayersList();
                this.initCPTList();
                this.initPreferredLanguageList();
                this.initTherapyTypeList();
            },
            initPatientSpecificFormData() {
                if (this.patientId) {
                    this.$nextTick(() => {
                        this.isLoading = true;
                        this.$store.dispatch('getPatientForEdit', this.patientId).then(({data}) => {
                            for (const patientKey in data.patient) {
                                if (this.formData.hasOwnProperty(patientKey) && data.patient[patientKey]) {
                                    let value = data.patient[patientKey];

                                    if (isMoneyRoundString(value)) {
                                        value = parseMoney(value);
                                    }

                                    this.formData[patientKey] = value
                                }
                                if (data.patient[patientKey]) {
                                    switch (patientKey) {
                                        case 'primary_provider':
                                            this.formData.provider_id = data.patient.primary_provider.id;
                                            this.providersData.list = [{
                                                id: data.patient.primary_provider.id,
                                                provider_name: data.patient.primary_provider.provider_name
                                            }];
                                            break;
                                        case 'insurance':
                                            this.formData.insurance_id = data.patient.insurance.id;
                                            this.insuranceData.list = [{
                                                id: data.patient.insurance.id,
                                                insurance: data.patient.insurance.insurance,
                                                city: data.patient.insurance.city,
                                                state: data.patient.insurance.state,
                                                zip: data.patient.insurance.zip,
                                                address_line_1: data.patient.insurance.address_line_1,
                                            }];
                                            break;
                                        case 'eligibility_payer':
                                            this.formData.eligibility_payer_id = data.patient.eligibility_payer.id;
                                            this.payersData.list = [{
                                                id: data.patient.eligibility_payer.id,
                                                name: data.patient.eligibility_payer.name
                                            }]
                                            break;
                                        case 'insurance_plan':
                                            this.formData.plan_name = data.patient.insurance_plan.name;
                                            break;
                                    }
                                }
                            }
                        }).finally(() => this.isLoading = false)
                    })
                }
            },
            updateLazyLoadList(dispatchName, dataName) {
                let payload = {
                    page: this[dataName].pageIndex,
                    limit: this[dataName].pageSize
                };
                if (dataName === 'providersData') {
                    payload.diagnoses = this.selectedDiagnosesId
                }
                this.$store.dispatch(dispatchName, payload).then(({data}) => {
                    let dataList = dataName === 'providersData' ? data.providers.data : data.data;
                    this[dataName].list = _.uniqWith(this[dataName].list.concat(dataList), _.isEqual);
                })
            },
            reloadProviderList() {
                this.providersData.pageIndex = 1;
                this.providersData.list = [];
                this.updateLazyLoadList('getProviderListForAppointments', 'providersData');
            },
            loadMoreList(dispatchName, dataName) {
                this[dataName].pageIndex++;
                if (this[dataName].pageIndex <= this[dataName].lastPageIndex) {
                    this.updateLazyLoadList(dispatchName, dataName);
                }
            },
            filterProviderHandler(query) {
                this.filterLazyLoadHandler(query, 'getProviderListForAppointments', 'providersData', 'initProviderList')
            },
            filterInsuranceHandler(query) {
                this.filterLazyLoadHandler(query, 'getInsuranceList', 'insuranceData', 'initInsuranceList')
            },
            filterPayersHandler(query) {
                this.filterLazyLoadHandler(query, 'getPayersList', 'payersData', 'initPayersList')
            },
            filterLazyLoadHandler(query, dispatchName, dataName, initFunctionName) {
                if (query !== '') {
                    let payload = {
                        limit: this[dataName].pageSize,
                        search_query: query
                    };
                    if (dataName === 'providersData') {
                        payload.diagnoses = this.selectedDiagnosesId
                    }
                    this.$store.dispatch(dispatchName, payload).then(({data}) => {
                        this[dataName].list = dataName === 'providersData' ? data.providers.data : data.data;
                    })
                } else {
                    this[dataName].pageIndex = 1;
                    this[initFunctionName]();
                }
            },
            setDiagnoses(diagnoses) {
                this.formData.diagnoses = diagnoses;
            },
            changeCPT(template) {
                let cptOption = this.cptOptionList.find(item => String(item.code) === String(template.cpt));
                template.patient_insurances_procedure_id = cptOption.id;
                if (!template.isUserChangesModifierA && (template.modifier_a === '' || template.modifier_a === null || template.modifier_a === undefined)) {
                    template.modifier_a = cptOption.modifier_a;
                }
                if (!template.isUserChangesCharge && (template.charge === '' || template.charge === null || template.charge === undefined)) {
                    template.charge = cptOption.charge === null ? undefined : cptOption.charge;
                }
            },
            inputTemplateField(template, fieldName) {
                template[fieldName] = true;
            },
            inputDiagnosePointer(template) {
                template.diagnose_pointer = template.diagnose_pointer.toUpperCase();
                template.isUserChangesDiagnosePointer = true;
                this.debounceValidationDiagnosePointer();
            },
            inputVisitsAuth(value) {
                this.formData.visits_auth_left = value;
            },
            setDiagnosePointer() {
                /**
                 * Updating the diagnose_pointer field depending on how many diagnoses were previously and whether the user changed this field manually
                 */
                this.formData.templates.forEach(item => {
                    if (!item.isUserChangesDiagnosePointer) {
                        switch (this.formData.diagnoses.length) {
                            case 0:
                                item.diagnose_pointer = '';
                                break;
                            case 1:
                                item.diagnose_pointer = 'A';
                                break;
                            case 2:
                                item.diagnose_pointer = 'AB';
                                break;
                            case 3:
                                item.diagnose_pointer = 'ABC';
                                break;
                            default:
                                item.diagnose_pointer = 'ABCD';
                                break;
                        }
                    }
                })
            },
            validationDiagnosePointer() {
                /**
                 * Checks if the field value is equal to 4 unique capital letters A-D
                 */
                let notValidField = [];
                this.formData.templates.forEach(item => {
                    if (Boolean(item.diagnose_pointer)) {
                        let modifyValue = item.diagnose_pointer.replace(/\s+/g, '').toUpperCase(),
                            conformity = modifyValue.match(/[A-D]{1,4}/g),
                            notUniqueChart = modifyValue.split('').filter((element, index, array) => array.indexOf(element) !== array.lastIndexOf(element));
                        if (conformity === null || conformity[0].length < modifyValue.length || notUniqueChart.length) {
                            item.isDiagnosePointerError = true;
                            this.tableErrorMessage = 'Diagnose Pointer must contain 4 unique characters A-D';
                            notValidField.push(item.diagnose_pointer);
                        } else {
                            item.isDiagnosePointerError = false;
                        }
                    } else {
                        item.isDiagnosePointerError = false;
                    }
                })
                if (!notValidField.length) {
                    this.tableErrorMessage = '';
                }
                return !notValidField.length;
            },
            debounceValidationDiagnosePointer: debounce(function () {
                this.validationDiagnosePointer()
            }, 200),
            addedTemplateRow() {
                this.formData.templates.push(_.cloneDeep(defaultTemplatesRow));
            },
            removeTemplateRow(index) {
                if (this.formData.templates.length > 1) {
                    this.$delete(this.formData.templates, index)
                }
            },
            disabledButtonRemoveRow(value) {
                this.isRemoveRowDisabled = value.length === 1;
            },
            handleErrorMessage(errors) {
                errors = errors.hasOwnProperty('errors') ? errors.errors : errors;
                for (const errorsName in errors) {
                    if (errors.hasOwnProperty(errorsName)) {
                        errors[errorsName].forEach(error => {
                            setTimeout(() => {
                                this.$message({
                                    type: 'error',
                                    message: error,
                                    duration: 10000,
                                });
                            }, 300)
                        })
                    }
                }
            },
            handleSuccessSendForm(successMessage) {
                this.closeDialog();
                Notification.success({
                    title: 'Success',
                    message: successMessage,
                    type: 'success'
                });
            },
            handleFormValidateError() {
                let elementError = this.$refs.patientForm.$children.find(item => item.validateState === 'error');
                if (Boolean(elementError)) {
                    elementError.$el.scrollIntoView({block: "center", inline: "center", behavior: "smooth"})
                }
            },
            sendForm() {
                this.isLoading = true;
                this.$refs.patientForm.validate((valid) => {
                    let isTemplatesDiagnosesValid = this.validationDiagnosePointer();
                    if (valid && isTemplatesDiagnosesValid) {
                        let payload = {
                            formData: Object.assign({}, this.formData, {
                                is_payment_forbidden: this.formData.is_payment_forbidden ? 1 : 0,
                            }),
                        };
                        let successMessage = 'Patient was created successfully';
                        let dispatchName = 'createPatient';
                        if (!this.isCreated) {
                            payload.patient_id = this.patientId;
                            successMessage = 'Patient was updated successfully';
                            dispatchName = 'updatedPatient';
                        }
                        this.$store.dispatch(dispatchName, payload)
                            .then(({data}) => {
                                this.isLoading = false;
                                if (this.scheduleAppointment) {
                                    this.closeDialog();
                                    let patient = {
                                        patient_id: data.patient.id,
                                        full_name: data.patient.first_name + ' ' + data.patient.last_name,
                                        phone: data.patient.cell_phone,
                                        email: data.patient.email,
                                        secondary_email: data.patient.secondary_email,
                                    }
                                    if (data.patient.middle_initial) {
                                        patient.full_name = data.patient.first_name + ' ' + data.patient.last_name + ' ' + data.patient.middle_initial;
                                    }
                                    this.$emit('scheduleAppointment', patient);
                                }
                                if (!this.isCreated) {
                                    this.$store.dispatch('getPatient', {patientId: this.patientId}).finally(() => this.handleSuccessSendForm(successMessage));
                                } else {
                                    this.handleSuccessSendForm(successMessage);
                                }
                            })
                            .catch(error => {
                                this.isLoading = false;
                                if (error.response && error.response.data && error.response.status === 422) {
                                    this.handleErrorMessage(error.response.data)
                                } else {
                                    this.$message({
                                        type: 'error',
                                        message: 'Oops, something went wrong!',
                                        duration: 10000,
                                    });
                                }
                            })
                    } else {
                        this.isLoading = false;
                        this.handleFormValidateError();
                    }
                })
            },
            closeDialog() {
                this.showDialog = false;
            },
            handleBlur(fieldName) {
                this.handleInputNumberBlur("formData", fieldName);
            },
        },
        mounted() {
            this.initSelectFieldsData();
            this.initPatientSpecificFormData();
            this.disabledButtonRemoveRow(this.formData.templates)
        }
    }
</script>

<style lang="scss">
    .patient-dialog {

        .el-dialog {
            width: 95%;
            max-width: 900px;
        }

        .el-form-item {
            margin-bottom: 15px;

            &__label {
                width: 100%;
                line-height: 24px;
                font-size: 13px;
                text-align: left;
            }

            .el-select {
                width: 100%;

                .el-input__suffix {

                    i.el-input__icon {
                        line-height: 40px;
                    }
                }
            }
        }

        .form-group {
            position: relative;
            padding: 0 15px 10px;
            z-index: 15;

            &--diagnosis {
                z-index: 20;
            }

            &__title {
                position: relative;
                font-size: 16px;
                padding: 0 15px;
                margin-bottom: 15px;
                z-index: 5;
                display: inline-block;
                background-color: #ffffff;
            }

            &::before {
                content: '';
                position: absolute;
                top: 13px;
                left: 0;
                right: 0;
                bottom: 0;
                border: 1px solid #ebeef5;
                border-radius: 5px;
                z-index: -5;
            }
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin: 0 -15px;

            &--table {
                padding-bottom: 15px;
            }
        }

        .form-col {
            width: auto;
            flex: 1 0 auto;
            padding: 0 15px;

            &-4 {
                width: 33.333%;
            }

            &-6 {
                width: 50%;
            }

            &-12 {
                width: 100%;
            }

            @media (max-width: 930px) {
                width: 100%;
            }
        }

        .form-field {
            width: 100%;

            &-number {

                .el-input__inner {
                    text-align: left;
                }
            }
        }

        .form-footer {
            padding-top: 15px;

            &-control {
                display: flex;
                justify-content: flex-end;
            }
        }

        .multiselect {

            &.document-diagnoses-multiselect {

                .multiselect__tags {
                    min-height: 40px;
                    border-radius: 4px !important;
                    padding-top: 5px;
                    border: 1px solid #DCDFE6;
                    margin-top: 30px;

                    .multiselect__input {
                        margin-top: 7px;
                    }
                }
            }
        }

        .table-templates {
            margin-bottom: 20px;

            .el-form-item {
                margin-bottom: 0;
            }

            .column-remove-line {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            &-error {
                position: absolute;
                left: 15px;
                bottom: 50px;
                font-size: 13px;
                color: #F56C6C;
            }
        }

        .modifier-form-row {
            display: flex;

            .el-form-item {
                width: 40px;
                margin-right: 15px;
                flex: 1 0 auto;

                &:last-of-type {
                    margin-right: 0;
                }

                .el-input__inner {
                    padding: 0 10px;
                    text-align: center;
                }
            }
        }

        .added-button-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding-right: 30px;
        }

        .form-control.input-number {
            height: 40px;
            width: 100%;
            padding: 0 15px;
            border: 1px solid rgb(220, 223, 230);
            border-radius: 4px;

            &:hover {
                border-color: rgb(192, 196, 204) !important;
            }

            &:focus {
                border-width: 1px !important;
                border-color: rgb(70, 160, 252) !important;
                box-shadow: none !important;
            }

            &::-webkit-outer-spin-button,
            &::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
        }
    }
</style>
