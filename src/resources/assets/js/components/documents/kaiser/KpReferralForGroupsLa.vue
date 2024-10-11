<template>
    <div>
        <div class="black-layer" v-if="statuses && statuses.saving" style="position: fixed;
top: 0;">
            <pageloader add-classes="saving-loader" image-alt="Saving..."></pageloader>
        </div>

        <div class="modal modal-vertical-center fade" :id="document_name" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-note">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-lg-12">
                                <button type="button" class="close" data-dismiss="modal"
                                        @click.prevent="closeDocument"
                                >&times;</button>
                                <h4 class="modal-title" v-html="computed_modal_title()"></h4>
                            </div>
                        </div>
                        <div class="row last-row">
                            <div class="col-lg-12 text-left">
                                Date: {{getFormattedDate()}}
                            </div>
                            <div class="col-lg-12">
                                <h4 class="modal-title">
                                    Los Angeles Medical Center Service Area
                                    <br>
                                    Referral for Groups
                                </h4>
                            </div>
                            <div class="col-lg-12">
                                <p>
                                    Submit via encrypted attachment to:
                                    <a href="mailto:LAMC-OSM-BH@kp.org">LAMC-OSM-BH@kp.org</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="section section-add-note">
                            <form class="form-note from-document" novalidate>

                                <div class="row input-row">
                                    <document-input
                                            name="first_name"
                                            label="Firstname"
                                            size="col-md-4"
                                            v-model="form_data.first_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="last_name"
                                            label="Lastname"
                                            size="col-md-4"
                                            v-model="form_data.last_name"
                                            @change="setHasValue"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            name="mrn"
                                            label="MRN"
                                            size="col-md-4"
                                            v-model="form_data.mrn"
                                            @change="setHasValue"
                                            :disabled="statuses.editingDisabled"
                                            validateRules="required"
                                    ></document-input>
                                </div>
                                <div class="row">
                                  <div class="form-group input-container col-md-4 radio-without-label radio-recommend-treatment">
                                    <div class="col-lg-12">
                                      Initial Assessment Attached via
                                    </div>
                                    <div class="col-lg-12 radio">
                                      <label class="radio-inline">
                                        <input type="radio"
                                               v-model="form_data.ia_attached_via_rb"
                                               value="email"
                                               @change="setHasValue"
                                               :disabled="statuses.editingDisabled"
                                        >
                                        Email
                                      </label>

                                      <label class="radio-inline">
                                        <input type="radio"
                                               v-model="form_data.ia_attached_via_rb"
                                               value="tridiuum"
                                               @change="setHasValue"
                                               :disabled="statuses.editingDisabled"
                                        >
                                        Tridiuum
                                      </label>
                                    </div>
                                  </div>
                                  <document-textarea
                                    label="Clinical Rationale for Group Referral"
                                    size="col-md-8"
                                    v-model="form_data.clinical_rationale_for_group_referral"
                                    @change="setHasValue"
                                    :disabled="statuses.editingDisabled"
                                  ></document-textarea>
                                </div>

                                <br/>

                                <div class="row ">
                                    <label>Adults</label>
                                </div>

                                <div class="row row--flex">
                                  <div class="form-group form-group-bordered col-md-12 fix-row-1" style="padding-top: 10px!important;padding-bottom: 10px!important;">
                                    <table class="table borderless">
                                      <tbody>
                                      <tr>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.covid19_support_cb" :disabled="statuses.editingDisabled"
                                                   @change="setHasValue">
                                            CoVid 19 Support Group – Hope and Healing
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.overcoming_anxiety_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Overcoming your Anxiety
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.iop_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Intensive Outpatient Program (IOP)
                                          </label>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.gender_confirming_support_cb" :disabled="statuses.editingDisabled"
                                                   @change="setHasValue">
                                            Express Yourself for Gender Confirming Support
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.adhd_workshop_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            ADHD Workshop
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.intensive_case_management_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Intensive Case Management
                                          </label>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.post_partum_cb" :disabled="statuses.editingDisabled"
                                                   @change="setHasValue">
                                            Post-Partum
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.dbt_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            DBT
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.crisis_group_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Crisis Group (English and Spanish)
                                          </label>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.emotional_overeating_cb" :disabled="statuses.editingDisabled"
                                                   @change="setHasValue">
                                            Emotional Overeating
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.parenting_skills_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Parenting Skills
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.seniors_connection_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Seniors Connection
                                          </label>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.work_clinic_cb" :disabled="statuses.editingDisabled"
                                                   @change="setHasValue">
                                            Work Clinic
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.mood_boosters_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Mood Boosters
                                          </label>
                                        </td>
                                        <td>

                                        </td>
                                      </tr>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>

                                <br/>

                                <div class="row ">
                                  <label>Children / Adolescents</label>
                                </div>

                                <div class="row row--flex">
                                  <div class="form-group form-group-bordered col-md-12 fix-row-1" style="padding-top: 10px!important;padding-bottom: 10px!important;">
                                    <table class="table borderless">
                                      <tbody>
                                      <tr>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.coping_skills_age_11_14_cb" :disabled="statuses.editingDisabled"
                                                   @change="setHasValue">
                                            Coping Skills – Age 11-14
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.coping_skills_high_school_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Coping Skills – High School
                                          </label>
                                        </td>
                                        <td>
                                          <label class="checkbox-inline">
                                            <input type="checkbox" class="checkbox-form-control"
                                                   v-model="form_data.teen_iop_cb" @change="setHasValue" :disabled="statuses.editingDisabled">
                                            Teen IOP
                                          </label>
                                        </td>
                                      </tr>
                                      </tbody>
                                    </table>
                                  </div>
                                </div>
                                <div class="row ">
                                  <b style="font-size:12px;"><i>Bereavement groups available through Hospice Department, <a href="tel:2135339530">(213)533-9530</a>. Patients can self-refer</i></b>
                                </div>
                                <br />

                                <div class="row ">
                                    <label>External Provider Information</label>
                                </div>
                                <div class="row  input-row">
                                    <document-input
                                            name="provider_name"
                                            label="Provider Name"
                                            size="col-md-4"
                                            v-model="form_data.provider_name"
                                            :disabled="true"
                                    ></document-input>
                                    <document-input
                                            label="Provider Email"
                                            size="col-md-4"
                                            v-model="form_data.provider_email"
                                    ></document-input>
                                    <document-input
                                      label="Provider Phone"
                                      size="col-md-4"
                                      v-model="form_data.provider_phone"
                                      v-mask="'###-###-####'"
                                    ></document-input>
                                </div>


                                <div class="form-note-button-block">
                                    <div class="row">
                                        <div class="col-lg-12 text-center" style="padding-right:0;margin-bottom:15px;">
                                        <span class="text-red validation-error-msg" v-if="statuses.noErrors === false && !validation_message">
                                            Please make sure you have filled all the required fields.
                                        </span>
                                            <span class="text-red validation-error-msg" v-if="validation_message">
                                                {{validation_message}}
                                        </span>
                                        </div>

                                        <div class="col-lg-12 text-right" style="padding-right:0;">
                                            <div class="col-lg-12" style="padding-right:0;">
                                                <button type="submit" class="btn btn-primary document-button"
                                                        @click.prevent="saveDocument"
                                                        v-if="!statuses.editingDisabled"
                                                >
                                                    Save
                                                </button>

                                                <button type="button" class="btn btn-default document-button"
                                                        @click.prevent="closeDocument"
                                                >
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!--/.modal-content-->
            </div>
        </div>
    </div>
