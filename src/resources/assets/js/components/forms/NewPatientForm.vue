<template>
  <div class="container confidential-info-form" v-if="form_data">
    <div class="patient-contact-info-container">
      <div class="section section-add-note">
        <form
          class="form-note form-horizontal patient-contact-info-form"
          autocomplete="off"
          id="form-note"
          novalidate
        >
          <div class="inp-group">
            <div class="row">
              <div class="col-xs-12 col-sm-6 col-md-7 row-centered">
                <label class="control-label col-sm-2 col-lg-1 pf-label"
                  >Name:</label
                >
                <div class="col-sm-10 col-lg-11 pci-form-group">
                  <input
                    type="text"
                    class="form-control empty-input"
                    v-model="form_data.name"
                    readonly
                  />
                </div>
              </div>
              <!--date of birth-->
              <div class="col-xs-12 col-sm-6 col-md-5 row-centered">
                <label class="control-label col-sm-4 col-lg-3 pf-label"
                  >Date of Birth:</label
                >
                <div class="col-sm-8 col-lg-9 pci-form-group">
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
              <div class="col-xs-12 row-centered">
                <label class="control-label col-sm-2 pf-label"
                  >Home Address:</label
                >
                <div class="col-sm-10 pci-form-group">
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
              <div class="col-md-3 col-sm-6 col-xs-12 row-centered">
                <label class="control-label col-sm-2 col-lg-2 pf-label"
                  >City:</label
                >
                <div class="col-sm-10 col-lg-10 pci-form-group">
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
              <div class="col-md-3 col-sm-3 col-xs-7 row-centered">
                <label class="control-label col-sm-4 col-lg-3 pf-label"
                  >State:</label
                >
                <div class="col-sm-8 col-lg-9 pci-form-group">
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
              <div class="col-md-2 col-sm-3 col-xs-5 row-centered">
                <label
                  class="control-label col-sm-3 pf-label"
                  :class="{ 'label-error': errors.has('zip') }"
                  >Zip:</label
                >
                <div class="col-sm-9 pci-form-group">
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
              <div class="col-md-4 col-xs-12 mt-md row-centered">
                <label
                  class="control-label col-xs-12 col-sm-2 pf-label"
                  :class="{ 'label-error': errors.has('email') }"
                  >Email:</label
                ><!--col-md-3-->
                <div class="col-xs-12 col-sm-10 pci-form-group">
                  <!--col-md-9-->
                  <input
                    id="email"
                    type="text"
                    class="form-control empty-input"
                    autocomplete="new-password"
                    v-model.trim="form_data.email"
                    required
                    v-validate="'required|email'"
                    name="email"
                    :class="{ 'input-error': errors.has('email') }"
                    @change="requiredOnChange('email')"
                    @input="
                      allowContact($event.target.value, ['allow_mailing'])
                    "
                  />
                </div>
              </div>
            </div>

            <div class="row pci-row" id="allow_mailing">
              <div class="col-md-12 row-centered">
                <label class="control-label col-xs-8 switch-label">
                  Do we have your permission to e-mail you educational materials, treatment related information and appointment reminders?
                </label>
                <div class="col-xs-4 text-right">
                  <label
                    class="form-switch"
                    :class="{ disabled: form_data.email.trim() === ''}"
                    :checked="form_data.allow_mailing === 'Yes'"
                    @click="toggleRadioButtons($event, 'allow_mailing')"
                  >
                    <input
                      type="checkbox"
                      :disabled="form_data.email.trim() === ''"
                    />
                    <span class="form-switch__slider round"></span>
                  </label>
                  <label class="control-label d-none">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_mailing"
                      :disabled="form_data.email.trim() === ''"
                    />
                    Yes
                  </label>
                  <label class="control-label d-none">
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
              <div class="col-md-5 col-xs-12 row-centered">
                <label
                  class="control-label col-xs-12 col-sm-2 col-md-4 col-lg-3 pf-label"
                  :class="{ 'error-label': errors.has('home_phone') }"
                  >Phone (home):</label
                >
                <div
                  class="col-xs-12 col-sm-10 col-md-8 col-lg-9 pci-form-group"
                >
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
                    @input="allowContact($event, ['allow_home_phone_call'])"
                  ></the-mask>
                  <!--<input type="tel" class="form-control empty-input"-->
                  <!--v-model="form_data.home_phone"-->
                  <!--@change="clearRadio('home_phone','allow_home_phone_call')">-->
                </div>
              </div>
              <div class="col-md-7 row-centered" id="allow_home_phone_call">
                <label class="control-label col-xs-8 switch-label"
                  >Okay to call this number and leave messages?</label
                >
                <div class="col-xs-4 text-right">
                  <label
                    class="form-switch"
                    :class="{ disabled: form_data.home_phone.trim() === ''}"
                    @click="toggleRadioButtons($event, 'allow_home_phone_call')"
                  >
                    <input
                      type="checkbox"
                      :checked="form_data.allow_home_phone_call === 'Yes'"
                      :disabled="form_data.home_phone.trim() === ''"
                    />
                    <span class="form-switch__slider round"></span>
                  </label>
                  <label class="control-label d-none">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_home_phone_call"
                      :disabled="form_data.home_phone.trim() === ''"
                    />
                    Yes
                  </label>
                  <label class="control-label d-none">
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
              <div class="col-md-5 col-xs-12 row-centered">
                <label
                  class="control-label col-xs-12 col-sm-2 col-md-5 col-lg-3 pf-label"
                  :class="{ 'error-label': errors.has('mobile_phone') }"
                  >Phone (mobile):</label
                >
                <div
                  class="col-xs-12 col-sm-10 col-md-7 col-lg-9 pci-form-group"
                >
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
                    @input="
                      allowContact($event, [
                        'allow_mobile_phone_call',
                        'allow_mobile_send_messages',
                      ])
                      requiredOnChange('mobile_phone');
                    "
                  ></the-mask>
                  <!--<input id="mobile_phone" type="tel" class="form-control empty-input"-->
                  <!--v-model="form_data.mobile_phone"-->
                  <!--@change="requiredOnChange('mobile_phone'); clearRadio('mobile_phone','allow_mobile_phone_call'); clearRadio('mobile_phone','allow_mobile_send_messages')"-->
                  <!--required>-->
                </div>
              </div>
              <div
                class="col-md-7 row-centered mt-md"
                id="allow_mobile_phone_call"
              >
                <label class="control-label col-xs-8 switch-label"
                  >Okay to call this number and leave messages?</label
                >
                <div class="col-xs-4 text-right">
                  <label
                    class="form-switch"
                    :class="{ disabled: form_data.mobile_phone.trim() === ''}"
                    @click="
                      toggleRadioButtons($event, 'allow_mobile_phone_call')
                    "
                  >
                    <input
                      type="checkbox"
                      :disabled="form_data.mobile_phone.trim() === ''"
                    />
                    <span class="form-switch__slider round"></span>
                  </label>
                  <label class="control-label d-none">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_mobile_phone_call"
                      :disabled="form_data.mobile_phone.trim() === ''"
                    />
                    Yes
                  </label>
                  <label class="control-label d-none">
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

            <div
              class="row pci-row row-centered mr-0"
              id="allow_mobile_send_messages"
            >
              <div class="col-xs-8 col-sm-9 col-md-10">
                <label class="control-label pf-label switch-label">
                  Okay to send treatment related text messages & appointment
                  reminders to mobile number?
                </label>
              </div>
              <div class="col-xs-4 col-sm-3 col-md-2 text-right">
                <label
                  class="form-switch"
                  :class="{ disabled: form_data.mobile_phone.trim() === ''}"
                  @click="
                    toggleRadioButtons($event, 'allow_mobile_send_messages')
                  "
                >
                  <input
                    type="checkbox"
                    :disabled="form_data.mobile_phone.trim() === ''"
                  />
                  <span class="form-switch__slider round"></span>
                </label>
                <label class="control-label d-none">
                  <input
                    type="radio"
                    value="Yes"
                    v-model="form_data.allow_mobile_send_messages"
                    :disabled="form_data.mobile_phone.trim() === ''"
                  />
                  Yes
                </label>
                <label class="control-label d-none">
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
              <div class="col-md-5 col-xs-12 row-centered">
                <label
                  class="control-label col-xs-12 col-sm-2 col-md-4 col-lg-3 pf-label"
                  :class="{ 'error-label': errors.has('work_phone') }"
                  >Phone (work):</label
                >
                <div
                  class="col-xs-12 col-sm-10col-md-8 col-lg-9 pci-form-group"
                >
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
                    @input="allowContact($event, ['allow_work_phone_call'])"
                  ></the-mask>
                  <!--<input type="tel" class="form-control empty-input"-->
                  <!--v-model="form_data.work_phone"-->
                  <!--@change="clearRadio('work_phone','allow_work_phone_call')">-->
                </div>
              </div>
              <div
                class="col-md-7 row-centered mt-md"
                id="allow_work_phone_call"
              >
                <label class="control-label col-xs-8 switch-label"
                  >Okay to call this number and leave messages?</label
                >
                <div class="col-xs-4 text-right">
                  <label
                    class="form-switch"
                    :class="{ disabled: form_data.work_phone.trim() === ''}"
                    @click="toggleRadioButtons($event, 'allow_work_phone_call')"
                  >
                    <input
                      type="checkbox"
                      :disabled="form_data.work_phone.trim() === ''"
                    />
                    <span class="form-switch__slider round"></span>
                  </label>
                  <label class="control-label d-none">
                    <input
                      type="radio"
                      value="Yes"
                      v-model="form_data.allow_work_phone_call"
                      :disabled="form_data.work_phone.trim() === ''"
                    />
                    Yes
                  </label>
                  <label class="control-label d-none">
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
              <div class="col-lg-5 col-sm-6 col-xs-12 row-centered">
                <label class="control-label col-sm-5 col-xs-12 col-lg-4"
                  >Emergency Contact:</label
                >
                <div class="col-sm-7 col-xs-12 col-lg-8 pci-form-group">
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
              <div class="col-lg-3 col-sm-6 col-xs-12 row-centered">
                <label
                  class="control-label col-sm-2 col-xs-12 col-lg-4"
                  :class="{
                    'error-label': errors.has('emergency_contact_phone'),
                  }"
                  >Phone:</label
                >
                <div class="col-sm-10 col-xs-12 col-lg-8 pci-form-group">
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
                    @input="requiredOnChange('emergency_contact_phone');"
                  ></the-mask>
                </div>
              </div>
              <div class="col-lg-4 col-sm-12 col-xs-12 row-centered mt-lg">
                <label
                  class="control-label col-md-2 col-sm-2 col-xs-12 col-lg-4"
                  >Relationship:</label
                >
                <div
                  class="col-md-10 col-sm-10 col-xs-12 col-lg-8 pci-form-group"
                >
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
            <div id="required_only_one" class="form-checkboxes-section">
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
            </div>
            <div
              id="required_only_one_referred"
              class="form-checkboxes-section"
            >
              <div class="row pci-row">
                <div class="col-xs-12 bold-label">
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="referred_by"
                      />
                    </span>
                    I was referred by:
                  </label>
                </div>
                <div
                  class="col-xs-12 collapse-checkboxes"
                  id="checkboxesCollapse"
                >
                  <label class="control-label checkbox-label bold-label">
                    <span class="checkbox-wrapper">
                      <input
                        type="checkbox"
                        class="form-control empty-field checkbox-inline custom-checkbox"
                        v-model="form_data.friend_or_relative"
                        :disabled="
                          one_referred_checked && !form_data.friend_or_relative
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
                          one_referred_checked &&
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
                        :disabled="one_referred_checked && !form_data.kaiser"
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
                            one_referred_checked &&
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
        </form>
      </div>
    </div>
  </div>
