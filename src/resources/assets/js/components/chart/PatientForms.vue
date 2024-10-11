<template>
  <patient-forms-collapse
    :patient="patient"
    :documents-sent="documentsSent"
    @selected-forms="(value) => selectedForms = value"
  />
</template>

<script>
    export default {
        name: "patient-forms-tab",
        props: {
            patient: {
                validator: prop => typeof prop === 'object' || prop === null,
                required: true,
            }
        },

        data() {
            return {
                documentsSent: false,
                selectedForms: []
            };
        },

        methods: {
            sendDocuments() {
                this.documentsSent = true;
            }
        }
    }
</script>

<style scoped>
</style>