</template>

<script>
  import validate from './../../../mixins/validate';
  import save from './../../../mixins/save-document-if-valid';
  import methods from './../../../mixins/document-methods';

  export default {
    mixins: [validate, save, methods],
    data(){
      return{
        document_name: 'kp-ref-for-groups-la',
        document_title: 'Referral for Groups',
      }
    },
    beforeMount(){
      if(!this.form_data && this.$store.state.currentDocument){
        this.patient_id = this.$store.state.currentPatient.id;
        this.form_data = {
          first_name: this.$store.state.currentPatient.first_name,
          last_name: this.$store.state.currentPatient.last_name,
          date: this.formatDate(new Date(), this.momentDateFormat),
          mrn: this.$store.state.currentPatient.subscriber_id,
          ia_attached_via_rb: null,
          clinical_rationale_for_group_referral: null,
          covid19_support_cb: false,
          overcoming_anxiety_cb: false,
          iop_cb: false,
          gender_confirming_support_cb: false,
          adhd_workshop_cb: false,
          intensive_case_management_cb: false,
          post_partum_cb: false,
          dbt_cb: false,
          crisis_group_cb: false,
          emotional_overeating_cb: false,
          parenting_skills_cb: false,
          seniors_connection_cb: false,
          work_clinic_cb: false,
          mood_boosters_cb: false,
          coping_skills_age_11_14_cb: false,
          coping_skills_high_school_cb: false,
          teen_iop_cb: false,
          provider_name: this.parseProviderName(this.currentProvider.provider_name),
          provider_email: this.currentProvider.email,
          provider_phone: this.currentProvider.phone,
        }
      }
    },
    mounted() {
      let self = this;
      let menu_item_selector = 'kp-ref-for-groups-la';
      let document_name = self.getFormName(menu_item_selector);
      self.document_name = document_name;
      self.document_title = self.getFormTitle(menu_item_selector);

      window.setTimeout(() => {
        $('#'+this.document_name).on('shown.bs.modal', function() {
          $('body').addClass('custom-modal');

          autosize($('#'+document_name).find('textarea'));

          $('.input-container').on('click', function(){

            $(this).find('.input-element').focus();
          });

          $('#'+document_name).find('input.el-input__inner').addClass('input-element');
        }).on('hidden.bs.modal', function() {
          $('body').removeClass('custom-modal');
        });
      },500);
    },
    methods: {
      getCustomValidation(){

        let error = false;
        //

        return error;
      },
    },
  }
</script>