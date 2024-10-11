const DEDUCTIBLE_FIELD_NAME = "deductible";
const DEDUCTIBLE_MET_FIELD_NAME = "deductible_met";
const DEDUCTIBLE_REMAINING_FIELD_NAME = "deductible_remaining";

export default {
    methods: {
        handleInputNumberBlur(dataObjectName, fieldName) {
            const fieldValue = this[dataObjectName][fieldName]
                .toString()
                .replace(/^0+/, "");
            this[dataObjectName][fieldName] =
                this.formatNumberWithTwoDecimalPlaces(fieldValue);

            const currentFieldValue = this[dataObjectName][fieldName];
            if (!currentFieldValue || currentFieldValue <= 0) {
                this[dataObjectName][fieldName] = 0;
            }

            this.updateDeductibleFieldsIfNeeded(dataObjectName, fieldName);
        },

        updateDeductibleFieldsIfNeeded(dataObjectName, fieldName) {
            const deductibleFields = [
                DEDUCTIBLE_FIELD_NAME,
                DEDUCTIBLE_MET_FIELD_NAME,
                DEDUCTIBLE_REMAINING_FIELD_NAME,
            ];

            if (!deductibleFields.includes(fieldName)) {
                return;
            }

            let currentFieldValue = Number(this[dataObjectName][fieldName]);

            const { deductible } = this[dataObjectName];

            if (currentFieldValue > deductible) {
                this[dataObjectName][fieldName] = deductible;
            }

            this[dataObjectName][fieldName] = this.formatNumberWithTwoDecimalPlaces(
                this[dataObjectName][fieldName],
            );

            const sum = this.formatNumberWithTwoDecimalPlaces(
                Number(this[dataObjectName].deductible_met) +
                    Number(this[dataObjectName].deductible_remaining),
            );
            if (
                fieldName === DEDUCTIBLE_FIELD_NAME &&
                this[dataObjectName][fieldName] !== sum
            ) {
                this[dataObjectName].deductible_met = 0;
                this[dataObjectName].deductible_remaining = deductible;
            }

            if (fieldName === DEDUCTIBLE_MET_FIELD_NAME) {
                this[dataObjectName].deductible_remaining =
                    this.formatNumberWithTwoDecimalPlaces(
                        deductible - this[dataObjectName][fieldName],
                    );
            }

            if (fieldName === DEDUCTIBLE_REMAINING_FIELD_NAME) {
                this[dataObjectName].deductible_met =
                    this.formatNumberWithTwoDecimalPlaces(
                        deductible - this[dataObjectName][fieldName],
                    );
            }
        },

        formatNumberWithTwoDecimalPlaces(value) {
            return Number(value).toFixed(2);
        },

        handleWheel(e) {
            return e.target.blur();
        }
    },
};
