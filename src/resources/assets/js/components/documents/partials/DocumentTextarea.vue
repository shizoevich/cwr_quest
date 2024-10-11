<template>
    <div class="form-group input-container"
         :class="[size, {'has-error': errors.has(name) && (fields && fields[name] && fields[name].pristine), 'div-disabled': disabled}]"
    >
        <label :class="label_classes"
               :for="id"
               v-if="label"
               v-html="label"
        >
        </label>
        <textarea data-autosize="true"
                  class="form-control vertical-resize fix-row input-element"
                  :rows="rows"
                  :id="id"
                  :name="name"
                  :value="value"
                  :class="{'single-textarea': !label}"
                  @input="onInput($event)"
                  v-validate.disable="validateRules"
                  @change="parentChange"
                  :disabled="disabled"
                  :maxlength="maxlength"
        >
        </textarea>
    </div>
</template>

<script>
    export default {
        inheritAttrs: false,
        props: {
            name: String,
            labelClass: String,
            size: {
                type: String,
                default: "col-lg-12"
            },
            label: String,
            value: String,
            rows: {
                type: String,
                default: "1"
            },
            validateRules:{
                type: String,
                default: ""
            },
            disabled: Boolean,
            maxlength: Number
        },
        data() {
            return {
                id: this._uid
            }
        },
        mounted() {
            if (!this.fields) {
                this.fields = null;
            }
        },
        methods: {
            onInput($event) {
                this.$emit('input', $event.target.value);
            },
            parentChange(){
                this.$emit('change');
            }
        },

        computed: {
            label_classes() {
                let classes = 'control-label input-label ';
                if(this.labelClass) {
                    return classes += this.labelClass;
                }

                return classes;
            }
        },
    }
</script>