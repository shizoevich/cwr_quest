<template>
  <div class="profile-row">
    <div class="profile-title">{{ title }}:</div>
    <div class="profile-value d-flex flex-column">
      <div>
        <span>
          <a :href="`${'tel:' + phone}`" v-if="phone">{{ phone | formatPhone }}</a>
          <span v-else>-</span>
          <span>
            <span v-if="phone_label">
              <el-tooltip class="item" effect="dark" placement="right">
                <template #content>
                  {{ phone_label }}
                </template>
                <help />
              </el-tooltip>
            </span>
            <i
              v-if="show_edit_button"
              class="fa fa-pencil fa-relationship-button"
              @click.prevent="openModal"
            ></i>
          </span>
        </span>
      </div>

      <div
          v-for="additional_phone in additional_phones"
          :key="additional_phone.id"
      >
        <div>
          <span class="position-relative">
            <a :href="`${'tel:' + additional_phone.phone}`">{{ additional_phone.phone | formatPhone }}</a>
            <span style="right: -16px; top: -6px;" class="position-absolute">
              <el-tooltip class="item" effect="dark" placement="right">
                <template #content>
                  {{ additional_phone.label }}
                </template>
                <help />
              </el-tooltip>
            </span>
          </span>
        </div>
      </div>
    </div>

    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         :id="modal_id" role="dialog" v-if="show_edit_button && modal_id">
      <div class="modal-dialog">
        <div class="modal-content" v-loading="saving">
          <div class="modal-header">
            <h4 class="modal-title">Change {{ title }}</h4>
          </div>
          <div class="modal-body">
            <div class="phone__item">
              <div
                  class="input-group input-group_fixed-width"
                  :class="{
                  'has-error': new_phone_error,
                }"
              >
                <label>
                  {{ title }}
                </label>

                <input type="text"
                       class="form-control"
                       @keydown.enter.prevent
                       v-validate="'numeric|digits:10'"
                       v-mask="'(###)-###-####'"
                       :masked="true"
                       v-model="new_phone"
                />

                <div v-if="new_phone_error" class="invalid-feedback">
                  {{ new_phone_error }}
                </div>
              </div>
              <div class="input-group input-group_fixed-width">
                <label>
                  Add Label
                </label>

                <input type="text"
                       class="form-control"
                       placeholder="Label..."
                       @keydown.enter.prevent
                       v-validate="'required'"
                       v-model="new_phone_label"
                >
              </div>
              <div class="icon_fixed-width icon_centered" style="margin-top: 28px;">
                <i class="fa fa-plus fa-lg fa-relationship-button" @click.prevent="addNewAdditionalPhone" />
              </div>
            </div>

            <div v-if="new_additional_phones.length" class="additional_phones">
              <div class="additional_phones__caption font-bold">Additional phones:</div>

              <div class="additional_phones_list">
                <div
                    v-for="additional_phone in new_additional_phones"
                    :key="additional_phone.index"
                    class="phone__item"
                >
                  <div
                      class="input-group input-group_fixed-width"
                      :class="{
                      'has-error': additional_phone.errors.phone,
                    }"
                  >
                    <input type="text"
                           class="form-control"
                           @keydown.enter.prevent
                           v-validate="'numeric|digits:10'"
                           v-mask="'(###)-###-####'"
                           placeholder="(###)-###-####"
                           :masked="true"
                           v-model="additional_phone.phone"
                    />
                    <div v-if="additional_phone.errors.phone" class="invalid-feedback">
                      {{ additional_phone.errors.phone }}
                    </div>
                  </div>

                  <div
                      class="input-group input-group_fixed-width"
                      :class="{
                      'has-error': additional_phone.errors.label,
                    }"
                  >
                    <input type="text"
                           class="form-control"
                           placeholder="Label..."
                           @keydown.enter.prevent
                           v-validate="'required'"
                           v-model="additional_phone.label"
                    >
                    <div v-if="additional_phone.errors.label" class="invalid-feedback">
                      {{ additional_phone.errors.label }}
                    </div>
                  </div>

                  <div class="icon_fixed-width icon_centered">
                    <i
                        class="fa fa-close fa-lg fa-relationship-button"
                        @click.prevent="() => removeNewAdditionalPhone(additional_phone.index)"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" @click.prevent="changePatientPhone()">
              Save
            </button>
            <button type="button" class="btn btn-default" @click.prevent="closeModal()">
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {Notification} from "element-ui";
import { mask } from "vue-the-mask";

