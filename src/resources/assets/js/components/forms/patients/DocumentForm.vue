<template>
  <div class="" v-if="form_data.length > 0">
    <template v-for="(form, formIndex) in form_data">
      <div class="document-form" :key="form.type" :id="formatName(form.type)">
        <div class="document-form__header">
          <h3 class="document-form__header-title">
            {{ form.type }}
          </h3>
        </div>
        <div class="document-form__body">
          <div class="document-form__body-row">
            <template v-for="(file, fileIndex) in form.files">
              <div
                      class="document-form__body-col"
                      :key="`${formatName(form.type)}-file-${fileIndex}`"
              >
                <form-fileinput
                        :value="file.value"
                        :document-type="form.type"
                        :id="`${formatName(form.type)}-file-${fileIndex}`"
                        @change="
                    (f) => setFile(f, { form: formIndex, file: fileIndex })
                  "
                        @clear="removeItem({ form: formIndex, file: fileIndex })"
                />
              </div>
            </template>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
  import FormFileinput from "./../partials/FormFileinput";
  import UploadFileSize from '../../../mixins/upload-file-size.js';

  export default {
    name: "DocumentForm",
    components: { FormFileinput },
    props: {
      data: {
        type: Array,
        required: true,
      },
      docTypes: {
        type: Array,
        required: true,
      },
    },
    mixins: [UploadFileSize],
    data: () => ({
      form_data: [],
    }),
    computed: {
      patientForms() {
        return this.$store.state.patientForms;
      },
    },
    methods: {
      addField() {
        this.files.push({ value: null });
      },
      setFile(file, form) {
        if (!this.validateFileSize(file)) {
          return;
        }
        
        this.form_data[form.form].files[form.file].value = file;
        this.form_data[form.form].files.push({
          value: null,
        });
      },
      removeItem(form) {
        this.form_data[form.form].files.splice(form.file, 1);
      },
      formatName(name) {
        return name
                .replace(/[.,\/#!$%\^&\*;:{}=\-_`'~()]/g, "")
                .replace(/\s/g, "_");
      },
      clearErrors() {
        for (let documents of this.form_data) {
          $(`#${this.formatName(documents.type)}`).removeClass("has-error");
        }
      },
      validateForm() {
        this.clearErrors();
        let has_error = false;

        for (let documents of this.form_data) {
          if (documents.files.length < 2) {
            $(`#${this.formatName(documents.type)}`).addClass("has-error");
            has_error = true;
          }
        }

        if (has_error) {
          this.$emit(
                  "validation-fails",
                  "Please make sure you have attached all requested documents."
          );
        } else {
          this.$emit("validation-success");
        }
      },
    },
    watch: {
      form_data: {
        handler: function (val) {
          let documents = [];
          for (let document of this.form_data) {
            let doc = {};
            doc.type = document.type;
            doc.files = document.files
                    .filter((item) => item.value !== null)
                    .map((item) => item.value);
            documents.push(doc);
          }
          this.$emit("documents-change", documents);
        },
        deep: true,
      },
    },
    mounted() {
      if (this.data.length === 0) {
        for (let docType of this.docTypes) {
          this.form_data.push({
            type: docType,
            files: [{ value: null }],
          });
        }
      } else {
        for (let docType of this.data) {
          let files = [];
          for (let file of docType.files) {
            files.push({ value: file });
          }
          files.push({ value: null });
          this.form_data.push({
            type: docType.type,
            files: files,
          });
        }
      }
    },
  };
</script>

<style scoped></style>