</template>

<script>
const DATA_FIELDS = {
  email: ["allow_mailing"],
  home_phone: ["allow_home_phone_call"],
  mobile_phone: ["allow_mobile_phone_call", "allow_mobile_send_messages"],
  work_phone: ["allow_work_phone_call"],
};

export default {
  name: "NewPatientForm",
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    hear_about_us_checked: false,
    form_data: null,
    referred_by: false,
    one_referred_checked: false,
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
    validation_messages: {
      current: "",
      required: "Please make sure you have filled all the required fields.",
      incorrect_password: "You have entered incorrect PIN code.",
      try_again: "Error! Please try again.",
      required_one_method: "At least one method of communication must be allowed"
    },
    required_only_one_checkbox: [
      "yelp",
      "google",
      "yellow_pages",
      "event_i_attended",
      "hear_about_us_other",
    ],
    required_only_one_referred_checkbox: [
      "friend_or_relative",
      "another_professional",
      "kaiser",
      "referred_by_other_insurance",
    ],
    allow_contact: {
      allow_mailing: "",
      allow_home_phone_call: "",
      allow_mobile_phone_call: "",
      allow_mobile_send_messages: "",
      allow_mailing: "",
    },
  }),
  methods: {
    allowContact(value, keys) {
      for (let key of keys) {
        let container = document.querySelector(`#${key}`);
        let yesNoCheckboxes = container.querySelectorAll(
          ".control-label.d-none"
        );
        let sliderCheckbox = container.querySelector("input");
        if (!value) {
          this.form_data[key] = "";
          sliderCheckbox.checked = false;
          yesNoCheckboxes[0].checked = false;
        } else {
          if (this.allow_contact[key]) {
            let checkboxId = this.allow_contact[key] === "Yes" ? 0 : 1;
            this.form_data[key] = this.allow_contact[key];
            sliderCheckbox.checked =
              this.allow_contact[key] === "Yes" ? true : false;
            yesNoCheckboxes[checkboxId].click();
          } else {
            this.form_data[key] = "Yes";
            sliderCheckbox.checked = true;
            yesNoCheckboxes[0].click();
            this.removeErrorFromMethodsLabels();
          }
        }
      }
    },
    requiredOnChange(field) {
      if (this.form_data[field].trim() !== "" && !this.errors.has(field)) {
        $("#" + field)
          .removeClass("input-error")
          .parents("div")
          .prev("label")
          .removeClass("label-error");
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
    clearRadio(el, radio) {
      if (this.form_data[el].trim() === "") {
        this.form_data[radio] = "";
      }
    },
    toggleRadioButtons(event, key) {
      let sliderLabel = event.target.parentElement;
      let sliderCheckbox = sliderLabel.querySelector("input");
      let yesNoCheckboxes = sliderLabel.parentElement.querySelectorAll(".control-label.d-none");
      let methodMainEl = document.getElementById(`${key}`);
      let methodMainLabel = methodMainEl.querySelector(".switch-label")

      if (sliderCheckbox.checked) {
        yesNoCheckboxes[0].click();
        this.allow_contact[key] = "Yes";
      } else {
        const atLeastOneMethodChecked = Array.from(document.querySelectorAll('.form-switch input'))
          .some(checkbox => checkbox.checked);
        if (atLeastOneMethodChecked) {
          yesNoCheckboxes[1].click();
          this.allow_contact[key] = "No";
          this.removeErrorFromMethodsLabels();
        } else {
          this.$emit("validation-fails", this.validation_messages.required_one_method);
          sliderCheckbox.checked = true;
          methodMainLabel.classList.add('label-error');
        }
      }
    },

    removeErrorFromMethodsLabels() {
      let methodLabels = document.querySelectorAll('.switch-label');
      methodLabels.forEach(label => {
        label.classList.remove('label-error');
      });
      this.$emit("validation-fails", "");
    },

    validateForm() {
      let has_errors = false;
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

      if (this.referred_by) {
        let referred_checked = false;
        for (
          let i = 0;
          i < this.required_only_one_referred_checkbox.length;
          i++
        ) {
          let val = this.form_data[this.required_only_one_referred_checkbox[i]];
          if (val === true) {
            referred_checked = true;
            break;
          }
        }
        if (!referred_checked) {
          $("#required_only_one_referred .bold-label").addClass("label-error");
          $("#required_only_one_referred label").addClass("label-error");
          has_errors = true;
        }
      } else {
        $("#required_only_one_referred .bold-label").removeClass("label-error");
        $("#required_only_one_referred label").removeClass("label-error");
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

      if (this.errors.items.length !== 0) {
        has_errors = true;
      }

      if (has_errors) {
        this.$emit("validation-fails", this.validation_messages.required);
      } else {
        this.removeErrorFromMethodsLabels();
        this.$emit("validation-success", "");
      }
    },
    removeErrorClassesFromCheckboxes(id) {
      $(`${id} .bold-label`).removeClass("label-error");
      $(`${id} label`).removeClass("label-error");
    },
  },
  watch: {
    referred_by(val) {
      if (val) {
        this.$el.querySelector("#checkboxesCollapse").classList.add("open");
      } else {
        this.$el.querySelector("#checkboxesCollapse").classList.remove("open");
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
    "form_data.yelp": function () {
      if (this.form_data.yelp) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one");
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.google": function () {
      if (this.form_data.google) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one");
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.yellow_pages": function () {
      if (this.form_data.yellow_pages) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one");
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.event_i_attended": function () {
      if (this.form_data.event_i_attended) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one");
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.hear_about_us_other": function () {
      if (this.form_data.hear_about_us_other) {
        this.hear_about_us_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one");
      } else {
        this.hear_about_us_checked = false;
      }
    },

    "form_data.friend_or_relative": function () {
      if (this.form_data.friend_or_relative) {
        this.one_referred_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one_referred");
      } else {
        this.one_referred_checked = false;
      }
    },

    "form_data.another_professional": function () {
      if (this.form_data.another_professional) {
        this.one_referred_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one_referred");
      } else {
        this.one_referred_checked = false;
      }
    },

    "form_data.kaiser": function () {
      if (this.form_data.kaiser) {
        this.one_referred_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one_referred");
      } else {
        this.one_referred_checked = false;
      }
    },

    "form_data.referred_by_other_insurance": function () {
      if (this.form_data.referred_by_other_insurance) {
        this.one_referred_checked = true;
        this.removeErrorClassesFromCheckboxes("#required_only_one_referred");
      } else {
        this.one_referred_checked = false;
      }
    },
  },
  mounted() {
    this.form_data = this.data;
    this.$nextTick(() => {
      for (let key in DATA_FIELDS) {
        if (this.form_data[key]) {
          this.allowContact(this.form_data[key], DATA_FIELDS[key]);
        }
      }
    });
  },
};
</script>

<style scoped></style>
