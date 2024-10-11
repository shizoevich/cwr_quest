export default {
    props: {
        patient: {
            type: Object,
            default() {
                return {};
            }
        },
        isValidSendForm: {
            type: Boolean,
            default: false,
        },
        errorSendFormMessage: {
            type: String,
            default: ''
        },
    },
    methods: {
        currentTime() {
            return moment().startOf('day').format('MM/DD/yyyy hh:mm A');
        },
        appointmentTime({date, time}) {
            return date + ' ' + time;
        },
        appointmentInvite(googleMeetData) {
            let type = 0,
                time = '';
            if (googleMeetData === undefined || googleMeetData === null || googleMeetData.invitations === null || !googleMeetData.invitations.length) {
                type = 0;
            } else if (googleMeetData.invitations && googleMeetData.invitations.length) {
                const sentInvitation = googleMeetData.invitations.find(invitation => invitation.sent_at !== null);
                if (sentInvitation) {
                    type = 1;
                    time = this.formattedTime(sentInvitation.sent_at);
                } else {
                    const sendInvitation = googleMeetData.invitations.find(invitation => invitation.send_at !== null);
                    if (sendInvitation) {
                        type = 2;
                        time = this.formattedTime(sendInvitation.send_at);
                    }
                }
            }
            
            switch (type) {
                case 0:
                    return `<span class="text text-danger">Invitation Not Sent</span>`;
                case 1:
                    return `<span class="text text-success">Invitation Sent at ${time}</span>`;
                case 2:
                    return `<span class="text text-primary">Invitation will be sent on ${time}</span>`;
            }
        },
        formattedTime(timeData) {
            return moment(timeData).format('MM/DD/YYYY hh:mm A');
        },
    }
}
