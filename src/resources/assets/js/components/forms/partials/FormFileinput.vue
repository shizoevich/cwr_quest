<template>
  <div class="form-fileinput">
    <div class="input-wrapper" :class="{ empty: !value }">
      <input
              type="file"
              :name="name"
              :id="id"
              :ref="id"
              class="input"
              @click="resetImageUploader"
              @change="handleChange"
              :accept="accept"
      />
      <label :for="id" class="label">
        <template v-if="!value">
          <img class="label-icon" src="/images/icons/icon-plus.svg" />
          <div class="btn btn-upload">
            Upload more files
          </div>
        </template>
        <template v-else>
          <div class="preview" ref="preview-image"></div>
          <a
                  role="button"
                  href="#"
                  class="btn btn-clear"
                  @click.prevent="handleClear"
          >
            <img class="" src="/images/icons/icon-close.svg" alt="close icon" />
          </a>
        </template>
      </label>
    </div>
  </div>
</template>

<script>
  export default {
    name: "FormFileinput",
    props: {
      name: {
        type: String,
        default: "",
      },
      id: {
        type: String,
        default: "fileinput",
      },
      value: {
        default: null,
      },
      accept: {
        type: String,
        default: ".png, .jpg, .jpeg",
      },
      documentType: {
        type: String,
        default: "",
      },
    },
    data: () => ({
      filename: null,
    }),
    watch: {
      value() {
        this.$nextTick(() => {
          if (!this.value) {
            this.filename = null;
          } else if (typeof this.value === "string") {
            this.filename = this.getFilename(this.value);
          } else {
            this.renderPreview();
          }
        });
      },
    },
    methods: {
      renderPreview() {
        let preview = this.$refs["preview-image"];
        this.filename = this.value.name;
        if (preview) {
          let reader = new FileReader();
          reader.onload = (e) => {
            preview.style.backgroundImage = `url(${e.target.result})`;
            this.$forceUpdate();
          };
          reader.readAsDataURL(this.value);
        }
      },
      getFilename(filepath) {
        let path = filepath.split("/");
        return path.pop();
      },
      resetImageUploader() {
        this.$refs[this.id].value = '';
      },
      handleChange(event) {
        if (event.target.files.length > 0) {
          const file = event.target.files[0];
          let pattern = /image-*/;
          if (!file.type.match(pattern)) {
            this.$notify.warning({
              title: 'Warning',
              customClass: 'notify-field-not-valid',
              message: this.documentType ? `The ${this.documentType} must be an image.` : 'File must be an image',
              offset: 100
            });
            return;
          }

          this.$emit("change", event.target.files[0]);
        }
      },
      handleClear() {
        this.$emit("clear");
      }
    },
    mounted() {
      if (typeof this.value === "string") {
        this.filename = this.value;
      } else if (this.value instanceof File) {
        this.filename = this.value.name;
        this.renderPreview();
      }
    },
  };
</script>

<style scoped lang="scss">
  .form-fileinput {
    width: 100%;
    height: 100%;
    cursor: pointer;

    .input {
      display: none;
    }

    &.form-fileinput-invalid .form-input-error-message {
      max-height: 200px;
    }

    .input-wrapper {
      width: 100%;
      height: 100%;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      background-color: #ffffff;
      padding: 32px 16px;
    }

    .label {
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #c0c0c0;
      font-size: 1.5rem;
      font-weight: normal;
      text-align: left;
      height: auto;
      padding-left: 8px;
      padding-right: 8px;
      margin: 0;
      cursor: pointer;

      .label-icon {
        margin-bottom: 40px;
        margin-top: 66px;
      }

      .filename {
        overflow: hidden;
      }
    }

    .btn {

      &-upload {
        background-image: none !important;
      }
    }

    .preview {
      width: 100%;
      height: 170px;
      background-size: contain;
      background-position: center;
      background-repeat: no-repeat;
    }

    @media (max-width: 767px) {
      .label-icon {
        display: none;
      }

      .label {
        height: 100%;
        padding: 0;
      }

      .preview {
        height: 100%;
        background-size: 100% 100%;
      }

      .input-wrapper {
        padding: 0;
        height: 145px;

        &.empty {
          display: flex;
          justify-content: center;
          align-items: center;
        }

        &:not(.empty) {
          border: none;
        }
      }
    }

    @media (max-width: 575px) {
      .input-wrapper {
        height: 85px;
      }
    }
  }
</style>
