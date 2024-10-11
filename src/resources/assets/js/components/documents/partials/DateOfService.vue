<template>
    <div class="form-group date-of-service-document" id="date-of-service-fastselect-container"
         :class="size"

    >
        <label class="control-label">{{label}}</label>

        <div class="fastselect-disabled" v-if="editingDisabled"></div>
        <input v-if="patient"
               type="text"
               multiple
               id="date-of-service-fastselect"
               class="tagsInput"
               data-user-option-allowed="true"
        />
    </div>
</template>

<script>
    export default {
        props: {
            editingDisabled: {
                type: Boolean,
                default: false
            },
            patient: {
                type: Boolean,
                default: true
            },
            label: {
                type: String,
                default: "Date of Service"
            },
            size: {
                type: String,
                default: "col-md-4"
            },
        },
    }
</script>