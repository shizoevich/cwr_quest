<template>
    <div class="appointments-table-container">
        <div class="appointments-wrapper">
            <div class="form-group text-right" v-if="!is_secretary && is_admin">
                <square-customers class="inline-block" v-if="!is_secretary && is_admin && square_customers && square_customers.length > 0"></square-customers>
                <button class="btn btn-primary" @click="showAdjustmentModal()">Adjustment</button>
            </div>
            <div class="table-responsive" v-if="transactions && transactions.length">
                <table class="table table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th style="width:150px;">Payment Method</th>
                        <th style="width:200px;">Payment Type</th>
                        <th>Requested / Approved by</th>
                        <th>Payment Info</th>
                        <th style="width:100px;">Amount</th>
                        <th style="width:100px;">Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(transaction, index) in transactions" :class="{odd: (index % 2 === 0)}">
                        <td class="text-center" style="width:200px;">
                            {{getFormattedDateTime(transaction.transaction_date)}}
                        </td>
                        <td class="text-center">
                            {{getPaymentMethod(transaction.transaction_type)}}
                        </td>
                        <td class="text-center">
                            {{ transaction.catalog_item }}
                        </td>
                        <td class="text-center">
                            {{ transaction.user_name || '-' }}
                        </td>
                        <td class="text-center">
                            <div v-for="item in getPaymentInfo(transaction)" v-html="item">
                            </div>
                        </td>
                        <td class="text-center" :class="getBalanceClass(transaction.amount_money)">
                            {{getFormattedMoney(getAmountMoney(transaction.amount_money))}}
                        </td>
                        <td class="text-center" :class="getBalanceClass(transaction.balance_after_transaction)">
                            {{getFormattedMoney(getAmountMoney(transaction.balance_after_transaction), false)}}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="no-appointments" v-else>
                No payments.
            </div>
        </div>

        <!--Modals-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="adjustment-modal" role="dialog" v-if="!is_secretary && is_admin">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Adjustment</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group" id="adjustment-amount">
                            <label>Amount</label>
                            <input type="number" class="form-control" v-model="adjustment_amount">
                        </div>
                        <div>
                            Current Balance:
                            <label v-html="getFormattedPatientBalanceHTML(getPatientPreprocessedBalance(this.patient))"></label>
                        </div>

                        <div>
                            New Balance:
                            <label v-html="new_balance"></label>
                        </div>

                        <div class="form-group"  id="adjustment-comment">
                            <label>Comment</label>
                            <textarea rows="5" v-model="adjustment_comment" class="form-control no-resize"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span class="text-red validation-error-msg">{{ adjustment_validation_message }}</span>
                        <button type="button" class="btn btn-primary" :disabled="adding_adjustment"
                                @click.prevent="addAdjustment()">
                            OK
                        </button>
                        <button class="btn btn-default" @click="closeAdjustmentModal()">Cancel</button>

                        <!--<pageloader add-classes="save-loader" v-show="statuses.saving_document_type"></pageloader>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import DatetimeFormated from '../mixins/datetime-formated';
    import PatientBalance from '../mixins/patient-balance';

    export default {

        mixins: [
            DatetimeFormated,
            PatientBalance,
        ],

        data() {
            return {
                adjustment_amount: null,
                adjustment_comment: '',
                adjustment_validation_message: '',
                adding_adjustment: false,
            }
        },

        mounted() {
        },

        computed: {
            new_balance() {
                let balance = this.getPatientPreprocessedBalance(this.patient);
                if(this.adjustment_amount) {
                    return this.getFormattedPatientBalanceHTML(balance + parseFloat(this.adjustment_amount));
                }

                return this.getFormattedPatientBalanceHTML(balance);
            },

            transactions() {
                return this.$store.state.patient_transactions;
            },

            is_admin() {
                return this.$store.state.isUserAdmin;
            },

            patient() {
                return this.$store.state.currentPatient;
            },

            loading_appointments_tab() {
                return this.$store.state.loading_appointments_tab;
            },

            is_admin() {
                return this.$store.state.isUserAdmin;
            },

            is_secretary() {
                return this.$store.state.isUserSecretary;
            },

            square_customers() {
                return this.$store.state.patient_square_customers;
            },

        },

        watch: {
            adjustment_amount() {
                if(parseFloat(this.adjustment_amount)) {
                    $('#adjustment-amount').removeClass('with-errors');
                }
            },

            adjustment_comment() {
                if(this.adjustment_comment.trim()) {
                    $('#adjustment-comment').removeClass('with-errors');
                }
            },
        },

        methods: {
            addAdjustment() {
                if(!this.validateAdjustment()) {
                    return false;
                }
                this.adding_adjustment = true;
                let payload = {
                    amount: Math.round(parseFloat(this.adjustment_amount) * 100),
                    patient_id: this.patient.id,
                    comment: this.adjustment_comment,
                };

                this.$store.dispatch('addAdjustment', payload).then(() => {
                    this.closeAdjustmentModal();
                    const patientId = this.patient.id;
                    this.$store.dispatch('getPatient', {patientId: patientId});
                    this.$store.dispatch('getPatientPreprocessedTransactions', patientId);
                    this.adding_adjustment = false;
                });
            },

            validateAdjustment() {
                let hasErrors = false;
                if(!parseFloat(this.adjustment_amount)) {
                    hasErrors = true;
                    this.adjustment_validation_message = this.$store.state.validation_messages.required;
                    $('#adjustment-amount').addClass('with-errors');
                }

                if(!this.adjustment_comment.trim()) {
                    hasErrors = true;
                    this.adjustment_validation_message = this.$store.state.validation_messages.required;
                    $('#adjustment-comment').addClass('with-errors');
                }

                return !hasErrors;
            },

            clearAdjustmentModal() {
                this.adjustment_amount = null;
                this.adjustment_comment = '';
                this.adjustment_validation_message = '';
                $('#adjustment-modal .form-group').removeClass('with-errors');
            },

            showAdjustmentModal() {
                this.clearAdjustmentModal();
                $('#adjustment-modal').modal('show');
            },

            closeAdjustmentModal() {
                $('#adjustment-modal').modal('hide');
                this.clearAdjustmentModal();
            },

            ucfirst(str) {
                if(str && str.length) {
                    str = str.toLowerCase();
                    let f = str.charAt(0).toUpperCase();

                    return f + str.substr(1, str.length-1);
                }

                return str;
            },

            getCardNumber(lastFour) {
                if(lastFour) {
                    return '**** **** **** ' + lastFour;
                }

                return lastFour
            },

            getPaymentInfo(transaction) {
                let info = [];
                if(transaction.card_brand) {
                    info.push(transaction.card_brand);
                }
                if(transaction.card_last_four) {
                    info.push(this.getCardNumber(transaction.card_last_four));
                }
                if(transaction.comment) {
                    info.push("Comment: " + transaction.comment);
                }
                if(transaction.appt_date) {
                    info.push(`${transaction.transaction_type} (Appt. date: ${this.getFormattedDateTime(transaction.appt_date)})`);
                }

                if(!info.length) {
                    info.push('-');
                }

                return info;
            },

            getPaymentMethod(method) {
                if(method === 'CARD') {
                    return 'Credit Card';
                }

                return this.ucfirst(method);
            },

            getAmountMoney(amount) {
                return amount / 100;
            },

            getFormattedMoney(amount, allowPlus = true) {
                let sign = '';
                if(amount < 0) {
                    amount *= -1;
                    sign = '-';
                } else if(amount > 0 && allowPlus) {
                    sign = '+';
                }

                return sign + '$' + amount;
            },

            getBalanceClass(amount) {
                if(amount > 0) {
                    return 'balance-green';
                } else if(amount < 0) {
                    return 'balance-red';
                }
            }
        }
    }
</script>

<style scoped>
    .balance-red {
        color: #fb0007;
    }
    .balance-green {
        color: #02a756;
    }
</style>