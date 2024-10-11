export default {
    methods: {
        getFormattedDateTime(date) {
            return this.$moment(date).format('MM/DD/YYYY hh:mm A');
        },

        getFormattedTime(date) {
            return this.$moment(date).format('h:mm A');
        },

        getFormattedDate(date) {
            return this.$moment(date).format('DD MMM. YYYY');
        },

        getFormattedDateWithDayOfWeek(date) {
            return this.$moment(date).format('dddd, D MMM. YYYY');
        },

        getFormattedDateSimple(date) {
            return this.$moment(date).format('MM/DD/YYYY');
        },

        getCommentTime(date, nowrap, nowrap_all) {

            if (typeof nowrap === 'undefined' || !nowrap) {
                return this.$moment(date).format('DD MMM. YYYY, h:mm A');
            } else {
                let s = '';

                if (typeof nowrap_all === 'undefined' || !nowrap_all) {
                    s += '<nobr>'+this.$moment(date).format('DD MMM. YYYY') + '</nobr>';
                    s += ', ';
                    s += '<nobr>'+this.$moment(date).format('h:mm A') + '</nobr>';
                } else {
                    s += '<nobr>'+this.$moment(date).format('DD MMM. YYYY, h:mm A') + '</nobr>';
                }

                return s;
            }
        },

        getDurationFormat(duration) {
            if (duration) {
                return this.$moment.utc(duration).format('HH:mm:ss');
            }

            return '00:00:00';
        }
    }
}