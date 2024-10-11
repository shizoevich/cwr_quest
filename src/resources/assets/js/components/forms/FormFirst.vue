<!--Form Name: "Informed Consent, Payments & HIPAA Combined"-->
<template>
  <div class="container">
    <div
      class="modal modal-vertical-center fade"
      id="co-pay-modal"
      data-backdrop="static"
      data-keyboard="false"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">
              PAYMENT FOR SERVICE AND FEE ARRANGEMENTS
            </h4>
          </div>
          <div class="modal-body">
            <div class="row co-pay-row">
              <label class="col-xs-8"
                >Co-pay and/or co-insurance for session:</label
              >
              <div class="col-xs-4">
                <input
                  id="co_pay"
                  type="text"
                  class="form-control"
                  v-model="form_data.co_pay"
                />
              </div>
            </div>
            <div class="row co-pay-row">
              <label class="col-xs-8"
                >Payment for session not covered due to deductible:</label
              >
              <div class="col-xs-4">
                <input
                  type="text"
                  class="form-control"
                  v-model="form_data.payment_for_session_not_converted"
                />
              </div>
            </div>
            <div class="row co-pay-row">
              <label class="col-xs-8"
                >Self-pay for session when paid out-of-pocket:</label
              >
              <div class="col-xs-4">
                <input
                  id="self_pay"
                  type="text"
                  class="form-control"
                  v-model="form_data.self_pay"
                />
              </div>
            </div>
            <div class="row co-pay-row">
              <label class="col-xs-8"
                >Charge for cancellation without 24 hours’ notice:</label
              >
              <div class="col-xs-4">
                <input
                  id="charge_for_cancellation"
                  type="text"
                  class="form-control"
                  v-model="form_data.charge_for_cancellation"
                  disabled
                />
              </div>
            </div>
            <div class="row co-pay-row">
              <label class="col-xs-5 col-sm-4 col-md-3"
                >Other charges [specify]:</label
              >
              <div class="col-xs-3 col-sm-4 col-md-5">
                <input
                  type="text"
                  class="form-control"
                  v-model="form_data.other_charges"
                />
              </div>
              <div class="col-xs-4">
                <input
                  type="text"
                  class="form-control"
                  v-model="form_data.other_charges_price"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <span class="text-red validation-error-msg">{{
              validation_messages.current
            }}</span>
            <button
              type="button"
              class="btn btn-primary"
              @click.prevent="verifyCoPay"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="patient-contact-info-container"
      v-if="co_pay_entered && !hide_form"
    >
      <div class="section section-add-note">
        <form
          class="form-note form-horizontal patient-contact-info-form"
          autocomplete="off"
          id="form-note"
          novalidate
        >
          <h3 class="text-center header-part header-part-first">
            Patient Contact Information
          </h3>
          <div class="inp-group">
            <div class="row">
              <div class="col-xs-7">
                <label class="control-label col-md-2 col-lg-1 pf-label"
                  >Name:</label
                >
                <div class="col-md-10 col-lg-11 pci-form-group">
                  <input
                    type="text"
                    class="form-control empty-input"
                    v-model="form_data.name"
                    readonly
                  />
                </div>
              </div>
              <!--date of birth-->
              <div class="col-xs-5">
                <label class="control-label col-md-4 col-lg-3 pf-label"
                  >Date of Birth:</label
                >
                <div class="col-md-8 col-lg-9 pci-form-group">
                  <input
                    type="text"
                    class="form-control empty-input"
                    v-model="form_data.date_of_birth"
                    readonly
                  />
                </div>
              </div>
            </div>
            <div class="row pci-row">
              <!--home address-->
              <div class="col-xs-12">
                <label class="control-label col-md-2 pf-label"
                  >Home Address:</label
                >
                <div class="col-md-10 pci-form-group">
                  <input
                    id="home_address"
                    type="text"
                    class="form-control empty-input"
                    v-model="form_data.home_address"
                    @change="requiredOnChange('home_address')"
                    autocomplete="new-password"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="row pci-row">
              <!--city-->
              <div class="col-xs-3">
                <label class="control-label col-md-3 col-lg-2 pf-label"
                  >City:</label
                >
                <div class="col-md-9 col-lg-10 pci-form-group">
                  <input
                    id="city"
                    type="text"
                    class="form-control empty-input"
                    autocomplete="new-password"
                    v-model="form_data.city"
                    @change="requiredOnChange('city')"
                    required
                  />
                </div>
              </div>
              <div class="col-xs-2 pci-form-group">
                <label class="control-label col-md-4 col-lg-3 pf-label"
                  >State:</label
                >
                <div class="col-md-8 col-lg-9 pci-form-group">
                  <input
                    id="state"
                    type="text"
                    class="form-control empty-input"
                    autocomplete="new-password"
                    v-model="form_data.state"
                    @change="requiredOnChange('state')"
                    required
                  />
                </div>
              </div>
              <div class="col-xs-2">
                <label
                  class="control-label col-md-3 pf-label"
                  :class="{ 'label-error': errors.has('zip') }"
                  >Zip:</label
                >
                <div class="col-md-9 pci-form-group">
                  <the-mask
                    id="zip"
                    autocomplete="new-password"
                    name="zip"
                    :class="{ 'input-error': errors.has('zip') }"
                    class="form-control empty-input"
                    mask="#####"
                    :masked="true"
                    v-validate="'required|min:5'"
                    v-model="form_data.zip"
                    @keydown.enter.prevent
                    @change="requiredOnChange('zip')"
                  ></the-mask>
                  <!--<input id="zip"-->
                  <!--type="text"-->
                  <!--class="form-control empty-input"-->
                  <!--v-model="form_data.zip"-->
                  <!--@change="requiredOnChange('zip')" required>-->
                </div>
              </div>
              <div class="col-xs-5 wo-pl">
                <label
                  class="control-label col-md-2 pf-label"
                  :class="{ 'label-error': errors.has('email') }"
                  >Email:</label
                ><!--col-md-3-->
                <div class="col-md-10 pci-form-group">
                  <!--col-md-9-->
                  <input
                    id="email"
                    type="text"
                    class="form-control empty-input"
                    autocomplete="new-password"
                    v-model="form_data.email"
                    required
                    v-validate="emailValidateRules"
                    name="email"
                    :class="{ 'input-error': errors.has('email') }"
                    @change="
                      requiredOnChange('email');
                      clearRadio('email', 'allow_mailing');
                    "
                  />
                </div>
              </div>
            </div>

            <div class="row pci-row" id="allow_mailing">
              <div class="col-md-12">
                <label class="control-label col-xs-9"
                  >Do we have your permission to e-mail you free educational
                  materials that you can use between sessions to support your
                  treatment?</label
                >
                <div class="col-xs-3 text-right pci-form-group">
                  <label class="control-label">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_mailing"
                      :disabled="form_data.email.trim() === ''"
                    />
                    Yes
                  </label>
                  /
                  <label class="control-label">
                    <input
                      type="radio"
                      value="No"
                      v-model="form_data.allow_mailing"
                      :disabled="form_data.email.trim() === ''"
                    />
                    No
                  </label>
                </div>
              </div>
            </div>

            <div class="row pci-row">
              <div class="col-md-5">
                <label
                  class="control-label col-md-4 col-lg-3 pf-label"
                  :class="{ 'error-label': errors.has('home_phone') }"
                  >Phone (home):</label
                >
                <div class="col-md-8 col-lg-9 pci-form-group">
                  <the-mask
                    id="home_phone"
                    autocomplete="new-password"
                    name="home_phone"
                    :class="{ 'input-error': errors.has('home_phone') }"
                    class="form-control empty-input"
                    mask="###-###-####"
                    :masked="true"
                    v-validate="'min:12'"
                    v-model="form_data.home_phone"
                    @keydown.enter.prevent
                    @change="clearRadio('home_phone', 'allow_home_phone_call')"
                  ></the-mask>
                  <!--<input type="tel" class="form-control empty-input"-->
                  <!--v-model="form_data.home_phone"-->
                  <!--@change="clearRadio('home_phone','allow_home_phone_call')">-->
                </div>
              </div>
              <div class="col-md-7" id="allow_home_phone_call">
                <label class="control-label col-xs-9"
                  >Okay to call this number and leave messages?</label
                >
                <div class="col-xs-3 text-right pci-form-group">
                  <label class="control-label">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_home_phone_call"
                      :disabled="form_data.home_phone.trim() === ''"
                    />
                    Yes
                  </label>
                  /
                  <label class="control-label">
                    <input
                      type="radio"
                      value="No"
                      v-model="form_data.allow_home_phone_call"
                      :disabled="form_data.home_phone.trim() === ''"
                    />
                    No
                  </label>
                </div>
              </div>
            </div>
            <div class="row pci-row">
              <div class="col-md-5">
                <label
                  class="control-label col-md-5 col-lg-3 pf-label"
                  :class="{ 'error-label': errors.has('mobile_phone') }"
                  >Phone (mobile):</label
                >
                <div class="col-md-7 col-lg-9 pci-form-group">
                  <the-mask
                    id="mobile_phone"
                    autocomplete="new-password"
                    name="mobile_phone"
                    :class="{ 'input-error': errors.has('mobile_phone') }"
                    class="form-control empty-input"
                    mask="###-###-####"
                    :masked="true"
                    v-validate="'min:12'"
                    v-model="form_data.mobile_phone"
                    @keydown.enter.prevent
                    @change="
                      requiredOnChange('mobile_phone');
                      clearRadio('mobile_phone', 'allow_mobile_phone_call');
                      clearRadio('mobile_phone', 'allow_mobile_send_messages');
                    "
                  ></the-mask>
                  <!--<input id="mobile_phone" type="tel" class="form-control empty-input"-->
                  <!--v-model="form_data.mobile_phone"-->
                  <!--@change="requiredOnChange('mobile_phone'); clearRadio('mobile_phone','allow_mobile_phone_call'); clearRadio('mobile_phone','allow_mobile_send_messages')"-->
                  <!--required>-->
                </div>
              </div>
              <div class="col-md-7" id="allow_mobile_phone_call">
                <label class="control-label col-xs-9"
                  >Okay to call this number and leave messages?</label
                >
                <div class="col-xs-3 text-right pci-form-group">
                  <label class="control-label">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_mobile_phone_call"
                      :disabled="form_data.mobile_phone.trim() === ''"
                    />
                    Yes
                  </label>
                  /
                  <label class="control-label">
                    <input
                      type="radio"
                      value="No"
                      v-model="form_data.allow_mobile_phone_call"
                      :disabled="form_data.mobile_phone.trim() === ''"
                    />
                    No
                  </label>
                </div>
              </div>
            </div>

            <div class="row pci-row" id="allow_mobile_send_messages">
              <div class="col-xs-9 col-sm-9 col-md-10">
                <label class="control-label pf-label">
                  Okay to send treatment related text messages & appointment
                  reminders to mobile number?
                </label>
              </div>
              <div class="col-xs-3 col-sm-3 col-md-2 text-right">
                <label class="control-label">
                  <input
                    type="radio"
                    value="Yes"
                    v-model="form_data.allow_mobile_send_messages"
                    :disabled="form_data.mobile_phone.trim() === ''"
                  />
                  Yes
                </label>
                /
                <label class="control-label">
                  <input
                    type="radio"
                    value="No"
                    v-model="form_data.allow_mobile_send_messages"
                    :disabled="form_data.mobile_phone.trim() === ''"
                  />
                  No
                </label>
              </div>
            </div>

            <div class="row pci-row">
              <div class="col-md-5">
                <label
                  class="control-label col-md-4 col-lg-3 pf-label"
                  :class="{ 'error-label': errors.has('work_phone') }"
                  >Phone (work):</label
                >
                <div class="col-md-8 col-lg-9 pci-form-group">
                  <the-mask
                    id="work_phone"
                    autocomplete="new-password"
                    name="work_phone"
                    :class="{ 'input-error': errors.has('work_phone') }"
                    class="form-control empty-input"
                    mask="###-###-####"
                    :masked="true"
                    v-validate="'min:12'"
                    v-model="form_data.work_phone"
                    @keydown.enter.prevent
                    @change="clearRadio('work_phone', 'allow_work_phone_call')"
                  ></the-mask>
                  <!--<input type="tel" class="form-control empty-input"-->
                  <!--v-model="form_data.work_phone"-->
                  <!--@change="clearRadio('work_phone','allow_work_phone_call')">-->
                </div>
              </div>
              <div class="col-md-7" id="allow_work_phone_call">
                <label class="control-label col-xs-9"
                  >Okay to call this number and leave messages?</label
                >
                <div class="col-xs-3 text-right pci-form-group">
                  <label class="control-label">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_work_phone_call"
                      :disabled="form_data.work_phone.trim() === ''"
                    />
                    Yes
                  </label>
                  /
                  <label class="control-label">
                    <input
                      type="radio"
                      value="No"
                      v-model="form_data.allow_work_phone_call"
                      :disabled="form_data.work_phone.trim() === ''"
                    />
                    No
                  </label>
                </div>
              </div>
            </div>

            <div class="row pci-row">
              <div class="col-xs-6">
                <label class="control-label col-md-5 col-lg-4"
                  >Emergency Contact:</label
                >
                <div class="col-md-7 col-lg-8 pci-form-group">
                  <input
                    id="emergency_contact"
                    type="text"
                    class="form-control empty-input"
                    v-model="form_data.emergency_contact"
                    autocomplete="new-password"
                    @change="requiredOnChange('emergency_contact')"
                    required
                  />
                </div>
              </div>
              <div class="col-xs-2 pci-form-group">
                <label
                  class="control-label col-md-5 col-lg-4"
                  :class="{
                    'error-label': errors.has('emergency_contact_phone'),
                  }"
                  >Phone:</label
                >
                <div class="col-md-7 col-lg-8 pci-form-group">
                  <the-mask
                    id="emergency_contact_phone"
                    autocomplete="new-password"
                    name="emergency_contact_phone"
                    :class="{
                      'input-error': errors.has('emergency_contact_phone'),
                    }"
                    class="form-control empty-input"
                    mask="###-###-####"
                    :masked="true"
                    v-validate="'min:12'"
                    v-model="form_data.emergency_contact_phone"
                    @keydown.enter.prevent
                  ></the-mask>
                </div>
              </div>
              <div class="col-xs-4">
                <label class="control-label col-md-5 col-lg-4"
                  >Relationship:</label
                >
                <div class="col-md-7 col-lg-8 pci-form-group">
                  <input
                    id="emergency_contact_relationship"
                    type="text"
                    class="form-control empty-input"
                    autocomplete="new-password"
                    v-model="form_data.emergency_contact_relationship"
                    @change="requiredOnChange('emergency_contact_relationship')"
                    required
                  />
                </div>
              </div>
            </div>
            <div id="required_only_one">
              <div class="row pci-row">
                <div class="col-xs-12 bold-label">
                  How did you hear about us?
                </div>
                <div class="col-xs-12">
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.yelp"
                        :disabled="hear_about_us_checked && !form_data.yelp"
                      />
                    </span>
                    Yelp
                  </label>
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.google"
                        :disabled="hear_about_us_checked && !form_data.google"
                      />
                    </span>
                    Google
                  </label>
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.yellow_pages"
                        :disabled="
                          hear_about_us_checked && !form_data.yellow_pages
                        "
                      />
                    </span>
                    Yellow Pages
                  </label>
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.event_i_attended"
                        :disabled="
                          hear_about_us_checked && !form_data.event_i_attended
                        "
                      />
                    </span>
                    Event I attended
                  </label>
                  <span id="hear_about_us_other">
                    <label class="control-label checkbox-label bold-label">
                      <span class="checkbox-wrapper">
                        <input
                          type="checkbox"
                          class="form-control empty-field checkbox-inline custom-checkbox"
                          v-model="form_data.hear_about_us_other"
                          @change="
                            changeChecked(
                              'hear_about_us_other',
                              'hear_about_us_other_specify'
                            )
                          "
                          :disabled="
                            hear_about_us_checked &&
                            !form_data.hear_about_us_other
                          "
                        />
                      </span>
                      Other (specify):
                    </label>
                    <input
                      type="text"
                      class="form-control empty-input inline-block max-300"
                      v-model="form_data.hear_about_us_other_specify"
                      autocomplete="new-password"
                      :disabled="!form_data.hear_about_us_other"
                    />
                  </span>
                </div>
              </div>

              <div class="row pci-row">
                <div class="col-xs-12 bold-label">
                  I was referred by:
                </div>
                <div class="col-xs-12">
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.friend_or_relative"
                        :disabled="
                          hear_about_us_checked && !form_data.friend_or_relative
                        "
                      />
                    </span>
                    Friend or relative
                  </label>
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.another_professional"
                        :disabled="
                          hear_about_us_checked &&
                          !form_data.another_professional
                        "
                      />
                    </span>
                    Another professional
                  </label>
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.kaiser"
                        :disabled="hear_about_us_checked && !form_data.kaiser"
                      />
                    </span>
                    Kaiser
                  </label>
                  <span id="referred_by_other_insurance">
                    <label class="control-label checkbox-label bold-label">
                      <span class="checkbox-wrapper">
                        <input
                          type="checkbox"
                          class="form-control empty-field checkbox-inline custom-checkbox"
                          v-model="form_data.referred_by_other_insurance"
                          @change="
                            changeChecked(
                              'referred_by_other_insurance',
                              'referred_by_other_insurance_specify'
                            )
                          "
                          :disabled="
                            hear_about_us_checked &&
                            !form_data.referred_by_other_insurance
                          "
                        />
                      </span>
                      Other insurance
                    </label>
                    <input
                      type="text"
                      class="form-control empty-input inline-block max-300"
                      v-model="form_data.referred_by_other_insurance_specify"
                      autocomplete="new-password"
                      :disabled="!form_data.referred_by_other_insurance"
                    />
                  </span>
                </div>
              </div>
            </div>
          </div>
          <!--/.inp-group-->

          <h3 class="text-center header-part">
            AGREEMENT FOR SERVICE / PATIENT RIGHTS / HIPAA PRIVACY NOTICE
          </h3>
          <div class="inp-group">
            <h4 class="text-center sub-title">AGREEMENT FOR SERVICE</h4>
            <hr class="block-separator" />
            <div class="row">
              <div class="col-xs-8">
                <b>Introduction</b><br />
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
              </div>
              <div class="col-xs-4">
                <div
                  class="text-center view-block view-block-wo-mb"
                  @click.prevent="showModal('agreement_for_service')"
                >
                  <h4>Click Here to View<br />AGREEMENT FOR SERVICE</h4>
                </div>
              </div>
            </div>
            <label class="control-label checkbox-label">
              <span class="checkbox-wrapper">
                <input
                  type="checkbox"
                  class="form-control empty-field checkbox-inline custom-checkbox"
                  v-model="form_data.get_paper_copy_of_agreement_for_service"
                />
              </span>
              <b>I would like to get a paper copy of Agreement For Service</b>
            </label>

            <div class="empty-space-40"></div>
            <h4 class="text-center sub-title">HIPAA PRIVACY NOTICE</h4>
            <hr class="block-separator" />
            <div class="row">
              <div class="col-xs-8">
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
              </div>
              <div class="col-xs-4">
                <div
                  class="text-center view-block"
                  @click.prevent="showModal('hipaa_privacy_notice')"
                >
                  <h4>Click Here to View<br />HIPAA PRIVACY NOTICE</h4>
                </div>
              </div>
            </div>
            <label class="control-label checkbox-label">
              <span class="checkbox-wrapper">
                <input
                  type="checkbox"
                  class="form-control empty-field checkbox-inline custom-checkbox"
                  v-model="form_data.get_paper_copy_of_hipaa_privacy_notice"
                />
              </span>
              <b>I would like to get a paper copy of HIPAA Privacy Notice.</b>
            </label>

            <div class="empty-space-40"></div>
            <h4 class="text-center sub-title">PATIENT RIGHTS</h4>
            <hr class="block-separator" />
            <div class="row">
              <div class="col-xs-8">
                At Change Within Reach, Inc. our pledge to you as our client, is
                to provide you with quality care for the services you are
                seeking. We believe that a successful component in receiving
                psychological services is a trusting, working relationship with
                your assigned provider, one in which you feel comfortable and
                your needs and concerns are being met.
              </div>
              <div class="col-xs-4">
                <div
                  class="text-center view-block"
                  @click.prevent="showModal('patient_rights')"
                >
                  <h4>Click Here to View<br />YOUR PATIENT RIGHTS</h4>
                </div>
              </div>
            </div>
            <label class="control-label checkbox-label">
              <span class="checkbox-wrapper">
                <input
                  type="checkbox"
                  class="form-control empty-field checkbox-inline custom-checkbox"
                  v-model="form_data.get_paper_copy_of_patient_rights"
                />
              </span>
              <b>I would like to get a paper copy of my Patient Rights</b>
            </label>
            <div class="empty-space-40"></div>

            <h4 class="text-center sub-title">
              NOTICE TO PSYCHOTHERAPY CLIENTS
            </h4>
            <hr class="block-separator" />
            <div class="row">
              <div class="col-xs-12">
                The Board of Behavioral Sciences receives and responds to
                complaints regarding services provided within the scope of
                practice of (marriage and family therapists, licensed
                educational psychologists, clinical social workers, or
                professional clinical counselors). You may contact the board
                online at
                <a href="https://www.bbs.ca.gov" target="_blank"
                  >www.bbs.ca.gov</a
                >, or by calling (916) 574-7830
              </div>
            </div>
            <div class="empty-space-40"></div>
            <hr class="block-separator" />

            <div style="">
              <label class="control-label checkbox-label">
                <span class="checkbox-wrapper">
                  <input
                    type="checkbox"
                    class="form-control empty-field checkbox-inline custom-checkbox"
                    v-model="form_data.understand_agreements"
                  />
                </span>
                <b
                  >I understand that checking this box constitutes a legal
                  signature confirming that I acknowledge and agree to the Terms
                  and Conditions stated in these agreements.</b
                >
              </label>
            </div>
          </div>

          <h3 class="text-center header-part">
            PAYMENT FOR SERVICE AND FEE ARRANGEMENTS
          </h3>
          <div class="inp-group">
            <div class="row">
              <div class="col-xs-1 col-sm-2 col-md-1"></div>
              <label class="col-xs-8 col-sm-8 col-md-9 co-pay-label">
                Co-pay and/or co-insurance for session:
              </label>
              <div class="col-xs-3 col-sm-2 text-right">
                $<input
                  type="text"
                  class="payment-input form-control inline-block"
                  v-model="form_data.co_pay"
                  readonly
                />
              </div>
            </div>
            <div class="row">
              <div class="col-xs-1 col-sm-2 col-md-1"></div>
              <label class="col-xs-8 col-sm-8 col-md-9 co-pay-label">
                Payment for session not covered due to deductible:
              </label>
              <div class="col-xs-3 col-sm-2 text-right">
                $<input
                  type="text"
                  class="payment-input form-control inline-block"
                  v-model="form_data.payment_for_session_not_converted"
                  readonly
                />
              </div>
            </div>
            <div class="row">
              <div class="col-xs-1 col-sm-2 col-md-1"></div>
              <label class="col-xs-8 col-sm-8 col-md-9 co-pay-label">
                Self-pay for session when paid out-of-pocket:
              </label>
              <div class="col-xs-3 col-sm-2 text-right">
                $<input
                  type="text"
                  class="payment-input form-control inline-block"
                  v-model="form_data.self_pay"
                  readonly
                />
              </div>
            </div>
            <div class="row">
              <div class="col-xs-1 col-sm-2 col-md-1"></div>
              <label class="col-xs-8 col-sm-8 col-md-9 co-pay-label">
                Charge for cancellation without 24 hours&#039; notice:
              </label>
              <div class="col-xs-3 col-sm-2 text-right">
                $<input
                  type="text"
                  class="payment-input form-control inline-block"
                  v-model="form_data.charge_for_cancellation"
                  readonly
                />
              </div>
            </div>
            <div class="row">
              <div class="col-xs-1 col-sm-2 col-md-1"></div>
              <label class="col-xs-8 col-sm-8 col-md-9 co-pay-label">
                Other charges [specify]:
                <input
                  type="text"
                  class="payment-input other-payment-input form-control inline-block"
                  v-model="form_data.other_charges"
                  readonly
                />
              </label>
              <div class="col-xs-3 col-sm-2 text-right">
                $<input
                  type="text"
                  class="payment-input form-control inline-block"
                  v-model="form_data.other_charges_price"
                  readonly
                />
              </div>
            </div>

            <template v-if="patient && !patient.is_payment_forbidden">
              <div class="empty-space-40"></div>
              <h4 class="text-center sub-title">
                ABOUT RECURRING CREDIT CARD CHARGES
              </h4>
              <hr class="block-separator" />
              <div class="row">
                <div class="col-xs-12">
                  <ul>
                  <li>
                    For your convenience, to save valuable time, you may store a
                    credit card on file in our secure PCI DSS compliant system
                    and authorize recurring charges to pay for your therapy
                    sessions.
                  </li>
                  <li>
                    The charge will be made under the name Change Within Reach,
                    Inc on day of your therapy appointment and a receipt will be
                    sent to the email address or mobile phone provided by you.
                  </li>
                  <li>
                    You will be able to cancel this authorization at any time.
                    The setup is easy and takes just a few minutes.
                  </li>
                  <li>
                    If you would like to do so, check the box below and one of
                    our staff members will assist you.
                  </li>
                </ul>
                </div>
                <div class="col-xs-12">
                  <label class="control-label checkbox-label" style="margin-bottom:10px">
                  <span class="checkbox-wrapper">
                    <input
                    type="checkbox"
                    class="form-control empty-field checkbox-inline custom-checkbox"
                    v-model="form_data.store_credit_card"
                    />
                  </span>
                  <b>
                    I would like to store my credit card on file with Change
                    Within Reach, Inc.
                  </b>
                  </label>
                  <square-payment-form
                    ref="square_form"
                    class="store-card-form"
                    :class="{'store-card-form--hidden': !form_data.store_credit_card}"
                    :postalCode="form_data.zip"
                    @postal-code-changed="setPostalCode"
                    @card-nonce-received="setCardNonce"
                    @got-response="squareFormGotResponse"
                    @square-response-received="squareResponseReceived"
                  ></square-payment-form>
                </div>
              </div>
            </template>
          </div>

          <!--signatures-->
          <h3 class="text-center header-part">
            ELECTRONIC SIGNATURE
          </h3>
          <div class="inp-group">
            <div class="row pci-row">
              <div class="col-xs-12" data-signature="patient-signature">
                <div class="signature-title" style="margin-top: 0;">
                  <div class="row">
                    <div class="col-xs-10">
                      <h4 id="signature-title">
                        Signature of Patient (or authorized representative)
                      </h4>
                    </div>
                    <div class="col-xs-2">
                      <button
                        type="button"
                        class="btn btn-danger pull-right"
                        @click.prevent="clearSignature('patient-signature')"
                      >
                        Clear
                      </button>
                    </div>
                  </div>
                </div>

                <div id="patient-signature"></div>
                <hr class="signature-line" />
              </div>
            </div>
            <!--<div class="empty-space-50"></div>-->
            <div class="row" style="margin-bottom: 20px; margin-top: 75px;">
              <div class="col-xs-8">
                <label class="control-label col-md-6 col-lg-5 pf-label"
                  >Name of parent/guardian/representative:</label
                >
                <div class="col-md-6 col-lg-7 pci-form-group">
                  <input
                    id="guardian_name"
                    type="text"
                    class="form-control empty-input"
                    v-model="form_data.guardian_name"
                    autocomplete="new-password"
                  />
                </div>
              </div>
              <div class="col-xs-4">
                <label class="control-label col-md-4 pf-label"
                  >Relationship:</label
                >
                <div class="col-md-8 pci-form-group">
                  <input
                    id="relationship"
                    type="text"
                    class="form-control empty-input"
                    autocomplete="new-password"
                    v-model="form_data.relationship"
                  />
                </div>
              </div>
            </div>
            <div class="row pci-row">
              <div
                class="col-xs-12"
                style="margin-bottom: 50px;"
                data-signature="patient-signature-under-18"
              >
                <div class="signature-title test" style="margin-top: 0;">
                  <div class="row">
                    <div class="col-xs-10">
                      <h4 id="signature18-title" style="margin-top: 0;">
                        Signature of Parent or Legal Guardian (if Patient is
                        under 15)
                      </h4>
                    </div>
                    <div class="col-xs-2">
                      <button
                        type="button"
                        class="btn btn-danger pull-right"
                        @click.prevent="
                          clearSignature('patient-signature-under-18')
                        "
                      >
                        Clear
                      </button>
                    </div>
                  </div>
                </div>
                <div id="patient-signature-under-18"></div>
                <hr class="signature-line" />
              </div>
            </div>
          </div>

          <div class="form-note-button-block text-right">
            <div class="row form-note-row">
              <span class="text-red validation-error-msg">{{
                validation_messages.current
              }}</span>
              <button
                type="submit"
                class="btn btn-primary"
                @click.prevent="validateForm()"
                v-if="!statuses.saving"
              >
                Save
              </button>
              <pageloader
                add-classes="save-loader"
                v-show="statuses.saving"
              ></pageloader>
            </div>
          </div>
        </form>
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

    <div
      class="modal modal-vertical-center fade"
      id="enter-password-modal"
      data-backdrop="static"
      data-keyboard="false"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">
              Please hand the tablet to staff member.
            </h4>
          </div>
          <div class="modal-body">
            <label for="password" class="control-label required">
              Enter PIN
            </label>
            <input
              type="password"
              id="password"
              maxlength="255"
              class="form-control"
              required
              v-model="doctor_password"
              @keyup.enter="saveForm"
              autofocus
            />
          </div>
          <div class="modal-footer">
            <span class="text-red validation-error-msg">{{
              validation_messages.current
            }}</span>
            <button
              type="button"
              class="btn btn-primary"
              @click.prevent="saveForm"
              v-if="!statuses.saving"
            >
              Save
            </button>
            <pageloader
              add-classes="save-loader"
              v-show="statuses.saving"
            ></pageloader>
            <button
              type="button"
              class="btn btn-default"
              :disabled="statuses.saving"
              @click="closeConfirmDialog"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal modal-vertical-center fade"
      id="error-message-modal"
      data-backdrop="static"
      data-keyboard="false"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Can't submit the form</h4>
          </div>
          <div class="modal-body">
            {{ error_message }}
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-default"
              @click="closeErrorMessageModal()"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal modal-vertical-center fade"
      id="patient-data-modal"
      data-backdrop="static"
      data-keyboard="false"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-body">
            <h4 class="text-center sub-title">REQUESTED DOCUMENTS</h4>
            <hr class="block-separator" />
            <p>
              As a therapist/staff member at Change Within Reach, Inc. you are
              required to provide all documents requested by the patient in
              order to comply with state and federal laws.
            </p>
            <p v-html="requested_documents"></p>
            <label
              class="control-label checkbox-label2"
              id="provided_all_documents"
              v-if="is_any_document_requested"
            >
              <input
                type="checkbox"
                class="form-control checkbox-inline custom-checkbox"
                v-model="provided_all_documents"
              />
              I have provided all documents and other informational resources
              requested by the patient. I understand that I am required by law
              to provide the patient with paper copies of requested documents
              and by checking this box I confirm that I have done so.
            </label>
            <div class="empty-space-10"></div>
            <h4 class="text-center sub-title">PAYMENT FOR SERVICE</h4>
            <hr class="block-separator" />
            <div v-html="payment_for_service"></div>
            <label
              class="control-label checkbox-label2"
              v-if="form_data.store_credit_card"
              id="provider_store_credit_card"
            >
              <input
                type="checkbox"
                class="form-control checkbox-inline custom-checkbox"
                v-model="provider_store_credit_card"
              />
              I understand and agree to the terms stated above
            </label>
            <div class="row">
              <div class="col-xs-6">
                <div class="form-group" id="provider_name">
                  <label class="control-label checkbox-label2 required"
                    >Therapist / Staff Member Name:</label
                  >
                  <select
                    class="form-control"
                    v-model="form_data.provider_name"
                  >
                    <option value="-1" disabled></option>
                    <option
                      v-for="item in provider_list"
                      :value="item.provider_name"
                    >
                      {{ item.provider_name }}
                    </option>
                  </select>
                </div>
              </div>

              <div
                class="col-xs-12"
                style="margin-bottom: 50px;"
                data-signature="provider-signature"
              >
                <div class="signature-title" style="margin-top: 0;">
                  <div class="row">
                    <div class="col-xs-10">
                      <h4 id="provider-signature-title" class="required">
                        Therapist / Staff Member Signature:
                      </h4>
                    </div>
                    <div class="col-xs-2">
                      <button
                        type="button"
                        class="btn btn-danger pull-right"
                        @click.prevent="clearSignature('provider-signature')"
                      >
                        Clear
                      </button>
                    </div>
                  </div>
                </div>

                <div id="provider-signature"></div>
                <hr class="signature-line" />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <span class="text-red validation-error-msg">{{
              validation_messages.current
            }}</span>
            <button
              type="button"
              class="btn btn-primary"
              @click.prevent="validateLastForm"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { DEFAULT_CANCELLATION_FEE } from '../../settings';

