export default {
    methods: {
        getPatientBalance(patient) {
            if(patient.balance) {
                return patient.balance.balance / 100;
            }

            return 0;
        },

        getPatientPreprocessedBalance(patient) {
            if(patient.preprocessed_balance) {
                return patient.preprocessed_balance.balance / 100;
            }

            return 0;
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

        getFormattedPatientBalanceHTML(balance, formatted = true) {
            let className = 'text-red';
            if(balance >= 0) {
                className = 'text-green';
            }
            if(formatted) {
                balance = this.getFormattedMoney(balance, false);
            }

            return '<span class="' + className + '">' + balance + '</span>';
        },

        getLocaleFormattedBalance({amount, options, locale = 'en-US', currency = '$'}) {
            if (!amount) {
                let zero = 0;
                return `${currency}${zero.toLocaleString(locale, options)}`;
            }

            let sign = '';
            if (amount < 0) {
                amount *= -1;
                sign = '-';
            }

            return `${sign}${currency}${amount.toLocaleString(locale, options)}`;
        }
    }
}