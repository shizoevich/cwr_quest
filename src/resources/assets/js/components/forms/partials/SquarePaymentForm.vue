<template>
  <div class="square-payment-form container-fluid">
    <form
      id="form"
      class="form-horizontal patient-contact-info-form"
      novalidate
      action=""
      method="post"
    >
      <div class="row">
        <div :id="cardContainerId"></div>
      </div>
    </form>
  </div>
</template>

<script>
import squarePaymentForm from '../../../mixins/square-payment-form';

export default {
  name: "SquarePaymentForm",

  mixins: [squarePaymentForm],

  props: {
    cardContainerId: {
      type: String,
      default: 'card-container',
    },
    postalCode: {
      type: String,
    },
  },

  data() {
    return {
      card_events: [{
        eventName: "postalCodeChanged",
        eventHandler: this.onPostalCodeChanged
      }],
    };
  },

  watch: {
    postalCode() {
      if (!this.square_card) {
        return;
      }

      this.square_card.configure({
        "postalCode": this.postalCode || "",
      })
    },
  },

  mounted() {
    this.initCardForm();
  },
  
  methods: {
    requestCardNonce() {
      if (!this.square_card) {
        return;
      }

      this.square_card.tokenize()
        .then((tokenResult) => {
          this.$emit("square-response-received", tokenResult);

          setTimeout(() => {
            if (tokenResult.status === "OK") {
              this.$emit("card-nonce-received", tokenResult.token);
            }

            this.$emit("got-response");
          }, 100);
        })
        .catch(() => {
          this.$emit("got-response");
        })
    },
  },
};
</script>

<style lang="scss">

</style>
