<template>
    <div class="form-group form-group-select input-container"
         :class="[size, {'has-error': errors.has(name) && (fields[name] && fields[name].pristine), 'div-disabled': disabled, 'form-group-single-select': !label}]"
    >
        <label class="control-label input-label"
               :for="id"
               v-if="label"
               v-html="label"
        >
        </label>
        <select class="dropdown-form-control component-select"
                :class="{'single-select': !label}"
                :id="id"
                :name="name"
                :value="value"
                @change="parentChange"
                :disabled="disabled"
                @input="onInput($event)"
        >
            <option v-for="option in options" :value="option.value">{{option.label}}</option>
        </select>
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
            value: String,
            validateRules:{
                type: String,
                default: ""
            },
            disabled: Boolean,
            options: [Array, Object],
        },
        data() {
            return {
                id: this._uid
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
    }
</script>