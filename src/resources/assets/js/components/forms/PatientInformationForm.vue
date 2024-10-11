<template>
  <div class="container patient-info-form" v-if="form_data">
    <div class="patient-contact-info-container">
      <div class="section section-add-note">
        <div class="inp-group">
          <h4 class="text-center sub-title">AGREEMENT FOR SERVICE</h4>
          <h5 class="title-introduction">Introduction</h5>
          <div class="row info-block">
            <div class="col-md-8 col-xs-12">
              <p class="text-info-form">
                This Agreement is intended to provide
                <input
                  type="text"
                  class="form-control empty-input introduction-input"
                  v-model="form_data.name"
                  readonly
                />
                &nbsp;(herein "Patient") with important information regarding
                the practices, policies and procedures of Change Within Reach,
                Inc., and to clarify the terms of the professional therapeutic
                relationship between a Therapist, hired as an independent
                contractor by Change Within Reach, Inc. and Patient. Any
                questions or concerns regarding the contents of this Agreement
                should be discussed with Therapist prior to signing it.
              </p>
            </div>
            <div class="col-md-4 visible-md-block visible-lg-block">
              <div
                class="text-center view-modal"
                @click.prevent="showModal('agreement_for_service')"
              >
                <h4>Click Here to View<br />AGREEMENT FOR SERVICE</h4>
              </div>
            </div>
            <div class="col-xs-12">
              <h5 class="title-introduction">Notice to psychotherapy clients</h5>
              <p class="text-info-form">
                The Board of Behavioral Sciences receives and responds to
                complaints regarding services provided within the scope of
                practice of (marriage and family therapists, licensed
                educational psychologists, clinical social workers, or
                professional clinical counselors). You may contact the board
                online at
                <a href="https://www.bbs.ca.gov" target="_blank"
                >www.bbs.ca.gov</a
                >, or by calling <a href="tel:+9165747830">(916) 574-7830</a>
              </p>
            </div>
            <div class="col-xs-12 visible-xs-block visible-sm-block">
              <div
                class="text-center view-modal"
                @click.prevent="showModal('agreement_for_service')"
              >
                <h4>Click Here to View<br />AGREEMENT FOR SERVICE</h4>
              </div>
            </div>
          </div>

          <h4 class="text-center sub-title">HIPAA PRIVACY NOTICE</h4>
          <div class="row info-block">
            <div class="col-md-8 col-xs-12">
              <p class="text-info-form">
                I,
                <input
                  type="text"
                  class="form-control empty-input charges-input"
                  v-model="form_data.name"
                  readonly
                />, have received a copy of this office&#039;s Notice of Privacy
                Practices. I understand that I have certain rights to privacy
                regarding my protected health information. I understand that
                this information can and will be used to:
                <br />
                &#9679;&nbsp;&nbsp;&nbsp;&nbsp; Conduct, plan and direct my
                treatment and follow-up among the health care providers who may
                be directly and indirectly involved in providing my treatment.
                <br />
                &#9679;&nbsp;&nbsp;&nbsp;&nbsp; Obtain payment from third-party
                payers.
                <br />
                &#9679;&nbsp;&nbsp;&nbsp;&nbsp; Conduct normal health care
                operations such as quality assessments and accreditation.
              </p>
            </div>
            <div class="col-md-4 col-xs-12">
              <div
                class="text-center view-modal"
                @click.prevent="showModal('hipaa_privacy_notice')"
              >
                <h4>Click Here to View<br />HIPAA PRIVACY NOTICE</h4>
              </div>
            </div>
          </div>

          <h4 class="text-center sub-title">PATIENT RIGHTS</h4>
          <div class="row info-block info-block--no-mb">
            <div class="col-md-8 col-xs-12">
              <p class="text-info-form">
                At Change Within Reach, Inc. our pledge to you as our client, is
                to provide you with quality care for the services you are
                seeking. We believe that a successful component in receiving
                psychological services is a trusting, working relationship with
                your assigned provider, one in which you feel comfortable and
                your needs and concerns are being met.
              </p>
            </div>
            <div class="col-md-4 col-xs-12">
              <div
                class="text-center view-modal"
                @click.prevent="showModal('patient_rights')"
              >
                <h4>Click Here to View<br />YOUR PATIENT RIGHTS</h4>
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
                I understand that checking this box constitutes a legal
                signature confirming that I acknowledge and agree to the Terms
                and Conditions stated in these agreements.
              </b>
            </label>
          </div>
        </div>
      </div>
    </div>

    <modal
      name="agreement_for_service"
      :title="agreement_for_service.title"
      :body="agreement_for_service.body"
    ></modal>
    <modal
      name="patient_rights"
      :title="modals.patient_rights.title"
      :body="modals.patient_rights.body"
    ></modal>
    <modal
      name="hipaa_privacy_notice"
      :title="modals.hipaa_privacy_notice.title"
      :body="modals.hipaa_privacy_notice.body"
    ></modal>
  </div>
