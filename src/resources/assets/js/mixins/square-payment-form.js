import {
  SQUARE_FORM_APPLICATION_ID,
} from "../settings";

export default {
  data() {
    return {
      square_card: null,
      square_initialization: false,
      // card_events: [{
      //   eventName: "postalCodeChanged",
      //   eventHandler: this.onPostalCodeChanged
      // }],
      card_events: [],
    };
  },

  methods: {
    initCardForm() {
      if (!window.Square) {
        return;
      }

      this.emitFormInitialization(true);

      let payments;
      try {
        payments = window.Square.payments(SQUARE_FORM_APPLICATION_ID, null);
      } catch (e) {
        console.error("Payments initialization failed", e);
        this.emitFormInitialization(false);
        return;
      }

      this.initCard(payments)
        .then((card) => {
          this.square_card = card;
        })
        .catch((e) => {
          console.error("Card initialization failed", e);
        })
        .finally(() => {
          this.emitFormInitialization(false);
        });
    },

    initCard(payments) {
      return new Promise((resolve, reject) => {
        payments.card({
          "postalCode": this.postalCode || "",
        })
          .then((card) => {
            return this.initCardEvents(card);
          })
          .then((card) => {
            return this.attachCard(card);
          })
          .then((card) => {
            resolve(card);
          })
          .catch((e) => {
            reject(e);
          })
      });
    },

    initCardEvents(card) {
      this.card_events.forEach((event) => {
        card.addEventListener(event.eventName, event.eventHandler);
      });
      return card;
    },

    attachCard(card) {
      return new Promise((resolve, reject) => {
        if (!this.cardContainerId) {
          resolve(card);
          return;
        }

        card.attach(`#${this.cardContainerId}`)
          .then(() => {
            resolve(card);
          })
          .catch((e) => {
            reject(e);
          })
      })
    },

    onPostalCodeChanged(event) {
      const postalCode = event && event.detail && event.detail.postalCodeValue;
      this.$emit("postal-code-changed", postalCode);
    },

    emitFormInitialization(loading) {
      this.square_initialization = loading;
      this.$emit("square-initialization", loading);
    },

    requestCardNonce() {
      if (!this.square_card) {
        return;
      }

      this.square_card.tokenize()
        .then((tokenResult) => {
          if (tokenResult.status !== "OK") {
            return;
          }

          this.$emit("card-nonce-received", tokenResult.token);
        })
        .catch(() => {
          //
        })
    },

    resetCardForm() {
      if (!this.square_card) {
        return;
      }

      this.square_card.destroy();
      this.square_card = null;
    },
  },
}