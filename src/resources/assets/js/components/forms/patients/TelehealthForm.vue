<template>
  <div class="container patient-info-form" v-if="form_data">
    <div class="patient-contact-info-container">
      <div class="section section-add-note">
        <div class="">
          <div class="row info-block">
            <div class="col-md-8 col-xs-12">
              <p class="text-info-form">
                I
                <input
                  type="text"
                  class="form-control empty-input charges-input"
                  v-model="form_data.name"
                  readonly
                />
                &nbsp; [name of patient(s)] hereby consent to engaging in
                TeleMental health with Change Within Reach, Licensed Clinical
                Social Worker Corporation as part of my psychotherapy. I
                understand that “TeleMental health” includes the practice of
                health care delivery, diagnosis, consultation, treatment,
                transfer of mental health data, and education using interactive
                audio, video, or data communications. I understand that
                TeleMental health also involves the communication of my
                medical/mental information, both orally and visually, to health
                care practitioners located in California or outside of
                California.
              </p>
              <div class="text-info-form">
                <label class="control-label checkbox-label">
                  In case of emergency my location is:
                  <br/>
                  <input type="text" class="form-control" style="width:100%;" v-model="form_data.home_address">
                </label>
              </div>
              <div class="text-info-form">
                <label class="control-label checkbox-label">
                  and contact information for local emergency individual is:
                  <input type="text" class="form-control" style="width:100%;" v-model="form_data.emergency_contact">
                </label>
              </div>
            </div>
            <div class="col-md-4 col-xs-12">
              <div
                class="text-center view-modal"
                @click.prevent="showModal('telemental')"
              >
                <h4>Click Here to View<br />TeleMental Health Services</h4>
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
              <b>
                I have read and understand the information provided above. I
                have discussed it with my psychotherapist, and all of my
                questions have been answered to my satisfaction.
              </b>
            </label>
          </div>
        </div>
      </div>
    </div>

    <modal name="telemental" :title="modals.telemental.title" with-slot>
      <template v-slot:body>
        <p>
          The following information is provided to clients who are seeking
          TeleMental health therapy. This document covers your rights, risks and
          benefits associated with receiving TeleMental Health services. Please
          read this document carefully, note any questions you would like to
          discuss, and sign. <br />
          TeleMental Health Defined: TeleMental health means the remote
          delivering of health care services via technology-assisted media. This
          includes a wide array of clinical services and various forms of
          technology. The technology includes but is not limited to video,
          internet, a smartphone, tablet, PC desktop system or other electronic
          means. The delivery method must be secured by two way encryption to be
          considered secure. Synchronous (at the same time) secure video
          chatting is the preferred method of service delivery.
        </p>
        <p>
          Limitations of TeleMental Health Therapy Services: While TeleMental
          health offers several advantages such as convenience and flexibility.
          It is an alternative form of therapy or adjunct to therapy and thus
          may involve disadvantages and limitations. For example, there may be a
          disruption to the service (e.g. video drops). This can be frustrating
          and interrupt the normal flow of personal interaction. Primarily,
          there is a risk of misunderstanding one another when communication
          lacks visual or auditory cues. For example, if video quality is
          lacking for some reason, therapist might not see various details such
          as facial expressions. Or, if audio quality is lacking, therapist
          might not hear differences in your tone of voice that could be easily
          picked up if you were in the office. Additionally, the therapy office
          decreases the likelihood of interruptions. However, there are ways to
          minimize interruptions and maximize privacy and effectiveness. The
          therapist will take every precaution to ensure a technologically
          secure and environmentally private psychotherapy sessions. As the
          client, you are responsible for finding a private quiet location where
          the sessions may be conducted. Consider using a “do not disturb”
          sign/note on the door. The virtual sessions must be conducted on a
          Wi-Fi connection for the best connection and to minimize disruption.
        </p>
        <p>
          In Case of Technology Failure: I understand that during a TeleMental
          health session we could encounter a technological failure.
          Difficulties with hardware, software, equipment, and/or services
          supplied by a 3rd party may result in service interruptions. If
          something occurs to prevent or disrupt any scheduled appointment due
          to technical complications and the session cannot be completed via
          online video conferencing, please call your therapist at the number
          provided for you by the therapist. Please make sure you have a phone
          with you, and therapist has that phone number. We may also reschedule
          if there are problems with connectivity.
        </p>
        <p>
          I understand that I have the following rights with respect to
          TeleMental health: <br />
          (1) I have the right to withhold or withdraw consent at any time
          without affecting my right to future care or treatment nor risking the
          loss or withdrawal of any program benefits to which I would otherwise
          be entitled. <br />
          (2) The laws that protect the confidentiality of my medical and mental
          health information also apply to TeleMental health. As such, I
          understand that the information disclosed by me during the course of
          my therapy is generally confidential. However, there are both
          mandatory and permissive exceptions to confidentiality, including, but
          not limited to reporting child, elder, and dependent adult abuse;
          expressed threats of violence towards self and/or an ascertainable
          victim; and where I make my mental or emotional state an issue in a
          legal proceeding.
        </p>
        <p>
          I understand that the therapist may contact my emergency contact
          and/or appropriate authorities in case of emergency. I also understand
          that the dissemination of any personally identifiable images or
          information from the TeleMental health interaction to researchers or
          other entities shall not occur without my written consent. <br />
          (3) I understand that there are risks and consequences from TeleMental
          health, including, but not limited to, the possibility, despite
          reasonable efforts on the part of my psychotherapist, that: the
          transmission of my medical or mental health information could be
          disrupted or distorted by technical failures; the transmission of my
          medical or mental health information could be interrupted by
          unauthorized persons; the electronic storage of my medical information
          could be accessed by unauthorized persons; and/or limited ability to
          respond to emergencies. In addition, I understand that TeleMental
          health-based services and care may not be as complete as face-to-face
          services. I also understand that if my psychotherapist believes I
          would be better served by another form of psychotherapeutic services
          (e.g. face-to-face services) I will switch to utilizing the services
          that are recommended as more effective. Finally, I understand that
          there are potential risks and benefits associated with any form of
          psychotherapy, and that despite my efforts and the efforts of my
          psychotherapist, my condition may not be improved, and in some cases
          may even get worse.<br />
          (4) I understand that I may benefit from TeleMental health, but that
          results cannot be guaranteed or assured.<br />
          (5) I understand that I have the right to access my medical and mental
          health information and copies of medical records in accordance with
          California law.
        </p>
      </template>
    </modal>
  </div>
</template>

<script>
import phoneFormartter from "../../../mixins/phone-format.js";

export default {
  mixins: [phoneFormartter],
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    form_data: null,
    modals: {
      telemental: {
        title: "TeleMental Health Services",
        body: "",
      },
    },
  }),
  mounted() {
    this.form_data = this.data;
  },
  computed: {
    emergencyContact() {
      let contact = "";
      if (
        this.form_data.emergency_contact &&
        this.form_data.emergency_contact_phone
      ) {
        contact = `${this.form_data.emergency_contact}, ${this.getFullUsFormat(
          this.form_data.emergency_contact_phone
        )}`;
      } else if (
        this.form_data.emergency_contact &&
        !this.form_data.emergency_contact_phone
      ) {
        contact = this.form_data.emergency_contact;
      } else if (
        !this.form_data.emergency_contact &&
        this.form_data.emergency_contact_phone
      ) {
        contact = this.getFullUsFormat(this.form_data.emergency_contact_phone);
      } else {
        contact = "";
      }

      return contact;
    },
  },
  methods: {
    showModal(name) {
      $("#" + name).modal("show");
    },
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

<style></style>