</template>

<script>
export default {
  props: {
    data: {
      type: Object,
    },
  },
  data: () => ({
    form_data: null,
    modals: {
      patient_rights: {
        title: "PATIENT RIGHTS",
        body:
          "<b>As a patient of Change Within Reach, Inc. you have the right to:</b>" +
          "<ul>" +
          "<li>Speak freely and privately with your health care provider about all of your concerns related to treatment.</li>" +
          "<li>Receive treatment that is available to you when you need it.</li>" +
          "<li>Be treated with dignity and respect.</li>" +
          "<li>Expect that your personal health information will be kept private by following our privacy policies, as well as State and Federal laws.</li>" +
          "<li>Be an active participant with your health care professional in decisions related to your medical condition and treatment options.</li>" +
          "<li>Say no to care, for any condition, without it having an effect on any care you may receive in the future. This includes asking your provider to tell you how that may affect you now and in the future.</li>" +
          "</ul>" +
          "<p><b>If at any time you feel that your provider is not meeting your needs by not treating the condition in which you sought services for, you have the right to discuss this with your provider first. If after speaking with your provider, you still do not feel that your needs have been met, you may contact our quality assurance team at 213-908-1234 at any time to have your voice heard and to be reassigned to another provider within our group practice.</b></p>",
      },
      hipaa_privacy_notice: {
        title: "HIPAA PRIVACY NOTICE",
        body:
          "<img src='../../images/hipaa_privacy_notice/1.png' class='img-responsive'>" +
          "<img src='../../images/hipaa_privacy_notice/2.png' class='img-responsive'>" +
          "<img src='../../images/hipaa_privacy_notice/3.png' class='img-responsive'>" +
          "<img src='../../images/hipaa_privacy_notice/4.png' class='img-responsive'>" +
          "<img src='../../images/hipaa_privacy_notice/5.png' class='img-responsive'>" +
          "<img src='../../images/hipaa_privacy_notice/6.png' class='img-responsive'>" +
          "<img src='../../images/hipaa_privacy_notice/7.png' class='img-responsive'>",
      },
    },
  }),
  computed: {
    agreement_for_service() {
      return {
        title: "AGREEMENT FOR SERVICE",
        body:
          "<b>Introduction</b>" +
          "<p>This Agreement is intended to provide&nbsp;<u>&nbsp;&nbsp;" +
          this.form_data.name +
          '&nbsp;&nbsp;</u>&nbsp;(herein "Patient") with important information regarding the practices, policies and procedures of Change Within Reach, Inc., and to clarify the terms of the professional therapeutic relationship between a Therapist, hired as an independent contractor by Change Within Reach, Inc. and Patient. Any questions or concerns regarding the contents of this Agreement should be discussed with Therapist prior to signing it.</p>' +
          "<b>Notice to psychotherapy clients</b>" +
          "<p>The Board of Behavioral Sciences receives and responds to complaints regarding services provided within the scope of practice of (marriage and family therapists, licensed educational psychologists, clinical social workers, or professional clinical counselors). You may contact the board online at <a href=\"https://www.bbs.ca.gov\" target=\"_blank\">www.bbs.ca.gov</a>, or by calling <a href=\"tel:+9165747830\">(916) 574-7830</a></p>" +
          "<b>Risks and Benefits of Therapy</b>" +
          "<p>Psychotherapy is a process in which Therapist and Patient discuss a myriad of issues, events, experiences and memories for the purpose of creating positive change so Patient can experience his/her life more fully. It provides an opportunity to better, and more deeply understand oneself, as well as any problems or difficulties Patient may be experiencing. Psychotherapy is a joint effort between Patient and Therapist. Progress and success may vary depending upon the particular problems or issues being addressed, as well as many other factors.</p>" +
          "<p>Participating in therapy may result in a number of benefits to Patient, including, but not limited to, reduced stress and anxiety, a decrease in negative thoughts and self-sabotaging behaviors, improved interpersonal relationships, increased comfort in social, work, and family settings, increased capacity for intimacy, and increased self-confidence. Such benefits may also require substantial effort on the part of Patient, including an active participation in the therapeutic process, honesty, and a willingness to change feelings, thoughts and behaviors. There is no guarantee that therapy will yield any or all of the benefits listed above.</p>" +
          "<p>Participating in therapy may also involve some discomfort, including remembering and discussing unpleasant events, feelings and experiences. The process may evoke strong feelings of sadness, anger, fear, etc. There may be times in which Therapist will challenge Patient&#039;s perceptions and assumptions, and offer different perspectives. The issues presented by Patient may result in unintended outcomes, including changes in personal relationships. Patient should be aware that any decision on the status of his/her personal relationships is entirely the responsibility of Patient.</p>" +
          "<p>During the therapeutic process, many Patients find that they feel worse before they feel better. This is generally a normal course of events. Personal growth and change may be easy and swift at times, but may also be slow and frustrating. Patient should address any concerns he/she has regarding his/her progress in therapy with Therapist.</p>" +
          "<b>Patient Safety and Conduct</b>" +
          "<p>It is okay to express your anger in a therapy session, but shouting and throwing things is never appropriate. While your privacy is of utmost concern, you should be aware that any incidents of abuse or threats to others must be reported. If you feel that you may harm yourself in any way, you should discuss this immediately with your Therapist. Suicidal threats may result in notifying the Patient&#039;s emergency contact and other people who can keep you safe. You safety is the number one concern. It is never appropriate to bring any form of weapon, alcohol, or illegal or dangerous substance or item into therapy, and Patients who do so will be asked to leave immediately.</p>" +
          "<p>Please do not bring children under 12 to wait unsupervised while you are in therapy. Please do not bring pets into the building with the exception of service dogs. This is a smoke-free building and any kind of smoking, including e-cigarettes, is not allowed on the property. In the event of severe weather, please contact the office to see whether it is open. For ethical reasons, your Therapist does not accept gifts of any kind.</p>" +
          "<b>Professional Consultation</b>" +
          "<p>Professional consultation is an important component of a healthy psychotherapy practice. As such, Therapist regularly participates in clinical, ethical, and legal consultation with appropriate professionals. During such consultations, Therapist will not reveal any personally identifying information regarding Patient or Patient&#039;s family members or caregivers.</p>" +
          "<b>Records and Record Keeping</b>" +
          "<p>Therapist may take notes during session, and will also produce other notes and records regarding Patient&#039;s treatment. These notes constitute Therapist&#039;s clinical and business records, which by law, Therapist is required to maintain. Such records are the sole property of Therapist. Therapist will not alter his/her normal record keeping process at the request of any Patient. Should Patient request a copy of Therapist&#039;s records, such a request must be made in writing. Therapist reserves the right, under California law, to provide Patient with a treatment summary in lieu of actual records. Therapist also reserves the right to refuse to produce a copy of the record under certain circumstances, but may, as requested, provide a copy of the record to another treating health care provider. Therapist also reserves the right to charge a reasonable fee for each instance of copying and/or mailing of any portion(s) of Patient&#039;s records. Therapist will maintain Patient&#039;s records for ten years following termination of therapy. However, after ten years, Patient&#039;s records will be destroyed in a manner that preserves Patient&#039;s confidentiality.</p>" +
          "<p>Change Within Reach uses external providers to enhance services including the Upheal platform. Upheal empowers counselors to concentrate on their services by offering automated notes and analytics for client conversations. As a part of this process, Upheal handles protected health information for counselors, adhering to HIPAA regulations as a Business Associate.</p>" +
          "<p>Change Within Reach has signed a Business Associate Agreement (BAA) to protect data that is shared with Upheal. Under the BAA, Upheal adheres to regulations such as the HIPAA Security Rule and Privacy Rule. This ensures that electronic health information (ePHI) is safeguarded through appropriate administrative, physical, and technical measures, ensuring its confidentiality, integrity, and security. You can learn more about Upheal and its privacy practices at <a href='https://www.upheal.io/privacy' target='_blank'>www.upheal.io/privacy</a>.</p>" +
          "<b>Confidentiality</b>" +
          "<p>The information disclosed by Patient is generally confidential and will not be released to any third party without written authorization from Patient, except where required or permitted by law. Exceptions to confidentiality, include, but are not limited to, reporting child, elder and dependent adult abuse, when a Patient makes a serious threat of violence towards a reasonably identifiable victim, or when a Patient is dangerous to him/herself or the person or property of another. In addition, a federal law known as The Patriot Act of 2001 requires Therapists (and others) in certain circumstances, to provide FBI agents with books, records, papers and documents and other items and prohibits the Therapist from disclosing to the Patient that the FBI sought or obtained the items under the Act. If you would like to bring a friend or family member to a session, please notify your Therapist at least one week in advance. You will be asked to sign a release giving your Therapist permission to talk about issues that may be confidential.</p>" +
          '<p>If you participate in marital or family therapy, your Therapist will not disclose confidential information about your treatment unless all person(s) who participated in the treatment with you provide their written authorization to release such information. However, it is important that you know that your Therapist utilizes a "no-secrets" policy when conducting family or marital/couples therapy. This means that if you participate in family, and/or marital/couples therapy, your Therapist is permitted to use information obtained in an individual session that you may have had with him or her, when working with other members of your family. Please ask your Therapist about his or her "no secrets" policy and how it may apply to you.</p>' +
          "<b>Minors and Confidentiality</b>" +
          "<p>Communications between Therapists and Patients who are minors (under the age of 15) are confidential. However, parents and other guardians who provide authorization for their child&#039;s treatment are often involved in their treatment. Consequently, Therapist, in the exercise of his or her professional judgment, may discuss the treatment progress of a minor Patient with the parent or caretaker. Patients who are minors and their parents are urged to discuss any questions or concerns that they have on this topic with their Therapist.</p>" +
          "<b>Patient Litigation</b>" +
          "<p>Therapist will not voluntarily participate in any litigation, or custody dispute in which Patient and another individual, or entity, are parties. Therapist has a policy of not communicating with Patient&#039;s attorney and will generally not write or sign letters, reports, declarations, or affidavits to be used in Patient&#039;s legal matters. Therapist will generally not provide records or testimony unless compelled to do so. Should Therapist be subpoenaed, or ordered by a court of law, to appear as a witness in an action involving Patient, Patient agrees to reimburse Therapist for any time spent for preparation, travel, or other time in which Therapist has made him/herself available for such an appearance at Therapist&#039;s usual and customary hourly rate of $200.00.</p>" +
          "<b>Psychotherapist-Patient Privilege</b>" +
          "<p>The information disclosed by Patient, as well as any records created, is subject to the Psychotherapist-Patient privilege. The Psychotherapist-Patient privilege results from the special relationship between Therapist and Patient in the eyes of the law. It is akin to the attorney-Patient privilege or the doctor-Patient privilege. Typically, the Patient is the holder of the Psychotherapist-Patient privilege. If Therapist received a subpoena for records, deposition testimony, or testimony in a court of law, Therapist will assert the Psychotherapist-Patient privilege on Patient&#039;s behalf until instructed, in writing, to do otherwise by Patient or Patient&#039;s representative. Patient should be aware that he/she might be waiving the Psychotherapist-Patient privilege if he/she makes his/her mental or emotional state an issue in a legal proceeding. Patient should address any concerns he/she might have regarding the Psychotherapist-Patient privilege with his/her attorney.</p>" +
          "<b>Fee and Fee Arrangements for Private Pay Patients</b>" +
          "<p>The usual and customary fee for service is $200.00 per 50-minute session. Sessions longer than 50-minutes are charged for the additional time pro rata in 15-minute increments. Therapist reserves the right to periodically adjust this fee. Patient will be notified of any fee adjustment in advance. In addition, this fee may be adjusted by contract with insurance companies, managed care organizations, or other third-party payors, or by agreement with Therapist. I understand it is my responsibility to arrive on time for all sessions. I understand that if I am late to a session, that session will still end at the time originally scheduled. Exceptions to this policy may be made at the Therapist&#039;s discretion if time allows.</p>" +
          "<p>Therapist may engage in telephone contact with Patient for purposes other than scheduling sessions. Patient is responsible for payment of the agreed upon fee (on a pro rata basis) for any telephone calls. In addition, Therapist may engage in telephone contact with third parties at Patient&#039;s request and with Patient&#039;s advance written authorization. Services including any/all phone calls, emails, record reviews, and professional consults at times other than scheduled therapy sessions are the Patient&#039;s responsibility. These services will be billed in 15-minute increments at the agreed upon fee. From time to time, at the agreement of both Therapist and Patient, therapeutic services may be provided outside the office by phone or video. In these circumstances, the same fees for treatment will apply as for in-office sessions.</p>" +
          "<p>Patients are expected to pay for services at the time services are rendered. Therapist accepts cash, checks, and major credit cards, including Visa and MasterCard. Checks returned for insufficient funds are subject to a $40.00 fee, per check.</p>" +
          "<b>Therapist Availability</b>" +
          "<p>Therapist&#039;s office is equipped with a confidential voice mail system that allows Patient to leave a message at any time. Therapist will make every effort to return calls within 24 hours (or by the next business day), but cannot guarantee the calls will be returned immediately. Therapist is unable to provide 24-hour crisis service. In the event that Patient is feeling unsafe or requires immediate medical or psychiatric assistance, he/she should call 911, or go to the nearest emergency room.</p>" +
          "<b>Termination of Therapy</b>" +
          "<p>Therapist reserves the right to terminate therapy at his/her discretion. Reasons for termination include, but are not limited to, untimely payment of fees, failure to comply with treatment recommendations, conflicts of interest, failure to participate in therapy, Patient needs are outside of Therapist&#039;s scope of competence or practice, or Patient is not making adequate progress in therapy. Patient has the right to terminate therapy at his/her discretion. Upon either party&#039;s decision to terminate therapy, Therapist will generally recommend that Patient participate in at least one, or possibly more, termination sessions. These sessions are intended to facilitate a positive termination experience and give both parties an opportunity to reflect on the work that has been done. Therapist will also attempt to ensure a smooth transition to another Therapist by offering referrals to Patient.</p>" +
          "<b>Acknowledgement</b>" +
          "<p>By signing below, Patient acknowledges that he/she has reviewed and fully understands the terms and conditions of this Agreement. Patient has discussed such terms and conditions with Therapist, and has had any questions with regard to its terms and conditions answered to Patient&#039;s satisfaction. Patient agrees to abide by the terms and conditions of this Agreement and consents to participate in psychotherapy with Therapist. Moreover, Patient agrees to hold Therapist free and harmless from any claims, demands, or suits for damages from any injury or complications whatsoever, save negligence, that may result from such treatment.</p>",
      };
    },
  },
  mounted() {
    this.form_data = this.data;
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
