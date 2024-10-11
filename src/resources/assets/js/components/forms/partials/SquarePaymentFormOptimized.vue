<template>
    <div class="container-fluid">
        <form
            id="form"
            class="form-horizontal form-with-spinner patient-contact-info-form sq-form"
            novalidate
            action=""
            method="post"
            v-loading="square_initialization"
            element-loading-spinner="el-icon-loading"
        >
            <div class="row">
                <div v-if="withEmailField" class="row-centered sq-form__email">
                    <label class="control-label pf-label" :class="{ 'label-error': isEmailError }" data-input_name="email" style="margin-right: 15px;">
                        Email
                    </label>
                    <div id="sq-email" style="flex-grow: 1; padding: 0">
                        <input
                            v-model.trim="email"
                            id="email"
                            name="email"
                            type="email"
                            placeholder="example@gmal.com"
                            v-validate="'required|email'"
                            class="sq-input form-control"
                            :class="{ 'input-error': isEmailError }"
                        />
                    </div>
                </div>

                <div :id="cardContainerId"></div>
            </div>
        </form>
    </div>
</template>

<script>
import squarePaymentForm from '../../../mixins/square-payment-form';

export default {
    name: "SquarePaymentFormOptimized",

    mixins: [squarePaymentForm],

    props: {
        cardContainerId: {
            type: String,
            default: 'card-container',
        },
        postalCode: {
            required: true,
        },
        withEmailField: {
            required: false,
            type: Boolean,
        },
        patientEmail: {
            required: false,
            type: String,
        },
    },

    data() {
        return {
            email: "",
            isEmailError: false,
            isStartHandleEmailError: false,
            card_events: [{
                eventName: "postalCodeChanged",
                eventHandler: this.onPostalCodeChanged
            }],
        };
    },

    mounted() {
        this.email = this.patientEmail;
        this.initCardForm();
    },

    watch: {
        patientEmail(value) {
            this.email = value;
        },

        email(value) {
            this.$validator.validate().then(() => {
                this.isEmailError = this.errors.has("email") && this.isStartHandleEmailError;
            });
            this.$emit("patient-email-changed", value);
        },
    },

    methods: {
        requestCardNonce() {
            this.validateEmail().then((isValid) => {
                if (!isValid) {
                    return;
                }
                
                this.tokenizeCard();
            });
        },

        validateEmail() {
            return new Promise((resolve) => {
                if (!this.withEmailField) {
                    resolve(true);
                    return;
                }

                this.isStartHandleEmailError = true;
                this.$validator.validate().then(() => {
                    if (this.errors.has("email")) {
                        this.isEmailError = true;
                        this.$emit("validation-fails", this.errors.first("email"));
                        resolve(false);
                    } else {
                        resolve(true);
                    }
                });
            });
        },

        tokenizeCard() {
            if (!this.square_card) {
                return;
            }

            this.square_card.tokenize()
                .then((tokenResult) => {
                    if (tokenResult.status === "OK") {
                        this.$emit("card-nonce-received", tokenResult);
                        return;
                    }

                    const errorMessage = tokenResult.errors && tokenResult.errors.length
                        ? tokenResult.errors[0].message
                        : "";
                    this.$emit("validation-fails", errorMessage);
                })
                .catch(() => {
                    this.$emit("validation-fails", "");
                });
        }
    },
};
</script>

<style lang="scss" scoped>
.sq-form {
    margin-left: -15px;
    margin-right: -15px;

    .row {
        margin-left: 0;
        margin-right: 0;
    }
}

.sq-form__email {
    margin-bottom: 10px;

    &:after {
        clear: both;
        content: "";
        display: block;
    }
}

.form-with-spinner {
    min-height: 24px;
}

.sq-input {
    width: 100%;
    height: 36px !important;
    padding: 0 6px !important;
    font-size: 14px !important;
    background-color: #ffffff;

    &::placeholder{
        color: #3e4855;
        opacity: 0.5;
    }
}
</style>
