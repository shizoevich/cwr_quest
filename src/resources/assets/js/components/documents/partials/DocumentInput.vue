<template>
    <div class="form-group input-container"
         :class="[size, {'has-error': errors.has(name) && (fields && fields[name] && fields[name].pristine), 'div-disabled': disabled}]"
    >
        <label :class="label_classes"
               :for="id"
               v-if="label"
               v-html="label"
        ></label>
        <input type="text"
               class="form-control fix-row input-element"
               :id="id"
               :name="name"
               :value="value"
               @input="onInput($event)"
               v-validate.disable="validateRules"
               @change="parentChange"
               :disabled="disabled"
               :maxlength="maxlength"
        >
    </div>
</template>

<script>
    export default {
        inheritAttrs: false,
        props: {
            name: String,
            size: {
                type: String,
                default: "col-lg-12"
            },
            label: String,
            labelClass: String,
            value: String,
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