<template>
    <div class="form-tag">
       <span class="form-tag__text">
           <slot></slot>
       </span>
        <button type="button" class="form-tag__remove" @click.prevent="remove">
            <img src="/images/icons/icon-xmark.svg" alt="x-mark icon">
        </button>
    </div>
</template>

<script>
    export default {
        name: "Tag",
        methods: {
            remove() {
                this.$emit('remove')
            }
        },
    }
</script>

<style scoped>

</style>