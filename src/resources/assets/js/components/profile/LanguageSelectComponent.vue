<template>
  <div class="profile-row">
    <div class="profile-title">Language:</div>
    <div class="profile-value">
       <span v-if="title">{{ title }} </span>
      <i
        style="margin-left:2px; margin-top: 2px; z-index: 10"
        class="fa fa-pencil fa-relationship-button"
        v-if="show_edit_button"
        @click.prevent="openModal"
      ></i>
    </div>

    <div
      class="modal modal-vertical-center fade"
      data-backdrop="static"
      data-keyboard="false"
      :id="modal_id"
      role="dialog"
      v-if="show_edit_button && modal_id"
    >
      <div class="modal-dialog">
        <div class="modal-content" v-loading="saving">
          <div class="modal-header">
            <h4 class="modal-title">Change Language</h4>
          </div>
          <div class="modal-body">
            <label class="language_label">
              Language:
              <div class="row">
                <div class="form-col form-col-4 ml-5">
                  <select v-model="language_prefer" class="language-select">
                    <option
                      v-for="item in listLanguages"
                      v-bind:value="item.title"
                      :key="item.id"
                    >
                      {{ item.title }}
                    </option>
                  </select>
                </div>
              </div>
            </label>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-primary"
              @click.prevent="changePatientLanguage()"
            >
              Save
            </button>
            <button
              type="button"
              class="btn btn-default"
              @click.prevent="closeModal()"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Notification } from "element-ui";

export default {
  props: {
    title: {
      required: true,
      type: String,
    },
    show_edit_button: {
      required: true,
      type: Boolean,
    },
    patient_id: {
      required: true,
    },
    languagesList: {
      required: true,
      type: Array,
    },
  },
  data() {
    return {
      modal_id: null,
      saving: false,
      listLanguages: this.languagesList,
      language_prefer: "",
    };
  },
  beforeMount() {
    this.modal_id =
      "edit-language-" + Math.random().toString(16).substring(2, 7);
      this.language_prefer = this.title;
  },
  watch: {
    title() {
       this.language_prefer = this.title;
    },
  },
  methods: {
    openModal() {
      $("#" + this.modal_id).modal("show");
    },

    closeModal() {
      $("#" + this.modal_id).modal("hide");
    },
    changePatientLanguage() {
      this.saving = true;
      this.$store
        .dispatch("updatePatientLanguage", {
          patient_id: this.patient_id,
          language_prefer: this.language_prefer,
        })
        .then(() => {
          this.$store.dispatch("getPatient", {
            patientId: this.patient_id,
          });
          this.closeModal();
          Notification.success({
            title: "Success",
            message: this.language_prefer + " successfully updated.",
            type: "success",
          });
        })
        .finally(() => {
          this.saving = false;
        });
    },
  },
};
</script>
<style scoped>
.language-select {
  display: block;
  width: 254px;
  margin-left: 15px;
  margin-top: 1px;
  height: 36px;
  padding: 6px 12px;
  font-size: 14px;
  line-height: 1.6;
  color: #555555;
  background-color: #fff;
  background-image: none;
  border: 1px solid #a3aebc;
  border-radius: 4px;
  box-shadow: inset 0 1px 1px rgb(0 0 0 / 8%);
}
.language-title {
  color: #3f3f3f;
  padding: 0px 3px 0px 0px;
  word-wrap: break-word;
}
</style>
