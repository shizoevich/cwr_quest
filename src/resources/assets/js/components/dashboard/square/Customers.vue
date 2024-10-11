<template>
    <div>
        <button class="btn btn-default" data-toggle="modal"
                data-target="#customers">
            Square Customers <span class="badge">{{customers.length}}</span>
        </button>

        <!-- Modal -->
        <div id="customers" class="modal modal-vertical-center fade"
             data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" :disabled="detaching">&times;</button>
                        <h4 class="modal-title">Square Customers</h4>
                    </div>
                    <div class="modal-body">
                        <ul>
                            <li v-for="customer in customers">
                                {{customer.first_name}} {{customer.last_name}}
                                (<a target="_blank" :href="getCustomerUrl(customer.external_id)">Square</a>)
                                <i class="fa fa-times fa-relationship-button" title="Detach"
                                   @click="setCustomerToDetaching(customer)"></i>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" :disabled="detaching">Close
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div id="detach-confirmation" class="modal modal-vertical-center fade"
             data-backdrop="static" data-keyboard="false" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Detach Customer</h4>
                    </div>
                    <div class="modal-body" v-if="customer_to_detaching">
                        Are you sure you want to detach Square customer
                        <a target="_blank" :href="getCustomerUrl(customer_to_detaching.external_id)">
                            <b>{{customer_to_detaching.first_name}} {{customer_to_detaching.last_name}}</b>
                        </a>
                        from patient <b>{{patient.first_name}} {{patient.last_name}}</b>?
                        All customer payments will be deducted from the patient's balance.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click="detachCustomer()" :disabled="detaching">
                            Yes
                        </button>
                        <button type="button" class="btn btn-default" @click="setCustomerToDetaching(null)"
                                :disabled="detaching">
                            No
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                customer_to_detaching: null,
                detaching: false,
            };
        },

        methods: {
            getCustomerUrl(id) {
                return 'https://squareup.com/dashboard/customers/directory/customer/' + id;
            },

            setCustomerToDetaching(customer) {
                this.customer_to_detaching = customer;
            },

            detachCustomer() {
                this.detaching = true;
                this.$store.dispatch('detachSquareCustomerFromPatient', {
                    customer_id: this.customer_to_detaching.id,
                    patient_id: this.patient.id,
                }).then(() => {
                    this.setCustomerToDetaching(null);
                    this.$store.dispatch('getPatient', {patientId: this.patient.id});
                    window.setTimeout(() => {
                        this.detaching = false;
                        this.$store.dispatch('getPatientPreprocessedTransactions', this.patient.id);
                    }, 500);

                    this.$store.dispatch('getPatientSquareCustomers', this.patient.id);
                });
            },
        },

        computed: {
            customers() {
                return this.$store.state.patient_square_customers;
            },
            patient() {
                return this.$store.state.currentPatient;
            },
        },

        watch: {
            customer_to_detaching() {
                console.log(this.customer_to_detaching);
                if (this.customer_to_detaching === null) {
                    console.log(1111);
                    $('#detach-confirmation').modal('hide');
                    if (!this.detaching) {
                        console.log(2222);

                        window.setTimeout(() => {
                            $('#customers').modal('show');
                        }, 100)
                    }
                } else {
                    console.log(3333);

                    $('#customers').modal('hide');

                    window.setTimeout(() => {
                        $('#detach-confirmation').modal('show');
                    }, 100)
                }
            },
        }
    }
</script>