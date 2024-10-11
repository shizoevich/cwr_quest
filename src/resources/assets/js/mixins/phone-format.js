import { parsePhoneNumberFromString, findNumbers, parsePhoneNumber } from 'libphonenumber-js';
export default {
    methods: {
        getUsFormat(phone) {
            if(phone) {
                let phoneTo = findNumbers(
                        phone, 
                        'US', 
                        {v2: true}
                    );
                    if (phoneTo.length > 0) {
                        if(phoneTo[0].number.country == 'US') {
                            return parsePhoneNumber(phoneTo[0].number.nationalNumber, "US").formatNational();
                            
                        } else {
                            return phone;
                        }
                    } else {
                        return phone;
                    }
            } else {
                return phone;
            }
        },

        getFullUsFormat(phone) {
            if(phone) {
                let phoneTo = findNumbers(
                        phone, 
                        'US', 
                        {v2: true}
                    );
                    if (phoneTo.length > 0) {
                        if(phoneTo[0].number.country == 'US') {
                            return parsePhoneNumber(phoneTo[0].number.nationalNumber, "US").formatInternational();
                            
                        } else {
                            return phone;
                        }
                    } else {
                        return phone;
                    }
            } else {
                return phone;
            }
        }
    }
}