export default {
  data() {
    return {
      hide_form: false,
      co_pay_entered: false,
      provided_all_documents: false,
      provider_store_credit_card: false,

      provider_signature_is_empty: true,

      hear_about_us_checked: false,
      squareFormResponse: false,
      emailValidateRules: {
        required: true,
        regex: /^(?:[A-Za-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[A-Za-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z0-9](?:[A-Za-z0-9-]*[A-Za-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[A-Za-z0-9-]*[A-Za-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/,
      },
      error_message: "",
      form_data: {
        name: "",
        date_of_birth: "",
        home_address: "",
        city: "",
        state: "",
        zip: "",
        email: "",
        allow_mailing: "",
        home_phone: "",
        mobile_phone: "",
        work_phone: "",
        allow_home_phone_call: "",
        allow_mobile_phone_call: "",
        allow_mobile_send_messages: "",
        allow_work_phone_call: "",
        emergency_contact: "",
        emergency_contact_phone: "",
        emergency_contact_relationship: "",
        //how did you hear about us?
        yelp: false,
        google: false,
        yellow_pages: false,
        event_i_attended: false,
        hear_about_us_other: false,
        hear_about_us_other_specify: "",
        //I was referred by:
        friend_or_relative: false,
        another_professional: false,
        kaiser: false,
        referred_by_other_insurance: false,
        referred_by_other_insurance_specify: "",
        //--------------------------
        access_credit_card: false,
        co_pay: "0",
        payment_for_session_not_converted: "0",
        self_pay: "0",
        charge_for_cancellation: "0",
        other_charges: "",
        other_charges_price: "0",
        store_credit_card: false,
        credit_card_nonce: "",
        notify_agree: "",
        receive_electronic_v_of_pp: false,
        receive_paper_v_of_pp: false,
        introduction: "",
        health_insurance: "",
        request_payment_of_authorized: "",
        allow_request_payment_of_authorized: false,
        patient_id: null,
        signature: "",
        signature18: "",

        get_paper_copy_of_agreement_for_service: false,
        get_paper_copy_of_hipaa_privacy_notice: false,
        get_paper_copy_of_patient_rights: false,
        understand_agreements: false,

        provider_name: "-1",
        guardian_name: "",
        relationship: "",
        years_old: "",
        card_data: {},
      },
      required_fields: [
        "home_address",
        "city",
        "state",
        "zip",
        "email",
        "mobile_phone",
        "emergency_contact",
        "emergency_contact_phone",
        "emergency_contact_relationship",
      ],
      required_radio: [
        "allow_mailing",
        "allow_mobile_phone_call",
        "allow_mobile_send_messages",
      ],
      required_radio_if_entered: {
        home_phone: "allow_home_phone_call",
        work_phone: "allow_work_phone_call",
      },
      required_input_if_checkbox_checked: {
        hear_about_us_other: "hear_about_us_other_specify",
        referred_by_other_insurance: "referred_by_other_insurance_specify",
      },
      required_input_for_card: {
        store_credit_card: "credit_card_nonce",
      },
      validation_messages: {
        current: "",
        required: "Please make sure you have filled all the required fields.",
        incorrect_password: "You have entered incorrect PIN code.",
        try_again: "Error! Please try again.",
      },
      required_only_one_checkbox: [
        "yelp",
        "google",
        "yellow_pages",
        "event_i_attended",
        "hear_about_us_other",
        "friend_or_relative",
        "another_professional",
        "kaiser",
        "referred_by_other_insurance",
      ],

      id: "",
      doctor_password: "",
      signature_is_empty: true,
      signature18_is_empty: true,
      statuses: {
        saving: false,
      },

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
    };
  },

  computed: {
    is_develop_mode() {
      return this.$store.state.develop_mode;
    },
    agreement_for_service() {
      return {
        title: "AGREEMENT FOR SERVICE",
        body:
          "<b>Introduction</b>" +
          "<p>This Agreement is intended to provide&nbsp;<u>&nbsp;&nbsp;" +
          this.form_data.name +
          '&nbsp;&nbsp;</u>&nbsp;(herein "Patient") with important information regarding the practices, policies and procedures of Change Within Reach, Inc., and to clarify the terms of the professional therapeutic relationship between a Therapist, hired as an independent contractor by Change Within Reach, Inc. and Patient. Any questions or concerns regarding the contents of this Agreement should be discussed with Therapist prior to signing it.</p>' +
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

    patient() {
      return this.$store.state.currentPatient;
    },

    provider_list() {
      return this.$store.state.provider_list;
    },

    currentNote() {
      return this.$store.state.currentNote;
    },

    currentSignatureStatus() {
      return this.$store.state.signatureIsEmpty;
    },

    is_any_document_requested() {
      return (
        this.form_data.get_paper_copy_of_agreement_for_service ||
        this.form_data.get_paper_copy_of_hipaa_privacy_notice ||
        this.form_data.get_paper_copy_of_patient_rights
      );
    },

    requested_documents() {
      if (
        !this.form_data.get_paper_copy_of_agreement_for_service &&
        !this.form_data.get_paper_copy_of_hipaa_privacy_notice &&
        !this.form_data.get_paper_copy_of_patient_rights
      ) {
        return "This patient did not request paper copies of any documents or additional information at this time.";
      }
      let html =
        "<p>The patient requested a paper copy of the following documents:</p><ul>";
      if (this.form_data.get_paper_copy_of_agreement_for_service) {
        html += "<li>Agreement For Service (Informed Consent)</li>";
      }
      if (this.form_data.get_paper_copy_of_hipaa_privacy_notice) {
        html += "<li>HIPAA Privacy Notice</li>";
      }
      if (this.form_data.get_paper_copy_of_patient_rights) {
        html += "<li>Patient Rights</li>";
      }
      html += "</ul>";
      return html;
    },

    payment_for_service() {
      if (!this.form_data.store_credit_card) {
        return "<p>The patient does not want to store his/her credit card on file at this time</p>";
      } else {
        let html =
          "<p>The patient requested to store his/her credit card on file with Change Within Reach, Inc. Please assist the patient with this matter.</p>";
        html +=
          "<p>As a therapist/staff member at Change Within Reach, Inc. you are required to collect payments for services rendered and/or all associated fees from the patient on the day of therapy appointment.</p>";
        html +=
          "<p>By checking the box below, you agree to collect all payments due and understand that failure to do so will result in deduction of these fees from your paycheck.</p>";
        return html;
      }
    },

    provider() {
      return this.$store.state.currentProvider;
    },
  },

  methods: {
    closeErrorMessageModal() {
      this.error_message = "";
    },

    setCardNonce(nonce) {
      this.form_data.credit_card_nonce = nonce;
    },
    squareFormGotResponse() {
      this.squareFormResponse = true;
    },
    squareFormSendRequest() {
      this.squareFormResponse = false;
      
      if (!this.form_data.store_credit_card) {
        return;
      }

      this.$refs.square_form.requestCardNonce();
    },
    setPostalCode(postcode) {
      this.form_data.zip = postcode;
    },
    squareResponseReceived(response) {
      if (response && response.errors && response.errors.length) {
        this.validation_messages.current = response.errors[0].message;
      }

      this.form_data.card_data = response;
    },

    clearRadio(el, radio) {
      if (this.form_data[el].trim() === "") {
        this.form_data[radio] = "";
      }
    },

    validateLastForm() {
      let has_errors = false;

      if (this.is_any_document_requested && !this.provided_all_documents) {
        has_errors = true;
        $("#provided_all_documents").addClass("label-error");
      }

      if (
        this.form_data.store_credit_card &&
        !this.provider_store_credit_card
      ) {
        has_errors = true;
        $("#provider_store_credit_card").addClass("label-error");
      }

      let provider_name = this.form_data.provider_name.trim();
      if (provider_name === "" || provider_name === "-1") {
        has_errors = true;
        $("#provider_name label").addClass("label-error");
        $("#provider_name select").addClass("input-error");
      }

      if (this.provider_signature_is_empty) {
        has_errors = true;
        $("#provider-signature-title").addClass("label-error");
        $("#provider-signature").addClass("input-error");
        $(
          'div[data-signature="provider-signature"] hr.signature-line'
        ).addClass("signature-line-error");
      }

      if (has_errors) {
        this.validation_messages.current = this.validation_messages.required;
      } else {
        this.validation_messages.current = "";
        $("#patient-data-modal").modal("hide");
        this.$router.push("/forms/patient-" + this.patient.patient_id);
      }
    },

    changeChecked(checkbox, input) {
      if (this.form_data[checkbox] === false) {
        this.form_data[input] = "";
        $("#" + checkbox)
          .find("input[type=text]")
          .removeClass("input-error");
        $("#" + checkbox)
          .find("label")
          .removeClass("label-error");
      }
    },

    requiredOnChange(field) {
      if (this.form_data[field].trim() !== "") {
        $("#" + field)
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
      }
    },

    validateFormWithSquare() {
      let valid = true;
      return new Promise((resolve) => {
        this.squareFormSendRequest();

        // Wait for response from square-payment-form component
        let sqTimer = setInterval((_) => {
          if (this.squareFormResponse) {
            clearInterval(sqTimer);
            if (!this.validateNonce()) {
              valid = false;
            }
            setTimeout((_) => {
              resolve(valid);
            }, 500);
          }
        });
      });
    },
    validateNonce() {
      let has_errors = false;
      for (let key in this.required_input_for_card) {
        if (this.form_data[key] === true) {
          if (this.form_data[this.required_input_for_card[key]] === "") {
            $("#" + key)
              .find("input[type=text]")
              .addClass("input-error");
            $("#" + key)
              .find("label")
              .addClass("label-error");
            has_errors = true;
          }
        }

        if (has_errors) {
          this.validation_messages.current = this.validation_messages.required;
        } else {
          this.validation_messages.current = "";
        }

        return !has_errors;
      }
    },
    /**
     * validate form
     */
    validateForm() {
      let has_errors = false;
      if (this.form_data.years_old >= 15) {
        if (this.signature_is_empty) {
          has_errors = true;
          $("#signature-title").addClass("label-error");
          $("#patient-signature").addClass("input-error");
          $(
            'div[data-signature="patient-signature"] hr.signature-line'
          ).addClass("signature-line-error");
        }
      } else {
        if (this.signature18_is_empty) {
          has_errors = true;
          $("#signature18-title").addClass("label-error");
          $("#patient-signature-under-18").addClass("input-error");
          $(
            'div[data-signature="patient-signature-under-18"] hr.signature-line'
          ).addClass("signature-line-error");
        }
        this.required_fields.push("relationship");
        this.required_fields.push("guardian_name");
      }

      for (let i = 0; i < this.required_fields.length; i++) {
        let val = this.form_data[this.required_fields[i]].trim();
        if (val === "") {
          $("#" + this.required_fields[i])
            .addClass("input-error")
            .parents("div")
            .prev("label")
            .addClass("label-error");
          has_errors = true;
        }
      }

      let one_checked = false;
      for (let i = 0; i < this.required_only_one_checkbox.length; i++) {
        let val = this.form_data[this.required_only_one_checkbox[i]];
        if (val === true) {
          one_checked = true;
          break;
        }
      }
      if (!one_checked) {
        $("#required_only_one .bold-label").addClass("label-error");
        $("#required_only_one label").addClass("label-error");
        has_errors = true;
      }

      for (let i = 0; i < this.required_radio.length; i++) {
        let val = this.form_data[this.required_radio[i]].trim();
        if (val === "") {
          $("#" + this.required_radio[i])
            .find("label")
            .addClass("label-error");
          has_errors = true;
        }
      }

      for (let key in this.required_radio_if_entered) {
        if (this.form_data[key] !== "") {
          if (this.form_data[this.required_radio_if_entered[key]] === "") {
            $("#" + this.required_radio_if_entered[key])
              .find("label")
              .addClass("label-error");
            has_errors = true;
          }
        }
      }

      for (let key in this.required_input_if_checkbox_checked) {
        if (this.form_data[key] === true) {
          if (
            this.form_data[this.required_input_if_checkbox_checked[key]] === ""
          ) {
            $("#" + key)
              .find("input[type=text]")
              .addClass("input-error");
            $("#" + key)
              .find("label")
              .addClass("label-error");
            has_errors = true;
          }
        }
      }

      if (this.errors.has("email")) {
        let key = "email";
        $("#" + key)
          .parent()
          .parent()
          .find("label")
          .addClass("label-error");
        $("#" + key).addClass("input-error");
        has_errors = true;
      }

      this.$validator.validateAll().then((result) => {
        let phonesIsValid = result;

        if (!phonesIsValid) {
          has_errors = true;
        }
      });

      if (has_errors) {
        this.validation_messages.current = this.validation_messages.required;
      } else {
        this.validation_messages.current = "";
        this.showModal("enter-password-modal");
      }

      return !has_errors;
    },

    fetchData() {
      let id = this.$route.params.id;
      this.$store.dispatch("getPatient", { id: id }).then((error) => {
        if (error) {
          if (error.status === 403 || error.status === 404) {
            this.$router.push({ path: "/forms/404" });
          }
        }
        let first_name = this.patient.first_name;
        let last_name = this.patient.last_name;
        this.form_data.name = first_name + " " + last_name;
        this.form_data.date_of_birth = this.patient.date_of_birth;
        this.form_data.patient_id = this.patient.id;
        this.form_data.home_phone = this.patient.home_phone
          ? this.patient.home_phone
          : "";
        this.form_data.cell_phone = this.patient.cell_phone
          ? this.patient.cell_phone
          : "";
        this.form_data.work_phone = this.patient.work_phone
          ? this.patient.work_phone
          : "";
        let co_pay = this.patient.is_payment_forbidden ? 0 : this.patient.visit_copay;
        if (co_pay !== null && co_pay !== undefined) {
          this.form_data.co_pay = co_pay + "";
        }
        let diff = new Date(
          Date.now() - new Date(this.form_data.date_of_birth).getTime()
        );
        this.form_data.years_old = Math.abs(diff.getUTCFullYear() - 1970);

        if (this.patient.is_payment_forbidden) {
          this.co_pay_entered = true;
          window.setTimeout(() => {
            this.signatureInit();
          }, 500);
        } else if (!this.co_pay_entered) {
          window.setTimeout(() => {
            $("#co-pay-modal").modal("show");
          }, 500);
        }
      });

      this.$store.dispatch("getCurrentProvider").then(() => {
        let charge_for_cancellation = this.patient.is_payment_forbidden ? 0 : DEFAULT_CANCELLATION_FEE;
        
        if (charge_for_cancellation) {
            this.form_data.charge_for_cancellation = charge_for_cancellation + "";
        }
      });

      this.$store.dispatch("getProviderList");
    },

    showModal(name) {
      $("#" + name).modal("show");
    },

    closeConfirmDialog() {
      $("#enter-password-modal").modal("hide");
      this.doctor_password = "";
      this.validation_messages.current = "";
    },

    saveForm() {
      this.validation_messages.current = "";
      this.doctor_password = this.doctor_password.trim();
      if (this.doctor_password === "") {
        this.validation_messages.current = this.validation_messages.required;
        return false;
      }
      this.statuses.saving = true;
      this.$store
        .dispatch("isDoctorPasswordValid", { password: this.doctor_password })
        .then((response) => {
          if (response.valid === true) {
            if (this.form_data.store_credit_card) {
              this.validateFormWithSquare().then((valid) => {
                if (!valid) {
                  this.closeConfirmDialog();
                  this.validation_messages.current = this.form_data.card_data.errors[0].message;
                  this.statuses.saving = false;
                } else {
                  this.prepareAndSubmitForm();
                }
              });
            } else {
              this.prepareAndSubmitForm();
            }
          } else {
            this.statuses.saving = false;
            this.validation_messages.current = this.validation_messages.incorrect_password;
          }
        });
    },
    prepareAndSubmitForm() {
      if (!this.signature_is_empty) {
        let datapair = $("#patient-signature").jSignature("getData", "image");
        this.form_data.signature = "data:" + datapair[0] + "," + datapair[1];
      }
      if (!this.signature18_is_empty) {
        let datapair = $("#patient-signature-under-18").jSignature(
          "getData",
          "image"
        );
        this.form_data.signature18 = "data:" + datapair[0] + "," + datapair[1];
      }

      this.$store
        .dispatch("storeFirstForm", this.form_data)
        .then((response) => {
          this.statuses.saving = false;
          if (
            (response.status === 200 || response.status === 201) &&
            response.data &&
            response.data.success
          ) {
            $("#enter-password-modal").modal("hide");
            window.setTimeout(() => {
              $("#patient-data-modal").modal("show");
              this.hide_form = true;
            }, 500);
            //                                this.$router.push('/forms/patient-' + this.patient.patient_id);
          } else if (response.data && response.data.errors) {
            this.closeConfirmDialog();
            this.validation_messages.current = response.data.errors[0].detail;
          } else if (response.data && !response.data.success) {
            this.closeConfirmDialog();
            this.error_message = response.data.message;
          } else {
            this.validation_messages.current = this.validation_messages.try_again;
          }
        });
    },

    signatureInit() {
      var tmp = this;
      $("#patient-signature")
        .jSignature()
        .bind("change", function (e) {
          tmp.signature_is_empty = false;
          $("#signature-title").removeClass("label-error");
          $("#patient-signature").removeClass("input-error");
          $("#patient-signature").removeClass("input-error");
          $(
            'div[data-signature="patient-signature"] hr.signature-line'
          ).removeClass("signature-line-error");
        });
      $("#patient-signature-under-18")
        .jSignature()
        .bind("change", function (e) {
          tmp.signature18_is_empty = false;
          $("#signature18-title").removeClass("label-error");
          $("#patient-signature-under-18").removeClass("input-error");
          $("#patient-signature-under-18").removeClass("input-error");
          $(
            'div[data-signature="patient-signature-under-18"] hr.signature-line'
          ).removeClass("signature-line-error");
        });
    },

    clearSignature(name) {
      $("#" + name).jSignature("clear");
      if (name === "patient-signature") {
        this.signature_is_empty = true;
      } else if (name === "patient-signature-under-18") {
        this.signature18_is_empty = true;
      } else if (name === "provider-signature") {
        this.provider_signature_is_empty = true;
      }
    },

    verifyCoPay() {
      let has_errors = false;

      if (this.form_data.charge_for_cancellation.trim() === "") {
        has_errors = true;
        $("#charge_for_cancellation")
          .addClass("input-error")
          .parents("div")
          .prev("label")
          .addClass("label-error");
      }
      if (
        this.form_data.co_pay.trim() === "" &&
        this.form_data.self_pay.trim() === ""
      ) {
        has_errors = true;
        $("#co_pay")
          .addClass("input-error")
          .parents("div")
          .prev("label")
          .addClass("label-error");
        $("#self_pay")
          .addClass("input-error")
          .parents("div")
          .prev("label")
          .addClass("label-error");
      }
      if (!has_errors) {
        this.validation_messages.current = "";
        $("#co-pay-modal").modal("hide");
        this.co_pay_entered = true;
        window.setTimeout(() => {
          this.signatureInit();
        }, 500);
      } else {
        this.validation_messages.current = this.validation_messages.required;
      }
    },

    removeErrorClassesFromCheckboxes() {
      $("#required_only_one .bold-label").removeClass("label-error");
      $("#required_only_one label").removeClass("label-error");
    },
  },

  watch: {
    error_message() {
      if (this.error_message) {
        window.setTimeout(() => {
          $("#error-message-modal").modal("show");
        }, 500);
      } else {
        $("#error-message-modal").modal("hide");
      }
    },

    "form_data.allow_mailing": function () {
      $("#allow_mailing").find("label").removeClass("label-error");
    },

    "form_data.allow_mobile_phone_call": function () {
      $("#allow_mobile_phone_call").find("label").removeClass("label-error");
    },

    "form_data.allow_home_phone_call": function () {
      $("#allow_home_phone_call").find("label").removeClass("label-error");
    },

    "form_data.allow_work_phone_call": function () {
      $("#allow_work_phone_call").find("label").removeClass("label-error");
    },

    "form_data.allow_mobile_send_messages": function () {
      $("#allow_mobile_send_messages").find("label").removeClass("label-error");
    },

    "form_data.hear_about_us_other_specify": function () {
      if (this.form_data.hear_about_us_other_specify.trim() !== "") {
        $("#hear_about_us_other")
          .find("input[type=text]")
          .removeClass("input-error");
        $("#hear_about_us_other").find("label").removeClass("label-error");
      }
    },

    "form_data.referred_by_other_insurance_specify": function () {
      if (this.form_data.referred_by_other_insurance_specify.trim() !== "") {
        $("#referred_by_other_insurance")
          .find("input[type=text]")
          .removeClass("input-error");
        $("#referred_by_other_insurance")
          .find("label")
          .removeClass("label-error");
      }
    },

    "form_data.charge_for_cancellation": function () {
      if (this.form_data.charge_for_cancellation.trim() !== "") {
        $("#charge_for_cancellation")
          .removeClass("input-error")
          .parents("div")
          .find("label")
          .removeClass("label-error");
      }
    },

    "form_data.co_pay": function () {
      if (this.form_data.co_pay.trim() !== "") {
        $("#co_pay")
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
        $("#self_pay")
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
      }
    },

    "form_data.self_pay": function () {
      if (this.form_data.self_pay.trim() !== "") {
        $("#co_pay")
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
        $("#self_pay")
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
      }
    },

    "form_data.provider_name": function () {
      $("#provider_name label").removeClass("label-error");
      $("#provider_name select").removeClass("input-error");
    },

    "form_data.guardian_name": function () {
      if (this.form_data.guardian_name.trim() !== "") {
        $("#guardian_name")
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
      }
    },

    "form_data.relationship": function () {
      if (this.form_data.relationship.trim() !== "") {
        $("#relationship")
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
      }
    },

    "form_data.yelp": function () {
      if (this.form_data.yelp) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.google": function () {
      if (this.form_data.google) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.yellow_pages": function () {
      if (this.form_data.yellow_pages) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.event_i_attended": function () {
      if (this.form_data.event_i_attended) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.hear_about_us_other": function () {
      if (this.form_data.hear_about_us_other) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.friend_or_relative": function () {
      if (this.form_data.friend_or_relative) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.another_professional": function () {
      if (this.form_data.another_professional) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.kaiser": function () {
      if (this.form_data.kaiser) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.referred_by_other_insurance": function () {
      if (this.form_data.referred_by_other_insurance) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes();
      } else {
        this.hear_about_us_checked = false;
      }
    },

    provided_all_documents() {
      if (this.provided_all_documents) {
        $("#provided_all_documents").removeClass("label-error");
      }
    },
    provider_store_credit_card() {
      if (this.provider_store_credit_card) {
        $("#provider_store_credit_card").removeClass("label-error");
      }
    },
    patient() {
      if (this.patient !== null) {
        this.first_name = this.patient.first_name;
        this.last_name = this.patient.last_name;
        this.id = this.patient.id;
      }
    },
    currentNote() {
      let tempData = this.$data;
      if (this.currentNote !== null) {
        for (let prop in tempData) {
          this.$data[prop] =
            this.currentNote[prop] !== null ? this.currentNote[prop] : "";
        }
        this.signatureClear();
      }
    },
  },
  mounted() {
    this.fetchData();

    let self = this;
    $("#patient-data-modal").bind("shown.bs.modal", function () {
      $("#provider-signature")
        .jSignature()
        .bind("change", function (e) {
          self.provider_signature_is_empty = false;
          $("#provider-signature-title").removeClass("label-error");
          $("#provider-signature").removeClass("input-error");
          $('div[data-signature="provider-signature"] hr.signature-line').removeClass("signature-line-error");
        });
    });
    $("#enter-password-modal").bind("shown.bs.modal", function () {
      $("#password").focus();
    });
  },
};
</script>

<style scoped>
.save-loader {
  max-width: 36px;
  max-height: 36px;
  margin-right: 15px;
}

.bold-label {
  font-weight: 600 !important;
}

.validation-error-msg {
  padding-right: 20px;
}

.max-300 {
  max-width: 300px;
}

.sub-title {
  font-weight: 600;
}

.block-separator {
  margin-top: 0;
  background-color: #3e4855;
  height: 1px;
}

.empty-space-40 {
  height: 40px;
}

.empty-space-50 {
  height: 50px;
}

.empty-space-10 {
  height: 10px;
}

.co-pay-label {
  font-weight: 600;
}

.co-pay-row {
  margin-top: 10px;
}

.checkbox-label2 input {
  height: 20px;
  width: 25px;
}

.checkbox-label2 {
  font-weight: normal;
}

#patient-signature,
#patient-signature-under-18 {
  margin-top: 10px;
}

.store-card-form {
  width: 100%;
}

.store-card-form--hidden {
  position: absolute;
  visibility: hidden;
  pointer-events: none;
  z-index: -1;
}
</style>
