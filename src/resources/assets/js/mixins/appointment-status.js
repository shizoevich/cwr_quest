import { RESCHEDULED_APPOINTMENT_STATUS_ID } from "../settings";

export default {
    methods: {
        getAppointmentStatusName(appointment) {
            const appointmentStatusName = appointment.status.status;

            if (appointment.status.id === RESCHEDULED_APPOINTMENT_STATUS_ID) {
                return appointment.reschedule_sub_status
                    ? appointment.reschedule_sub_status.status
                    : appointmentStatusName;
            }

            return appointmentStatusName;
        },
    },
};
