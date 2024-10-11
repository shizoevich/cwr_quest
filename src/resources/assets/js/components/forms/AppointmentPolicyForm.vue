<template>
    <div class="container patient-info-form" v-if="form_data">
        <div class="patient-contact-info-container">
            <div class="section section-add-note">
                <p class="text-center text-info-form">
                    This policy outlines the expectations, procedures, and requirements regarding the late cancellation
                    or no-show for a scheduled and confirmed therapy session with a CWR Therapist.
                </p>
                <div class="inp-group">
                    <div class="row info-block d-flex flex-column gap-3">
                        <div class="col-xs-12 appt-policy-block">
                            <h5 class="title-introduction">Cancellations and Rescheduled Appointments</h5>
                            <p class="text-info-form">
                                Appointment cancellations may need to occur in certain circumstances; however, patients
                                within
                                CWR are expected to attend each scheduled session on time. A cancelled or no-showed
                                appointment has a direct negative impact on patient treatment and on continuity of care.
                                Since
                                patient appointments involve reserving a time that is specifically for you, and out of
                                respect for your
                                therapist and other clients, a minimum of 24 hours’ notice is required for rescheduling.
                                Frequent
                                cancellations (3 or more in a 6-month period or 2 consecutive) will result in termination of
                                treatment.
                            </p>
                        </div>

                        <div class="col-xs-12 appt-policy-block">
                            <h5 class="title-introduction">Fees for No-Shows & Late Cancellations for All Patients (excluding Medical)</h5>
                            <p class="text-info-form">
                                <b>No-Show Fees:</b> Anytime you fail to attend a scheduled appointment without giving
                                appropriate prior
                                notice of cancellation, you will be charged $100 for the no-show session. The credit card
                                information or other payment information you previously provided will be used to process
                                this
                                payment. By providing us with your credit card information or booking an appointment, you
                                consent
                                to this policy. Multiple no-shows will result in termination of therapy.
                            </p>
                            <p class="text-info-form">
                                <b>Late Cancellation Fees:</b> Any session missed by canceling less than 24 hours in advance
                                will be
                                charged a $100 fee. You will be charged even if the cancellation is work related and even if
                                you
                                rescheduled the appointment. The credit card information you previously provided will be
                                used to
                                process this payment. By providing us with your credit card information or booking an
                                appointment,
                                you consent to this policy. Repeated late cancellations (more than two) may result in
                                termination of
                                therapy.
                            </p>

                            <h5 class="title-introduction" style="font-weight: normal;">
                                <b><i>Example:</i></b>
                            </h5>
                            <p class="text-info-form">
                                A fee of $100 will be charged when you miss or cancel an appointment without giving 24
                                hours
                                advanced notice. This means that if an appointment is scheduled for 3:00pm on a Tuesday,
                                notice
                                must be given by 3:00pm on a Monday at the absolute latest. You can cancel your
                                appointment
                                directly with your therapist.
                            </p>
                        </div>

                        <div class="col-xs-12 appt-policy-block">
                            <h5 class="title-introduction">Wait Time for All Patients (excluding Medical)</h5>
                            <p class="text-info-form">
                                Your wait time is kept to a minimum. Due to the length of time provided for each
                                appointment, it is
                                critical that you arrive on time for your appointments. If you are more than 15 minutes late
                                to your
                                appointment, we will have no choice but to reschedule your appointment and you will be
                                responsible for the $100 fee of a no-show. To avoid paying no-show fees, we require at least
                                24
                                hours’ notice for all cancellations. If your therapist is more than 10 minutes late for the
                                appointment, you will not be charged a fee.
                            </p>
                        </div>

                        <div class="col-xs-12 appt-policy-block" style="border: 1px solid;padding-top: 15px;padding-bottom: 15px;">
                            <h3 class="title-introduction" style="font-size: 24px; margin-bottom: 20px;">
                                For Medical Patients
                            </h3>
                            <h5 class="title-introduction">No-Shows & Late Cancellations</h5>
                            <p class="text-info-form">
                                When a patient no-shows it means the patient did not make contact with the therapist before
                                the
                                scheduled appointment, and did not come. Late cancellations are when a patient calls within
                                24
                                hours to cancel or reschedule an appointment. For Medical patients
                                cancellations (3 or
                                more in a 6-month period or 2 consecutive) will result in termination of treatment and
                                referral to a
                                different provider.
                            </p>
                            <h5 class="title-introduction">Wait Time</h5>
                            <p class="text-info-form">
                                Your wait time is kept to a minimum. Due to the length of time provided for each
                                appointment, it is
                                critical that you arrive on time for your appointments. If you are more than 10 minutes late
                                to your
                                appointment, we will have no choice but to reschedule your appointment and count the
                                appointment as a no-show.
                            </p>
                        </div>

                        <div class="col-xs-12 appt-policy-block">
                            <h4 class="text-center sub-title">THANK YOU!</h4>
                            <p class="text-center text-info-form">We value you as a patient and are looking forward to
                                supporting you in your treatment! We
                                can only accomplish this with your commitment to consistent attendance. Thank you for your
                                understanding.
                            </p>
                        </div>
                    </div>
                </div>

                <hr class="block-separator" />

                <div class="agreements">
                    <label class="control-label checkbox-label" style="width: fit-content">
                        <span class="checkbox-wrapper">
                            <input
                                type="checkbox"
                                class="form-control empty-field checkbox-inline custom-checkbox"
                                v-model="form_data.understand_agreements"
                            />
                        </span>
                        <b>I have read and understand the information provided above.</b>
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        data: {
            type: Object,
            required: true,
        },
    },

    data: () => ({
        form_data: null,
    }),

    mounted() {
        this.form_data = this.data;
    },

    methods: {
        validateForm() {
            if (!this.form_data.understand_agreements) {
                $(".agreements").addClass("has-error");
                this.$emit(
                    "validation-fails",
                    "Please make sure you have filled all the required fields."
                );
            } else {
                $(".agreements").removeClass("has-error");
                this.$emit("validation-success");
            }
        },
    },

    watch: {
        "form_data.understand_agreements"() {
            if (this.form_data.understand_agreements === null) {
                return;
            }

            if (!this.form_data.understand_agreements) {
                $(".agreements").addClass("has-error");
            } else {
                $(".agreements").removeClass("has-error");
            }
        },
    },
};
</script>

<style scoped>
.inp-group {
    margin-top: 40px;
}

.sub-title {
    margin-bottom: 11px
}

.appt-policy-block:not(:last-child) {
    margin-bottom: 15px;
}
</style>