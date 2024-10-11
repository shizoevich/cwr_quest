<template>
    <div class="timeline-body">
        <div>
            <label>Comment</label>
            <div v-html="comment.comment" v-if="comment.comment">
                {{ comment.comment }}
            </div>
            <div>
                <label
                    >Appointment survey for provider
                    {{ comment.provider_name_from_appointment }}</label
                >
                <div class="d-flex flex-column gap-2">
                    <div>
                        <div>
                            On a scale of 1-5 (with 5 being the highest) how do
                            you rate the services you just received?
                        </div>
                        <div class="d-flex gap-1">
                            <span v-for="index in 5" :key="index">
                                <img
                                    :src="getStarIcon(index, 'service_rate')"
                                    class="star" />
                            </span>
                        </div>
                    </div>
                    <div>
                        <div>
                            On a scale of 1-5 (with 5 being the highest) how do
                            you rate the provider that was assigned to you?
                        </div>
                        <div class="d-flex gap-1">
                            <span v-for="index in 5" :key="index">
                                <img
                                    :src="getStarIcon(index, 'provider_rate')"
                                    class="star" />
                            </span>
                        </div>
                    </div>
                    <div>
                        <div>
                            Do you have any suggestions as to how we can elevate
                            your experience with Change WithinReach?
                        </div>
                        <div className="d-flex gap-1 mt-1">
                            {{ comment.comment_metadata.suggestions || "-" }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "initial-survey-complete-comment",

        props: {
            comment: {
                type: Object,
                required: true,
            },
            getStarIcon: {
                type: Function,
                required: true,
            },
        },
    };
</script>

<style scoped>
    .star {
        height: 18px;
    }
</style>
