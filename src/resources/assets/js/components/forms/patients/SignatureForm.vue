<template>
  <div class="container" v-if="form_data">
    <div class="patient-contact-info-container confidential-info-form">
      <div class="section section-add-note" style="margin-bottom:60px;">
        <form
          class="form-note form-horizontal patient-contact-info-form"
          autocomplete="off"
          id="form-note"
          novalidate
        >
          <div class="inp-group">
            <div class="row pci-row">
              <div class="col-xs-12" data-signature="patient-signature">
                <div class="signature-title" style="margin-top: 0;">
                  <div class="row">
                    <div class="col-xs-10">
                      <h4 id="signature-title">Signature of Patient</h4>
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
            <div class="row" style="margin-bottom: 20px; margin-top: 75px;" v-if="this.yearsOld <= 18">
              <div class="col-xs-8">
                <label class="control-label col-md-6 col-lg-5 pf-label"
                  >Name of parent/guardian/representative:</label
                >
                <div class="col-md-6 col-lg-7 pci-form-group">
                  <input
                    id="guardian_name"
                    type="text"
                    class="form-control empty-input"
                    autocomplete="new-password"
                    v-model="form_data.guardian_name"
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
            <div class="row pci-row" v-if="this.yearsOld <= 18">
              <div
                class="col-xs-12"
                style="margin-bottom: 50px;"
                data-signature="patient-signature18"
              >
                <div class="signature-title" style="margin-top: 0;">
                  <div class="row">
                    <div class="col-xs-10">
                      <h4 id="signature18-title">
                        Signature of parent/guardian/representative
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
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "SignatureForm",
  props: {
    forms: {
      type: Object,
      required: true,
      default: null,
    },
    data: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    signature_is_empty: true,
    signature18_is_empty: true,
    form_data: null,
    validation_messages: {
      current: "",
      required: "Please make sure you have filled all the required fields.",
      incorrect_password: "You have entered incorrect PIN code.",
      try_again: "Error! Please try again.",
    },
    statuses: {
      saving: false,
    },
    required_fields: [],
    form_data_inited: false,
  }),
  computed: {
    patient() {
      return this.$store.state.currentPatient;
    },
    patientForms() {
      return this.$store.state.patientForms;
    },
    yearsOld() {
      if(!this.patient.date_of_birth) {
        return 0;
      }

      let diff = new Date(
        Date.now() - new Date(this.patient.date_of_birth).getTime()
      );
      return Math.abs(diff.getUTCFullYear() - 1970);
    },
    signatureData() {
      if (this.yearsOld >= 15) {
        return { signature: this.form_data.signature };
      } else {
        return this.form_data;
      }
    },
  },
  methods: {
    clearSignature(name) {
      $("#" + name).jSignature("clear");
      if (name === "patient-signature") {
        this.signature_is_empty = true;
      } else if (name === "patient-signature-under-18") {
        this.signature18_is_empty = true;
      }
    },
    saveForm() {
        try {
            this.$emit('add-loader');
            this.statuses.saving = true;
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

            let dataToTransform = {
                forms: this.forms,
                signature_data: this.signatureData,
            };
            let data = this.prepareFormData(dataToTransform);

            this.$store
                .dispatch("safeStorePatientForms", {
                    data: data,
                    patient_id: this.patient.id,
                    hash: this.$route.params.hash,
                })
                .then((response) => {
                    this.statuses.saving = false;
                    this.$router.push("/f/" + this.$route.params.hash + "/download");
                })
                .catch((error) => {
                    // @todo remove
                    try {
                        this.logAdditionalData(`METHOD: saveForm; CATCH: ${JSON.stringify(error)}`);
                    } catch (e) {}

                    if(error.response.status === 422 && (typeof error.response.data) === 'object') {
                        this.$emit("validation-fails", error.response.data[Object.keys(error.response.data)[0]][0]);
                    } else {
                        this.$emit("validation-fails", this.validation_messages.try_again);
                    }
                })
                .finally(() => {
                    this.statuses.saving = false;
                    this.removeLoader();
                });
        } catch (err) {
            // @todo remove
            try {
                this.logAdditionalData(`METHOD: saveForm; ERROR: ${err}`);
            } catch (e) {}

            throw err;
        }
    },

    removeLoader() {
      let loader = document.querySelector(".loader-page");
      document.querySelector("html").classList.remove("document-loader");
      loader.parentNode.removeChild(loader);
    },

    validateForm() {
        try {
            let has_errors = false;
            if (this.signature_is_empty) {
                has_errors = true;
                $("#signature-title").addClass("label-error");
                $("#patient-signature").addClass("input-error");
                $('div[data-signature="patient-signature"] hr.signature-line').addClass("signature-line-error");
            }

            if (this.yearsOld < 15) {
                if (this.signature18_is_empty) {
                    has_errors = true;
                    $("#signature18-title").addClass("label-error");
                    $("#patient-signature-under-18").addClass("input-error");
                    $('div[data-signature="patient-signature18"] hr.signature-line').addClass("signature-line-error");
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

            if (has_errors) {
                // @todo remove
                try {
                    this.logAdditionalData(`METHOD: validateForm; VALIDATION_FAILS: ${JSON.stringify(this.form_data)}`);
                } catch (e) {}

                this.$emit("validation-fails", this.validation_messages.required);
            } else {
                this.$emit("validation-success");
            }
        } catch (err) {
            // @todo remove
            try {
                this.logAdditionalData(`METHOD: validateForm; ERROR: ${err}`);
            } catch (e) {}

            throw err;
        }
    },
    prepareFormData(data) {
      let formData = new FormData();
      for (let key in data) {
        this.addObjectToFormData(data[key], formData, `${key}`);
      }

      return formData;
    },
    addObjectToFormData(obj, formData, key) {
      if (Array.isArray(obj)) {
        for (let itemIndex in obj) {
          this.addObjectToFormData(
            obj[itemIndex],
            formData,
            `${key}[${itemIndex}]`
          );
        }
      } else if (obj instanceof File) {
        formData.append(key, obj);
      } else if (obj instanceof Object && Object.keys(obj).length > 0) {
        for (let objKey in obj) {
          this.addObjectToFormData(obj[objKey], formData, `${key}[${objKey}]`);
        }
      } else {
        formData.append(key, obj);
      }
    },

    // @todo remove this method
    logAdditionalData(message) {
        if (!this.patient) {
            return;
        }

        const messagePrefix = `PATIENT_ID: ${this.patient && this.patient.id}; ======> `;

        this.$store.dispatch('captureFrontendMessage', {message: (messagePrefix + message)});
    }
  },

  watch: {
    "statuses.saving"() {
      this.$emit("saving", this.statuses.saving);
    },
    'form_data.relationship'() {
      $('#relationship')
        .removeClass("input-error")
        .parents("div")
        .prev("label")
        .removeClass("label-error");
    },
    'form_data.guardian_name'() {
      $('#guardian_name')
        .removeClass("input-error")
        .parents("div")
        .prev("label")
        .removeClass("label-error");
    },
  },
  beforeMount() {
    this.form_data = this.data;
  },

  mounted() {
    window.setTimeout(() => {
      var tmp = this;
      $("#patient-signature")
        .jSignature()
        .bind("change", function (e) {
          tmp.signature_is_empty = false;
          $("#signature-title").removeClass("label-error");
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
          $(
            'div[data-signature="patient-signature18"] hr.signature-line'
          ).removeClass("signature-line-error");
        });
      $("#enter-password-modal").bind("shown.bs.modal", function () {
        $("#password").focus();
      });
    }, 500);
  },
};
</script>

<style scoped></style>
