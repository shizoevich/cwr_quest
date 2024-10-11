<template>
    <section class="patient-tags">
        <div
            v-for="tag in tags"
            :key="tag.id"
            class="badge patient-tag"
            :style="{
                backgroundColor: tag.hex_background_color,
                color: tag.hex_text_color,
            }"
        >
            {{ tag.tag }}
        </div>
    </section>
</template>

<script>
    export default {
        props: {
            tags: {
                type: Array,
                required: true,
            }
        },
    }
</script>

<style scoped>
.patient-tags {
    display: inline-flex;
    flex-flow: row wrap;
    gap: 6px;
}

.patient-tag {
    padding: 6px 10px;
    font-size: 16px;
    border-radius: 20px;
    margin: 4px 0;
}
</style>