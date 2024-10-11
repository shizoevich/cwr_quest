<template>
    <div>
        <el-button
            v-if="!patient.is_payment_forbidden"
            :loading="loading_data"
            type="success"
            class="chart__charge-button"
            @click.prevent="openChargeModal"
        >
            Collect Payment
        </el-button>

        <div
            id="change-modal"
            class="modal modal__green modal-vertical-center fade"
            data-backdrop="static"
            data-keyboard="false"
            role="dialog"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" aria-label="Close" :disabled="buttons_disabled" @click.prevent="closeModal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <span class="modal-head">Collect Payment</span>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="title">
                                    Patient Info
                                </div>

                                <span class="info-item">
                                    {{ patient_full_name }}<template v-if="patient_age">, {{ patient_age }} years old</template>
                                </span>
                                <span class="info-item">
                                    Co-Pay: ${{ patient_copay }}
                                </span>
                              <span class="info-item">
                                    Charge for cancellation: ${{ patient_cancellation_fee }}
                                </span>
                            </div>
                            <div class="col-sm-2"></div>
                            <div class="col-sm-5">
                                <div class="title">
                                    Insurance
                                </div>

                                <span class="info-item">
                                    {{ patient.primary_insurance }}
                                </span>
                                <span class="info-item" v-if="patient.insurance_plan">
                                    {{ patient.insurance_plan.name }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="charge-section__container text-center">
                                    <div class="charge-section">
                                        <div class="form-group" :class="{ 'has-error': errors.has('selected_catalog_item') }">
                                            <label class="control-label">Payment Type</label>
                                            <el-select
                                                v-model="selected_catalog_item"
                                                placeholder="Select Payment Type"
                                                style="width:100%;"
                                                :loading="catalog_items_loading"
                                                :disabled="buttons_disabled"
                                                popper-class="el-select__selected-green"
                                            >
                                                <el-option
                                                    v-for="item in catalog_items"
                                                    :key="item.id"
                                                    :label="item.name"
                                                    :value="item.id"
                                                ></el-option>
                                            </el-select>
                                        </div>

                                        <div class="form-group" :class="{ 'has-error': errors.has('selected_appointment') }">
                                            <label class="control-label">Date of Service</label>
                                            <el-select
                                                v-model="selected_appointment"
                                                placeholder="Select Date of Service"
                                                style="width:100%;"
                                                :loading="appointments_loading"
                                                :disabled="buttons_disabled || !selected_catalog_item"
                                                popper-class="el-select__selected-green el-select-dropdown__short"
                                            >
                                                <el-option
                                                    v-for="item in appointments_by_catalog_item"
                                                    :key="item.id"
                                                    :label="item.text"
                                                    :value="item.id"
                                                />
                                            </el-select>
                                        </div>

                                        <div class="form-group" :class="{ 'has-error': errors.has('payment_amount') }">
                                            <label class="control-label">Amount</label>
                                            <el-input-number
                                                id="charge_modal__amount"
                                                v-model="payment_amount"
                                                :precision="2"
                                                :step="1"
                                                :controls="false"
                                                :min="0"
                                                style="width:100%;"
                                                :disabled="buttons_disabled"
                                                class="with-dollar-symbol"
                                            >
                                            </el-input-number>
                                        </div>

                                        <div class="form-group" :class="{ 'has-error': errors.has('selected_card') }">
                                            <label class="control-label">Credit Card</label>
                                            <el-select
                                                v-model="selected_card"
                                                placeholder="Select Credit Card"
                                                style="width:100%;"
                                                :loading="credit_cards_loading"
                                                :disabled="buttons_disabled"
                                                popper-class="el-select__selected-green"
                                            >
                                                <el-option
                                                    v-for="credit_card in credit_cards.filter((card) => !card.is_expired)"
                                                    :key="credit_card.id"
                                                    :label="getCardLabel(credit_card)"
                                                    :value="credit_card.id"
                                                >
                                                    <div class="card-item">
                                                        {{ getCardLabel(credit_card) }}
                                                        <img
                                                            v-if="getCardBrandLogo(credit_card)"
                                                            width="30"
                                                            :src="getCardBrandLogo(credit_card)"
                                                            :alt="credit_card.card_brand"
                                                        >
                                                    </div>
                                                </el-option>
                                                <el-option
                                                    :key="-1"
                                                    :value="-1"
                                                    label="Enter Credit Card Manually"
                                                >
                                                    <div class="card-item">
                                                        Enter Credit Card Manually
                                                        <img width="30" src="/images/icons/add-credit-card.png">
                                                    </div>
                                                </el-option>
                                            </el-select>
                                        </div>

                                        <template v-if="selected_card === -1">
                                            <div class="row">
                                                <square-payment-form-optimized
                                                    ref="square_form"
                                                    style="width:290px;"
                                                    :postal-code="zip"
                                                    @square-initialization="changeSquareInitializationStatus"
                                                    @card-nonce-received="chargePatientByCardNonce"
                                                    @validation-fails="emitSquareValidationFails"
                                                    @postal-code-changed="setPostalCode"
                                                />
                                                <div class="col-md-12">
                                                    <el-checkbox
                                                        v-if="square_initialized"
                                                        :disabled="buttons_disabled"
                                                        v-model="store_credit_card"
                                                    >
                                                        Save this card to {{ patient_full_name }}'s file
                                                    </el-checkbox>
                                                </div>
                                            </div>
                                        </template>

                                        <span style="color:#a94442;" v-if="validation_message">
                                            {{ validation_message }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <el-button :loading="is_charging" :disabled="buttons_disabled" type="success" @click.prevent="chargePatient">Collect Payment</el-button>
                        <el-button :disabled="buttons_disabled" @click.prevent="closeModal">Cancel</el-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Notification } from 'element-ui';
import { PURPOSE_DEDUCTIBLE_ID } from '../settings';

export default {
    props: {
        patient: {
            required: true,
            type: Object,
        }
    },

    data() {
        return {
            payment_amount: null,
            credit_cards: [],
            selected_card: null,
            credit_cards_loading: false,
            store_credit_card: false,
            catalog_items: [],
            selected_catalog_item: null,
            catalog_items_loading: false,
            appointments: {
                for_copay: [],
                for_cancellation_fee: [],
            },
            selected_appointment: null,
            appointments_loading: false,
            is_charging: false,
            square_initialized: false,
            validation_message: '',
            zip: '',
        };
    },

    computed: {
        loading_data() {
            return this.credit_cards_loading || this.catalog_items_loading || this.appointments_loading;
        },

        buttons_disabled() {
            return this.is_charging || (this.selected_card === -1 && !this.square_initialized);
        },

        patient_full_name() {
            let full_name = `${this.patient.first_name} ${this.patient.last_name}`;

            if (this.patient.middle_initial) {
                full_name += ` ${this.patient.middle_initial}`;
            }

            return full_name;
        },

        patient_age() {
            if (this.patient.date_of_birth) {
                return moment().diff(moment(this.patient.date_of_birth), 'years');
            }

            return '';
        },

        patient_copay() {
            if (this.patient.visit_copay) {
                return parseFloat(this.patient.visit_copay)
            }

            return 0;
        },

        patient_cancellation_fee() {
            if (this.patient.cancelation_fee_info.charge_for_cancellation) {
                return parseFloat(this.patient.cancelation_fee_info.charge_for_cancellation)
            }

            return 0;
        },

        copay_catalog_item() {
            const item = this.catalog_items.find(item => item.name.toLowerCase().includes('co-pay') || item.name.toLowerCase().includes('self-pay'));

            return item ? item.id : null;
        },

        deductible_catalog_item() {
            const item = this.catalog_items.find(item => item.name.toLowerCase().includes('deductible'));

            return item ? item.id : null;
        },

        cancellation_fee_catalog_item() {
            const item = this.catalog_items.find(item => item.name.toLowerCase().includes('charge for cancellation'));

            return item ? item.id : null;
        },

        appointments_by_catalog_item() {
            if (this.selected_catalog_item) {
                if (this.selected_catalog_item === this.copay_catalog_item || this.selected_catalog_item === this.deductible_catalog_item) {
                    return this.appointments.for_copay;
                }
                if (this.selected_catalog_item === this.cancellation_fee_catalog_item) {
                    return this.appointments.for_cancellation_fee;
                }
            }

            return [];
        },

        collect_payment_appointment() {
            return this.$store.state.collectPaymentAppointment;
        },
    },

    watch: {
        payment_amount() {
            if (this.payment_amount) {
                this.errors.remove('payment_amount');
            }
        },

        selected_card() {
            if (this.selected_card) {
                this.errors.remove('selected_card');
            }
        },

        selected_catalog_item() {
            if (this.selected_catalog_item) {
                this.selected_appointment = null;
                this.errors.remove('selected_catalog_item');
            }
        },

        selected_appointment() {
            if (this.selected_appointment) {
                this.errors.remove('selected_appointment');
            }

            this.setPaymentAmount();
        },

        collect_payment_appointment() {
            if (this.collect_payment_appointment) {
                this.openChargeModal();
            }
        },

        loading_data() {
            this.$store.dispatch('setCollectPaymentDataLoading', this.loading_data);
        },
    },

    methods: {
        openChargeModal() {
            Promise.all([this.loadCreditCards(), this.loadCatalogItems(), this.loadChargeableAppointments()])
                .then(() => {
                    $('#change-modal').modal('show');

                    this.fillDataFromCollectPaymentAppointment();
                });
        },

        loadCreditCards() {
            return new Promise((resolve) => {
                this.credit_cards_loading = true;
                this.$store.dispatch('getCreditCards', {patientId: this.patient.id, forceSync: 1})
                    .then((response) => {
                        this.credit_cards = response.data.credit_cards;
                    })
                    .finally(() => {
                        this.credit_cards_loading = false;
                        resolve();
                    });
            });
        },

        loadCatalogItems() {
            return new Promise((resolve) => {
                this.catalog_items_loading = true;
                this.$store.dispatch('getSquareCatalogItems', {patientId: this.patient.id})
                    .then((response) => {
                        this.catalog_items = response.data.catalog_items;
                    })
                    .finally(() => {
                        this.catalog_items_loading = false;
                        resolve();
                    });
            });
        },

        loadChargeableAppointments() {
            return new Promise((resolve) => {
                this.appointments_loading = true;
                this.$store.dispatch('getChargeableAppointments', {patientId: this.patient.id})
                    .then((response) => {
                        this.appointments.for_copay = response.data.for_copay;
                        this.appointments.for_cancellation_fee = response.data.for_cancellation_fee;
                    })
                    .finally(() => {
                        this.appointments_loading = false;
                        resolve();
                    });
            });
        },

        setPostalCode(zip) {
            this.zip = zip;
        },

        changeSquareInitializationStatus(initialization) {
            this.square_initialized = !initialization;
        },

        chargePatient() {
            this.validation_message = '';
            this.validate().then((valid) => {
                if (!valid) {
                    return;
                }

                this.is_charging = true;

                if (this.selected_card === -1) {
                    this.$refs.square_form.requestCardNonce();
                } else {
                    this.sendRequest({
                        card_id: this.selected_card,
                        amount: Math.round(this.payment_amount * 100),
                        catalog_item_id: this.selected_catalog_item,
                        appointment_id: this.selected_appointment,
                    });
                }
            });
        },

        validate() {
            return new Promise((resolve) => {
                this.$validator.validateAll().then((valid) => {
                    let has_inner_errors = false;
                    if (!this.payment_amount || this.payment_amount <= 0) {
                        this.errors.add({
                            field: 'payment_amount',
                            msg: 'The amount must be at least 1.'
                        });
                        has_inner_errors = true;
                    }
                    if (!this.selected_card) {
                        this.errors.add({
                            field: 'selected_card',
                            msg: 'The credit card field is required.'
                        });
                        has_inner_errors = true;
                    }
                    if (!this.selected_catalog_item) {
                        this.errors.add({
                            field: 'selected_catalog_item',
                            msg: 'The payment type field is required.'
                        });
                        has_inner_errors = true;
                    }
                    if (!this.selected_appointment) {
                        this.errors.add({
                            field: 'selected_appointment',
                            msg: 'The date of service field is required.'
                        });
                        has_inner_errors = true;
                    }

                    resolve(valid && !has_inner_errors);
                });
            });
        },

        chargePatientByCardNonce(creditCardData) {
            this.sendRequest({
                card_nonce: creditCardData.token,
                zip: this.zip,
                store_credit_card: this.store_credit_card,
                amount: Math.round(this.payment_amount * 100),
                catalog_item_id: this.selected_catalog_item,
                appointment_id: this.selected_appointment,
            });
        },

        sendRequest(payload) {
            this.$store.dispatch('chargePatient', {patientId: this.patient.id, data: payload})
                .then(() => {
                    const patientId = this.patient.id;
                    this.$store.dispatch('getPatient', {patientId: patientId});
                    this.$store.dispatch('getPatientAppointments', patientId);
                    this.$store.dispatch('getPatientPreprocessedTransactions', patientId);

                    this.closeModal();
                    Notification.success({
                        title: 'Success',
                        message: `Co-pay has been successfully collected from ${this.patient_full_name}.`,
                        type: 'success'
                    });
                })
                .catch(error => {
                    if (error.response.data.errors) {
                        if (error.response.data.errors.length) {
                            this.validation_message = error.response.data.errors[0].detail;
                        } else {
                            this.validation_message = Object.values(error.response.data.errors)[0][0];
                        }
                    }
                })
                .finally(() => {
                    this.is_charging = false;
                });
        },

        emitSquareValidationFails(message) {
            this.is_charging = false;
            this.validation_message = message;
        },

        closeModal() {
            $('#change-modal').modal('hide');
            this.resetData();
        },

        resetData() {
            this.payment_amount = null;
            this.credit_cards = [];
            this.selected_card = null;
            this.credit_cards_loading = false;
            this.store_credit_card = false;
            this.catalog_items = [];
            this.selected_catalog_item = null;
            this.catalog_items_loading = false;
            this.appointments.for_copay = [];
            this.appointments.for_cancellation_fee = [];
            this.selected_appointment = null;
            this.appointments_loading = false;
            this.is_charging = false;
            this.square_initialized = false;
            this.validation_message = '';
            this.zip = '';
            this.errors.clear();
            this.$store.dispatch('setCollectPaymentAppointment', null);
        },

        getCardLabel(credit_card) {
            let exp_month = credit_card.exp_month;
            if (exp_month < 10) {
                exp_month = '0' + exp_month;
            }

            return `**** **** **** ${credit_card.last_four}`;
        },

        getCardBrandLogo(credit_card) {
            let base_logo_url = '/images/icons/';
            let logoName = null;

            switch (credit_card.card_brand) {
                case 'VISA':
                    logoName = 'visa.svg';
                    break;
                case 'MASTERCARD':
                    logoName = 'mastercard.svg';
                    break;
                case 'AMERICAN_EXPRESS':
                    logoName = 'american_express.svg';
                    break;
                default:
                    return null;
            }

            return base_logo_url + logoName;
        },

        fillDataFromCollectPaymentAppointment() {
            if (!this.collect_payment_appointment) {
                return;
            }

            if (
                this.appointments.for_copay.find((item) => item.id === this.collect_payment_appointment.id) === -1
                && this.appointments.for_cancellation_fee.find((item) => item.id === this.collect_payment_appointment.id) === -1
            ) {
                return;
            }

            this.setCatalogItem();

            this.$nextTick(() => {
                this.selected_appointment = this.collect_payment_appointment.id;
            });
        },

        setCatalogItem() {
            if (!this.collect_payment_appointment) {
                return;
            }

            if (this.collect_payment_appointment.late_cancellation_transaction) {
                this.selected_catalog_item = this.cancellation_fee_catalog_item;
            } else if (this.collect_payment_appointment.officeally_transaction) {
                if (this.collect_payment_appointment.officeally_transaction.transaction_purpose_id === PURPOSE_DEDUCTIBLE_ID) {
                    this.selected_catalog_item = this.deductible_catalog_item;
                } else {
                    this.selected_catalog_item = this.copay_catalog_item;
                }
            }
        },

        setPaymentAmount() {
            let paymentAmount = 0;
            if (this.selected_appointment) {
                const appointment = this.appointments_by_catalog_item.find((item) => item.id === this.selected_appointment);

                if (this.selected_catalog_item === this.copay_catalog_item || this.selected_catalog_item === this.deductible_catalog_item) {
                    paymentAmount = appointment ? appointment.copay : 0;
                }
                if (this.selected_catalog_item === this.cancellation_fee_catalog_item) {
                    paymentAmount = appointment ? appointment.cancellation_fee : 0;
                }
            }
            
            this.payment_amount = paymentAmount;
        },
    }
}
</script>

<style lang="scss" scoped>
.card-item {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}

.title {
    font-weight: 600;
    font-size: 16px;
    line-height: 24px;
    padding-bottom: 7px;
    border-bottom: 1px solid #333333;
    margin-bottom: 20px;
}

.info-item {
    font-weight: 600;
    font-size: 14px;
    display: block;
    line-height: 24px;
    padding-bottom: 5px;
}

.charge-section__container {
    border-top: 1px solid #333333;
    padding-top: 20px;
    margin-top: 20px;

    .charge-section {
        width: 290px;
        text-align: left;
        display: inline-block;

        label {
            display: block;
            font-weight: normal;
        }
    }
}
</style>

<style lang="scss">
.el-select-dropdown__short {
    .el-select-dropdown__wrap {
        max-height: 200px;
    }
}
</style>