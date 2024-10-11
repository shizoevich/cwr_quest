<template>
  <div style="height: 100%;">
    <div class="container" style="margin-bottom: 80px;">
      <router-link to="/forms" class="btn btn-lg btn-success btn-back">New Search</router-link>
    </div>

    <div class="forms-center-container" v-if="patient">
      <div class="container">
        <div id="status-alert"></div>
        <h2 class="text-center">{{ patientFullName }}</h2>
        <hr class="forms-separator col-sm-8 col-md-6 center-block" v-if="square_cards.length > 0">

        <div class="well col-sm-8 col-md-6 center-block" v-if="square_cards.length > 0">
          <h2>Cards on File:</h2>
          <b>
            <ul class="cards-list">
              <li v-for="(card, index) in square_cards" :key="index">
                <i class="fa" :title="card.card_brand" :class="getFaIcon(card)"></i>
                Ends in {{ card.last_four }}
              </li>
            </ul>
          </b>
        </div>

        <hr class="forms-separator col-sm-8 col-md-6 center-block">

        <div class="well col-sm-8 col-md-6 center-block">
          <div class="form-horizontal" :class="{ 'disabled': credit_card.saving }">
            <h2>Add Card</h2>

            <div class="form-group" v-if="!has_square_account"
              :class="{ 'has-error': credit_card.errors.email != false }">
              <label class="col-xs-12 control-label" for="email"
                :class="{ 'label-error': credit_card.errors.email }">Email:</label>
              <div class="col-xs-12">
                <input type="text" class="form-control card-email" id="email" placeholder="Email"
                  v-model.trim="credit_card.email" v-validate="'required|email'" name="email" @change="changeInput('email')">
                <span class="help-block">{{ credit_card.errors.email }}</span>
              </div>
            </div>

            <div class="form-group">
              <div class="col-xs-12">
                <div :id="cardContainerId"></div>
              </div>
            </div>

            <div class="form-group has-error">
              <div class="col-xs-9">
                <span class="help-block">{{ error }}</span>
              </div>
              <div class="col-xs-3">

                <button type="submit" class="btn btn-success btn-lg pull-right" :disabled="credit_card.saving"
                  :class="{ 'disabled': credit_card.saving }" @click="submit">
                  Add
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import squarePaymentForm from '../../mixins/square-payment-form';

