export const getDateString = (date) => {
    return date.getFullYear() + '-' +
        ('0'+ (date.getMonth()+1)).slice(-2) + '-' +
        ('0'+ date.getDate()).slice(-2);
};

export const formatWeek = (startDate, endDate) => {
    const start = moment(startDate);
    const end = moment(endDate);

    if (start.isSame(end, 'month') && start.isSame(end, 'year')) {
        return `${start.format('MMMM DD')} - ${end.format('DD, YYYY')}`;
    }
    if (start.isSame(end, 'year')) {
        return `${start.format('MMMM DD')} - ${end.format('MMMM DD, YYYY')}`;
    }
    
    return `${start.format('MMMM DD YYYY')} - ${end.format('MMMM DD YYYY')}`;
};