export default {
    directives: {mask},
  props: {
    title: {
      required: true,
      type    : String,
    },
    phone: {
      required: true,
    },
    phone_label: {
      required: true,
    },
    additional_phones: {
      required: true,
      type: Array,
    },
    show_edit_button: {
      required: true,
      type: Boolean,
    },
    patient_id: {
      required: true,
    },
    field_name: {
      required: true,
      type    : String,
    },
  },

  data() {
    return {
      modal_id: null,
      new_phone: '',
      new_phone_label: '',
      new_phone_error: '',
      saving: false,
      new_additional_phones: [],
      new_additional_phone_index: 0,
    };
  },

  beforeMount() {
    this.modal_id = 'edit-phone-' + Math.random().toString(16).substring(2,7);
    this.initializeData();
  },

  watch: {
    phone() {
      this.new_phone = this.phone;
    },
  },

  methods: {
    initializeData() {
      this.new_phone = this.phone;
      this.new_phone_label = this.phone_label;
      this.new_additional_phones = this.additional_phones.map(item => ({
        id: item.id,
        index: this.new_additional_phone_index++,
        phone: item.phone,
        label: item.label,
        errors: {
          phone: '',
          label: '',
        },
      }));
      this.new_phone_error = '';
    },

    openModal() {
      this.initializeData();
      $('#' + this.modal_id).modal('show');
    },

    closeModal() {
      $('#' + this.modal_id).modal('hide');
      this.initializeData();
    },

    changePatientPhone() {
      if (! this.validate()) {
        return;
      }

      this.saving = true;
      this.$store.dispatch('updatePatient', {
        patientId: this.patient_id,
        [this.field_name]: this.new_phone,
        [this.field_name + '_label']: this.new_phone_label,
        additional_phones: this.new_additional_phones.map(item => ({
          id: item.id,
          phone: item.phone,
          label: item.label,
          phone_type:  this.field_name,
        })),
      }).then(() => {
        this.$store.dispatch('getPatient', {patientId: this.patient_id});
        this.closeModal();
        Notification.success({title: 'Success', message: this.title + ' successfully updated.', type: 'success'});
      }).finally(() => {
        this.saving = false;
      });
    },

    addNewAdditionalPhone() {
      this.new_additional_phones.push({
        id: null,
        index: this.new_additional_phone_index++,
        phone: '',
        label: '',
        errors: {
          phone: '',
          label: '',
        },
      });
    },

    removeNewAdditionalPhone(new_additional_phone_index) {
      const index = this.new_additional_phones.indexOf(item => item.index === new_additional_phone_index);
      this.new_additional_phones.splice(index, 1);
      this.new_additional_phone_index--;
    },

    validate() {
      let hasError = false;

      if (this.new_phone && this.new_phone.length !== 14) {
        this.new_phone_error = 'The phone is invalid';
        hasError = true;
      } else {
        this.new_phone_error = '';
      }

      this.new_additional_phones.forEach(item => {
        if (!item.label) {
          item.errors.label = 'The label is required';
          hasError = true;
        } else {
          item.errors.label = ''
        }

        if (!item.phone) {
          item.errors.phone = 'The phone is required';
          hasError = true;
        } else if (item.phone && item.phone.length !== 14) {
          item.errors.phone = 'The phone is invalid';
          hasError = true;
        } else {
          item.errors.phone = ''
        }
      });

      return ! hasError;
    },
  }
}
</script>

<style scoped>
.form-control {
  border-radius: 4px !important;
}

.additional_phones {
  margin-top: 5px;
}

.phone__item {
  display: flex;
  gap: 5px;
}

.phone__item + .phone__item, .btn-add_new_additional_phone {
  margin-top: 5px;
}

.input-group_fixed-width {
  width: 46%;
}

.icon_fixed-width {
  width: 5%;
}
.icon_centered {
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-add_new_additional_phone {
  display: flex;
  align-items: center;
  justify-content: center;
  width: calc(92% + 5px);
}
</style>