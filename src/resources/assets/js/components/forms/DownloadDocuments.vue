<template>
    <patient-form-layout ref="downloadLayoutPage" class="page-download" @showDownloadPage="showDownloadPage" @invalidPassword="setInvalidPassword">
      <template v-slot:content v-if="$route.name === 'secure-download-forms' && !show_download_page">
        <p class="patient-form__body-text">For getting access to documents enter password below</p>
        <div class="row">
          <div class="col-md-4 col-md-offset-4">
            <div class="form-group" :class="{'has-error': errors.has('password') || invalid_get_access_password}">
              <label for="password" class="control-label">Password</label>
              <input type="text"
                     id="password"
                     v-model="get_access_password"
                     name="password"
                     class="form-control input-lg"
                     data-vv-validate-on="change"
                     v-validate="'required|max:50'"
                     @keydown.enter="getDocuments"
                     autofocus>
              <span class="help-block" v-if="errors.has('password')">{{ errors.first('password') }}</span>
              <span class="help-block" v-else-if="invalid_get_access_password">Invalid Password</span>
            </div>
          </div>
          <div class="col-md-4 col-md-offset-4 text-center" style="margin-top:25px;">
            <button class="btn btn-lg btn-success btn-download-all" @click="getDocuments">Get Access</button>
          </div>
        </div>
      </template>
      <template v-slot:content v-else-if="$route.name === 'download-forms' || show_download_page">
        <h2 class="patient-form__body-title">
          Thanks for filling out all the forms!
        </h2>
        <div class="patient-form__body-icon">
          <img src="/images/icons/icon-user-talk.svg" alt="Conversation" />
        </div>
        <p class="patient-form__body-text" v-if="visiblePatientForms.length > 0">
          <img
            src="/images/icons/icon-check.svg"
            alt="Green check mark"
            class="icon-text"
          />
          You can download all completed forms
        </p>
        <table class="table table-striped table-documents" style="margin-bottom:42px;">
          <tbody>
            <tr v-for="doc in visiblePatientForms" :key="doc.name">
              <td>{{ doc.type.title }}</td>
              <td>
                <a
                  v-if="doc.filled_at"
                  :href="`/api/public/patients/document-requests/${doc.request_hash}/${doc.id}`"
                  target="_blank"
                  class="btn btn-download"
                  style="text-align:right;"
                >
                  <span>Download</span>
                  <i class="fa fa-download"></i>
                </a>
                <div v-else class="not-completed" title="Not completed">
                  <span>Not completed</span>
                  <i class="fa fa-download"></i>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        <template v-if="$route.name === 'download-forms' && visiblePatientForms.length > 0">
          <p class="patient-form__body-text">
            <img
              src="/images/icons/icon-check.svg"
              alt="Green check mark"
              class="icon-text"
            />
            or send all completed forms via secure email
          </p>
          <div class="row">
            <div class="col-md-4 col-md-offset-4">
              <div class="form-group" :class="{'has-error': errors.has('email')}">
                <label for="email" class="control-label">Email</label>
                <input type="text" id="email" v-model.trim="email" name="email" class="form-control input-lg" data-vv-validate-on="change" v-validate="'required|email|max:50'" data-vv-as="email">
                <span class="help-block" v-if="errors.has('email')">{{ errors.first('email') }}</span>
              </div>
            </div>
            <div class="col-md-4 col-md-offset-4">
              <div class="form-group" :class="{'has-error': errors.has('password')}">
                <label for="password" class="control-label">Password</label>
                <input type="text" id="password" v-model="password" name="password" class="form-control input-lg" data-vv-validate-on="change" v-validate="'required|min:4|max:50'">
                <span class="help-block" v-if="errors.has('password')">{{ errors.first('password') }}</span>
              </div>
            </div>
            <div class="col-md-4 col-md-offset-4 text-center">
              <div class="form-group has-success">
                <label class="control-label">
                  <template v-if="documents_sent">
                    Documents has been sent.
                  </template>
                </label>
              </div>
              <button class="btn btn-lg btn-success btn-download-all" @click="sendDocumentsToEmail" :disabled="sending_documents">Send to Email</button>
            </div>
          </div>
        </template>
      </template>
    </patient-form-layout>
</template>

<script>
export default {
  name: "DownloadDocuments",

  data() {
    return {
      sending_documents: false,
      email: '',
      password: '',
      get_access_password: '',
      invalid_get_access_password: false,
      show_download_page: false,
      documents_sent: false,
    };
  },

  computed: {
    patientForms() {
      return this.$store.state.patientForms;
    },

    visiblePatientForms() {
      const formsToExclude = ['supporting_documents', 'credit_card_on_file', 'agreement_for_service_and_hipaa_privacy_notice_and_patient_rights', 'attendance_policy'];
      return this.patientForms.filter((form) => !formsToExclude.includes(form.type.name));
    },

    patient() {
      return this.$store.state.currentPatient;
    },

    hasNotFilledForms() {
      return this.patientForms.some((form) => !form.filled_at);
    },
  },

  methods: {
    sendDocumentsToEmail() {
      this.documents_sent = false;
      this.$validator.validateAll().then((result) => {
        if(!result) {
          return;
        }
        this.sending_documents = true;
        this.$store.dispatch('safeSendPatientFormsToEmail', {
          patient_id: this.patient.id,
          hash: this.$route.params.hash,
          data: {
            email: this.email,
            password: this.password
          }
        }).then(() => {
          this.email = '';
          this.password = '';
          this.documents_sent = true;
        }).catch((error) => {
            if(error.response.status === 409) {
                this.$message({
                    type: 'error',
                    message: error.response.data.error,
                    duration: 10000,
                });
            }
        }).finally(() => {
          this.sending_documents = false;
        });
      });
    },
    getDocuments() {
      this.$validator.validateAll().then((result) => {
        if(!result) {
          return;
        }
        this.$refs.downloadLayoutPage.getSecuredPatientFormsData(this.get_access_password);
      });
    },
    showDownloadPage() {
      this.show_download_page = true;
    },
    setInvalidPassword(status) {
      this.invalid_get_access_password = status;
    },
  },
};
</script>

<style scoped lang="scss">
.not-completed {
  display: block;
  font-weight: 600;
  color: #a94442;
  margin-left: auto;
  text-align: right;
  padding: 6px 12px;

  span {
    display: none;
  }
}

@media (min-width: 768px) {
  .not-completed {
    i {
      display: none;
    }

    span {
      display: inline;
    }
  }
}

@media (max-width: 767px) {
  .not-completed {
    font-size: 25px;
    text-align: center;
  }
}
</style>