export default {
  mixins: [squarePaymentForm],

  data() {
    return {
      error: null,
      credit_card: {
        saving: false,
        email: null,
        errors: {
          email: false,
          card_form: false,
        },
        nonce: null,
        cardData: null,
      },

      postalCode: '',
      cardContainerId: 'card-container',
    }
  },

  computed: {
    patient() {
      return this.$store.state.currentPatient;
    },
    patientFullName() {
      if (this.patient !== null) {
        return this.patient.first_name + ' ' + this.patient.last_name + ' ' + this.patient.middle_initial;
      } else {
        return '';
      }
    },
    has_square_cards() {
      return this.patient !== null && this.patient.square_account !== null
        && this.patient.square_account.cards !== null;
    },
    has_square_account() {
      return this.patient !== null && this.patient.square_account !== null;
    },
    square_cards() {
      return this.patient !== null && this.has_square_cards ? this.patient.square_account.cards : [];
    },
  },

  mounted() {
    this.fetchData();
  },

  methods: {
    fetchData() {
      let id = this.$route.params.id;
      this.$store.dispatch('getPatient', {
        id: id,
        with: 'credit_cards'
      }).then(response => {
        if (response && [403, 404].includes(response.status)) {
          this.$router.push({ path: '/forms/404' });
        }

        this.postalCode = '';
        this.resetCardForm();

        setTimeout(() => {
          if (response.data.information_form && response.data.information_form.zip) {
            this.postalCode = response.data.information_form.zip;
          }

          this.initCardForm();
        }, 300);
      });
    },

    emitFormInitialization(loading) {
      this.square_initialization = loading;
    },

    requestCardNonce() {
      if (!this.square_card) {
        return;
      }

      this.square_card.tokenize()
        .then((tokenResult) => {
          if (tokenResult.status === "OK") {
            const postalCode = tokenResult.details && tokenResult.details.billing && tokenResult.details.billing.postalCode;
            this.credit_card.cardData = { billing_postal_code: postalCode };
            this.credit_card.nonce = tokenResult.token;
            this.credit_card.errors.card_form = false;
            return;
          }

          this.credit_card.errors.card_form = true;
        })
        .catch(() => {
          this.credit_card.errors.card_form = true;
        })
    },

    getFaIcon(card) {
      let icon = '';
      switch (card.card_brand) {
        case 'VISA':
          icon = 'fa-cc-visa';
          break;
        case 'MASTERCARD':
          icon = 'fa-cc-mastercard';
          break;
        case 'DISCOVER':
          icon = 'fa-cc-discover';
          break;
        default:
          icon = 'fa-cc-visa';
          break;
      }

      return icon;
    },

    changeInput(field) {
      this.credit_card.errors[field] = false;
    },

    submit() {
      this.credit_card.saving = true;
      this.error = null;

      this.validateEmail().then((isValid) => {
        if (!isValid) {
          this.credit_card.saving = false;
          return;
        }

        this.credit_card.nonce = null;
        this.requestCardNonce();

        let triesNum = 0;
        let sqTimer = setInterval(() => {
          if (!this.credit_card.nonce) {
            if (triesNum >= 5) {
              clearInterval(sqTimer);
            } else {
              triesNum++;
            }
            
            return;
          }

          let has_error = false;
          Object.keys(this.credit_card.errors).forEach((field) => {
            if (this.credit_card.errors[field] != false) {
              has_error = true;
            }
          });

          if (!has_error) {
            this.addCard();
          } else {
            this.credit_card.saving = false;
          }
          
          clearInterval(sqTimer);
        }, 1000);
      });
    },

    validateEmail() {
      return new Promise((resolve) => {
        this.$validator.validate().then(() => {
          if (this.errors.has('email')) {
            this.credit_card.errors.email = this.errors.first('email');
            resolve(false);
          } else {
            resolve(true);
          }
        });
      })
    },

    addCard() {
      let self = this;

      self.$store.dispatch('addPatientCreditCard', {
        patient_id: self.patient.id,
        nonce: self.credit_card.nonce,
        cardData: self.credit_card.cardData,
        email: self.credit_card.email
      })
        .then((response) => {
          if (response.status === 200) {
            self.fetchData();
          } else {
            this.error = response.data.errors[0].detail;
          }

          this.credit_card.saving = false;
        })
        .catch(() => {
          this.credit_card.saving = false;
        });
    }
  },
}
</script>

<style scoped lang="scss">
.forms-separator {
  margin-bottom: 50px;
  margin-top: 39px;
  background-color: #3e4855;
  height: 1px;
}

.btn-back {
  position: absolute;
  top: 21px;
}

label.control-label {
  /*padding-top:5px;*/
  text-align: left !important;
  font-size: 24px !important;
}

p.delimiter {
  font-size: 24px !important;
}

.well,
.forms-separator {
  float: none;
}

.card-email {
  font-size: 18px;

  &::-webkit-input-placeholder {
    /* Chrome/Opera/Safari */
    opacity: 0.9;
    color: #555555;
    font-size: 18px;
  }

  &::-moz-placeholder {
    /* Firefox 19+ */
    color: #555555;
    opacity: 0.9;
    font-size: 18px;
  }

  &:-ms-input-placeholder {
    /* IE 10+ */
    color: #555555;
    opacity: 0.9;
    font-size: 18px;
  }

  &:-moz-placeholder {
    /* Firefox 18- */
    color: #555555;
    opacity: 0.9;
    font-size: 18px;
  }

}

.form-group:not(.has-error) .help-block {
  display: none;
}

.cards-list {
  font-size: 18px;
}

.form-horizontal {
  &.disabled {
    pointer-events: none;
    -webkit-filter: grayscale(50%);
    /* Safari 6.0 - 9.0 */
    filter: grayscale(50%);
    /*pointer-events: none;*/
    opacity: 0.7;
  }
}
</style>