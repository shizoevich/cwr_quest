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
                            How well do you feel understood and supported by
                            your therapist? Please rate from 1 to 5 stars, where
                            1 star means 'not at all' and 5 stars means
                            'completely'.
                        </div>
                        <div class="d-flex gap-1">
                            <span v-for="index in 5" :key="index">
                                <img
                                    :src="
                                        getStarIcon(
                                            index,
                                            'therapist_understanding_support_rate',
                                        )
                                    "
                                    class="star" />
                            </span>
                        </div>
                    </div>
                    <div>
                        <div>
                            How would you rate the overall atmosphere or vibe
                            during your therapy sessions? Please rate from 1 to
                            5 stars, where 1 star means 'very poor' and 5 stars
                            means 'excellent'.
                        </div>
                        <div class="d-flex gap-1">
                            <span v-for="index in 5" :key="index">
                                <img
                                    :src="
                                        getStarIcon(
                                            index,
                                            'therapy_atmosphere_rate',
                                        )
                                    "
                                    class="star" />
                            </span>
                        </div>
                    </div>
                    <div>
                        <div>
                            How easy has it been for you to open up and share
                            with your therapist? Please rate from 1 to 5 stars,
                            where 1 star means 'very difficult' and 5 stars
                            means 'very easy'.
                        </div>
                        <div class="d-flex gap-1">
                            <span v-for="index in 5" :key="index">
                                <img
                                    :src="
                                        getStarIcon(
                                            index,
                                            'therapist_openness_share_rate',
                                        )
                                    "
                                    class="star" />
                            </span>
                        </div>
                    </div>
                    <div>
                        <div>
                            How do you feel after your therapy sessions? Please
                            rate your sense of being heard and validated from 1
                            to 5 stars, where 1 star means 'not at all' and 5
                            stars means 'completely'.
                        </div>
                        <div class="d-flex gap-1">
                            <span v-for="index in 5" :key="index">
                                <img
                                    :src="
                                        getStarIcon(
                                            index,
                                            'therapy_session_after_feelings_rate',
                                        )
                                    "
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
        name: "second-survey-complete-comment",

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
