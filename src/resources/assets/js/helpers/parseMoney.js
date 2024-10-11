export const parseMoney = (value) => {
    return value ? parseFloat(value.replace(',', '')) : 0;
};

export const isMoneyRoundString = (value) => {
    return /^\d{1,3}(,\d{3})+(\.\d+)?$/.test(value);
};
