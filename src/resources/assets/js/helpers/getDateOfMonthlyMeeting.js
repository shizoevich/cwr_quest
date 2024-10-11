export default function getDateOfMonthlyMeeting(date, weekday) {
    const startMonthDate = moment(date).startOf('month');
    const daysToAdd = (weekday - startMonthDate.weekday() + 7) % 7;
    return startMonthDate.add(daysToAdd, 'day');
}
