<template>
    <div>
        <div class="page-loader-wrapper text-center" v-if="loading.payments">
            <pageloader add-classes="page-loader" />
        </div>
        <!--<pageloader v-if="loading.payments"></pageloader>-->
        <div v-else>
            <div class="row">
                <div class="col-xs-9">

                    <form action="" method="post" @submit.prevent="">
                        <div class="form-group date-filter-item">
                            <label>Filter By</label>
                            <select class="form-control" v-model="selected_filter_type" name="selected_filter_type">
                                <option value="1">Date</option>
                                <option value="2">Date Range</option>
                                <option value="3">Month</option>
                            </select>
                        </div>

                        <div class="form-group date-filter-item"
                             v-if="selected_filter_type == 1 || selected_filter_type == 2">
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

                        <div class="form-group">
                            <label v-for="status in statuses" style="margin-right:10px;">
                                <input type="checkbox" v-model="status.selected">
                                {{status.label}}
                            </label>
                        </div>

                        <div class="form-group inline-block">
                            <button class="btn btn-primary" @click.prevent="getPayments()">Show</button>
                        </div>
                    </form>


                </div>
                <div class="col-xs-3">
                    <div class="form-group text-right">
                        <label style="display:block;">&nbsp;</label>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#confirm-posting-modal" :disabled="is_make_posting_disabled">
                            Posting
                        </button>
                    </div>
                </div>
            </div>


            <div v-for="dailyPayments in payments">
                <h2 class="text-center">{{dailyPayments.date}}</h2>
                <div class="table-responsive">
                    <table class="table posting-table" id="posting-table" data-datatable="true">
                        <thead>
                        <tr>
                            <th class="text-center">
                                <input type="checkbox" v-model="dailyPayments.selected"
                                       @change="selectAllPayments(dailyPayments)">
                            </th>
                            <th class="text-center">Patient Name</th>
                            <th class="text-center">Payment Type</th>
                            <th class="text-center">Payment Amount</th>
                            <th class="text-center">Applied Amount</th>
                            <th class="text-center">Patient Balance</th>
                            <th class="text-center"></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payment in dailyPayments.dataset"
                            :class="{'bg-warning':payment.error_message && getAmountMoney(payment.applied_amount) == 0 && payment.is_warning,
                                 'with-errors': (!payment.has_balance && payment.is_applied == 0) || (payment.error_message && !payment.is_warning)}">
                            <td class="text-center" style="width:30px;">
                                <input type="checkbox" v-model="selected_payments[payment.transaction_id]"
                                    :disabled="!payment.allow_posting">
                            </td>
                            <td>
                                <a :href="`${'/chart/' + payment.patient.id}`">{{payment.patient.name}}</a>
                            </td>
                            <td>{{payment.transaction_type}}</td>
                            <td class="text-center">
                                {{getFormattedMoney(getAmountMoney(payment.payment_amount), false)}}
                            </td>
                            <td class="text-center">
                                {{getFormattedMoney(getAmountMoney(payment.applied_amount), false)}}
                            </td>
                            <td class="text-center">
                                {{getFormattedMoney(getPatientBalance(payment.patient.balance), false)}}
                            </td>
                            <td class="text-center">
                                <a :href="`${'https://pm.officeally.com/pm/Accounting/ApplyPayment.aspx?Tab=R&PageAction=apply&PID=' + payment.external_transaction_id}`"
                                   target="_blank">
                                    OfficeAlly
                                </a>
                            </td>
                            <td v-if="statuses.unapplied.selected">
                                <span v-if="payment.start_posting_date">Processing...</span>
                                <span v-else-if="payment.error_message && getAmountMoney(payment.applied_amount) == 0" v-html="payment.error_message"></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--Modals-->
        <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
             id="confirm-posting-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        Are you sure you want to do the posting?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click.prevent="makePosting()">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            No
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import DatetimeFormated from '../../mixins/datetime-formated';

    export default {

        mixins: [
            DatetimeFormated,
        ],

        mounted() {
            this.getPayments();
        },

        data() {
            return {
                loading: {
                    payments: true,
                },
                selected_filter_type: 2,
                selected_payments: {},
                date_from: null,
                date_to: null,
                date_format: 'MM/dd/yyyy',
                month: null,
                payments: {},
                statuses: {
                    applied: {
                        selected: false,
                        label: 'Applied',
                        value: 1,
                    },
                    unapplied: {
                        selected: true,
                        label: 'Unapplied',
                        value: 2,
                    },
                },
                in_progress_count: 0,
                interval: null,
            }
        },

        watch: {
            in_progress_count() {
                if(this.in_progress_count > 0 && this.interval === null) {
                    let self = this;
                    this.interval = window.setInterval(() => {
                        self.getPayments(false, false);
                    }, 30000);
                } else {
                    if(this.interval) {
                        window.clearInterval(this.interval);
                        this.interval = null;
                    }
                }
            },
            'loading.payments': function(val) {
                if(!val) {
                    window.setTimeout(() => {
                        $("table.posting-table[data-datatable='true']").DataTable({
                            'paging': false,
                            'lengthChange': false,
                            'searching': false,
                            'ordering': true,
                            'info': false,
                            'autoWidth': false,
                            order: [[1, 'asc']],
                            columns: [
                                {searchable: false, sortable: false},
                                null,
                                {searchable: false,},
                                {searchable: false,},
                                {searchable: false,},
                                {searchable: false,},
                                {searchable: false, sortable: false},
                                {searchable: false, sortable: false},
                            ]
                        });
                    }, 500);
                }
            },
        },

        computed: {
            is_make_posting_disabled() {
                if(!this.selected_payments) {
                    return true;
                }

                for (let i in this.selected_payments) {
                    if(this.selected_payments[i]) {
                        return false;
                    }
                }

                return true;
            }
        },

        methods: {
            getPatientBalance(balance) {
               if(balance && balance.balance) {
                   return this.getAmountMoney(balance.balance);
               }

               return 0;
            },

            makePosting() {
                let payment_ids = [];
                for(let i in this.selected_payments) {
                    if(this.selected_payments[i]) {
                        payment_ids.push(i);
                    }
                }
                let payload = {
                    payment_ids: payment_ids,
                };
                $('#confirm-posting-modal').modal('hide');
                this.loading.payments = true;

                this.$store.dispatch('makePosting', payload).then(() => {
                    this.getPayments();
                });
            },

            getPayments(withLoader = true, unselect = true) {
                if(withLoader) {
                    this.loading.payments = true;
                }

                let payload = {
                    selected_filter_type: this.selected_filter_type,
                    date_from: this.date_from,
                    date_to: this.date_to,
                    month: this.month,
                    statuses: this.statuses,
                };
                this.$store.dispatch('getOfficeallyPaymentsForPosting', payload).then(response => {
                    if (response.status === 200) {
                        if(unselect) {
                            this.selected_payments = {};
                        }
                        this.payments = response.data.payments;
                        this.date_from = response.data.dateFrom;
                        this.date_to = response.data.dateTo;
                        this.month = response.data.month;
                        this.in_progress_count = response.data.in_progress_count;
                        if(withLoader) {
                            this.loading.payments = false;
                        }
                    }
                });
            },

            getAmountMoney(amount) {
                return amount / 100;
            },

            selectAllPayments(data) {

                for (let i in data.dataset) {
                    if(data.dataset[i].has_balance && data.dataset[i].is_applied == 0 && !data.dataset[i].start_posting_date) {
                        this.$set(this.selected_payments, data.dataset[i].transaction_id, data.selected);
                    }
                }
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

    tr.with-errors {
        background-color: #f8d7dabd !important;
    }
</style>