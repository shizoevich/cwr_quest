import { ProviderAvailabilityMessage } from './alert-messages.js';
import { ProviderTherapistSurveyMessage } from './alert-messages.js';
import { ProviderHasMissingNotes } from './alert-messages.js';

export default {
    week_completed:{
        status: true,
        message: ProviderAvailabilityMessage,
    },
    therapist_survey:{
        status: true,
        message: ProviderTherapistSurveyMessage
    },

    provider_missing_notes:{
        status: true,
        message: ProviderHasMissingNotes
    },

    provider_overlapped_appointments_count:{
        status: true,
        message: ''
    },
    invalid_tridiuum_credentials:{
        status: true,
        message: ''
    },

}