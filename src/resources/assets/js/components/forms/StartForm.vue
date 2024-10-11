<template>
  <patient-form-layout @loaded="handleLoadedData">
    <template v-slot:content>
      <h2 class="patient-form__body-title">
        Welcome to Change Within Reach!
      </h2>
      <div class="patient-form__body-text">
        Thank you for scheduling an appointment at Change Within Reach, Inc. To expedite the check-in process, please take a few minutes to fill out our intake forms online. Just click the button below and follow simple instructions in our secure web portal.
      </div>
      <requested-documents
        title="Requested Documents"
        :patient-forms="patientForms"
      />
      <div class="buttons-container patient-form__buttons">
        <router-link
          :to="`/f/${$router.currentRoute.params.hash}/forms`"
          class="btn btn-lg btn-success btn-start btn-start-form"
          >Start
        </router-link>
      </div>
    </template>
  </patient-form-layout>
</template>

<script>
import RequestedDocuments from "./../documents/partials/RequestedDocuments";

const DOCUMENT_TYPES = {
  insurance: "Insurance",
  license: "Driver License",
};

export default {
  name: "StartForm",
  components: {
    RequestedDocuments,
  },
  data: () => ({
    patientForms: [],
  }),
  methods: {
    handleLoadedData(data) {
      this.patientForms = data.forms.filter((form) => !form.filled_at);
      if (this.patientForms.length === 0) {
        this.$router.push(`/f/${this.$route.params.hash}/download`);
      }
    }
  }
};
</script>

<style scoped></style>
