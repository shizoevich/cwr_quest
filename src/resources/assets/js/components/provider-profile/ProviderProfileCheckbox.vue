<template>
    <div>
        <label>
            <input
                :key="key"
                type="checkbox"
                :checked="checked"
                :disabled="disabled"
                @click.stop.prevent="change"
            >
            {{ label }}
        </label>
        <div v-if="error" class="error-message">
            {{ error }}
        </div>
    </div>
</template>

<script>
export default {
    props: {
        label: {
            type: String
        },
        checked: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        error: {
            type: String,
            default: '',
        }
    },

    data() {
        return {
            key: 1,
        }
    },

    watch: {
        checked() {
            this.key++; // redraw checkbox
        }
    },

    methods: {
        change() {
            this.$emit('change', !this.checked);
        }
    }
}
</script>