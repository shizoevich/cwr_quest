<template>
    <div class="timeline-body" v-html="comment.comment" v-if="comment.comment">
        {{ comment.comment }}
    </div>
</template>

<script>
    export default {
        name: "default-comment",

        props: {
            comment: {
                type: Object,
                required: true,
            },
        },
    };
</script>
