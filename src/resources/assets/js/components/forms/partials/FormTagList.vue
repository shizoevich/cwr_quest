<template>
    <ul class="form-tag-list">
        <li
            v-for="(tag, index) in tags"
            class="form-tag-list__item"
            :key="`tag-${tag.text.replace(' ', '')}-${index}`"
        >
            <form-tag @remove="remove(tag.id)" :title="tag.text">{{ tag.text }}</form-tag>
        </li>
    </ul>
</template>

<script>
    import FormTag from "./FormTag";
    export default {
        name: "FormTagList",
        props: {
            tags: {
                type: Array,
                default: [],
            }
        },
        components: {
            FormTag,
        },
        methods: {
            remove(id) {
                this.$emit('remove', id);
            }
        }
    }
</script>

<style scoped>

</style>