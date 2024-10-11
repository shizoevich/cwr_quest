<template>
    <div class="comments-block">
        <div class="comments" ref="comments">
            <div
                v-if="commentsAreLoading"
                class="w-100 d-flex justify-content-center"
                style="padding: 10px">
                <pageloader add-classes="save-loader" v-show="true" />
            </div>
            <template v-if="comments.length > 0">
                <div
                    v-for="(comment, index) in comments"
                    :key="comment.id"
                    :id="`comment-${comment.id}`">
                    <div
                        class="days-date"
                        v-if="
                            index === 0 ||
                            comments[index - 1].created_at.split(' ')[0] !==
                                comment.created_at.split(' ')[0]
                        ">
                        <hr />
                        <div>{{ formatDate(comment.created_at) }}</div>
                    </div>

                    <provider-comment
                        :ownComment="currentUserId === comment.admin_id"
                        :isScrolledComment="scrolledCommentId === comment.id"
                        :comment="comment" />
                </div>
            </template>
            <template v-else>
                <div class="no-comments-block">
                    <span v-if="pageLoaded">No comments yet</span>
                    <span v-else>Wait for the page to load...</span>
                </div>
            </template>
            <div class="scroll-to-bottom-button-container">
                <button
                    v-show="showScrollToBottomButton"
                    @click="scrollCommentsToBottom"
                    class="scroll-to-bottom-button text-primary">
                    <i class="fa fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <button
                class="btn btn-primary paperclip-button"
                :disabled="isSubmiting">
                <label for="file-input">
                    <i class="fa fa-paperclip"></i>
                </label>
            </button>
            <input
                id="file-input"
                type="file"
                style="display: none"
                @change="handleFileUpload" />
            <div class="w-100 position-relative">
                <div
                    class="attached-file badge bg-blue d-flex gap-3"
                    v-if="newComment.file">
                    <div class="file-info">
                        <i class="fa fa-file"></i>
                        {{ newComment.file.name }}
                    </div>
                    <i
                        @click="clearAttachedFile"
                        class="delete-file-button fa fa-times"></i>
                </div>
                <textarea
                    id="comment"
                    class="form-control no-resize custom-textarea"
                    :class="{ 'file-is-attached': newComment.file }"
                    v-model="newComment.comment"
                    :disabled="isSubmiting" />
            </div>
            <button
                class="btn btn-primary submit-button"
                @click.prevent="addComment"
                :disabled="submitButtonIsDisabled">
                <span v-if="!isSubmiting">Submit</span>
                <pageloader v-else add-classes="save-loader" v-show="true" />
            </button>
        </div>
    </div>
</template>

<script>
    import ProviderComment from "./ProviderComment.vue";
    import UploadFileSize from '../../mixins/upload-file-size.js';

    export default {
        components: {
            ProviderComment,
        },

        mixins: [UploadFileSize],

        props: {
            providerComments: {
                type: Object,
                required: true,
            },
            currentUserId: {
                type: Number,
                required: true,
            },
            providerId: {
                type: Number,
                required: true,
            },
        },

        computed: {
            submitButtonIsDisabled() {
                return (
                    (!this.newComment.comment && !this.newComment.file) ||
                    this.isSubmiting
                );
            },

            scrolledCommentId() {
                const urlParams = new URLSearchParams(window.location.search);
                return Number(urlParams.get("comment_id"));
            },
        },

        data() {
            return {
                newComment: {
                    comment: null,
                    file: null,
                },
                comments: [],
                currentPage: 1,
                lastPage: 1,
                copiedLink: null,
                isSubmiting: false,
                showScrollToBottomButton: false,
                commentsAreLoading: false,
                pageLoaded: false,
                scrolledComment: null,
            };
        },

        methods: {
            loadMoreComments() {
                const self = this;
                const scrollHeight = this.$refs.comments.scrollHeight;

                this.commentsAreLoading = true;

                const payload = {
                    providerId: this.providerId,
                    params: {
                        page: this.currentPage + 1,
                    },
                };

                this.$store
                    .dispatch("getProviderComments", payload)
                    .then(({ data }) => {
                        data.data.forEach((comment) =>
                            this.comments.unshift(comment),
                        );
                        self.currentPage = data.current_page;

                        setTimeout(() => {
                            self.$refs.comments.scrollTop =
                                self.$refs.comments.scrollHeight -
                                scrollHeight -
                                66;
                        }, 0);
                    })
                    .finally(() => {
                        self.commentsAreLoading = false;
                    });
            },

            addComment() {
                this.isSubmiting = true;

                const payload = new FormData();
                payload.append("provider_id", this.providerId);

                Object.keys(this.newComment).forEach((key) => {
                    if (!this.newComment[key]) {
                        return;
                    }

                    payload.append(key, this.newComment[key]);
                });

                this.$store
                    .dispatch("createProviderComment", payload)
                    .then(({ data }) => {
                        this.comments.push(data);

                        this.clearNewComment();
                        this.$nextTick(() => {
                            this.scrollCommentsToBottom();
                        });
                    })
                    .catch((e) => {
                        const status = e.response.status;
                        if (status === 422) {
                            this.handleErrorMessage(e.response.data.errors);
                        } else {
                            this.$message({
                                type: "error",
                                message: "Oops, something went wrong!",
                                duration: 10000,
                            });
                        }
                    })
                    .finally(() => {
                        this.isSubmiting = false;
                    });
            },

            handleFileUpload(e) {
                const file = e.target.files[0];

                if (!this.validateFileSize(file)) {
                    e.target.value = null;
                    return;
                }

                this.newComment.file = file;
            },

            clearAttachedFile() {
                this.newComment.file = null;

                const fileInput = document.querySelector("#file-input");

                if (fileInput) {
                    fileInput.value = "";
                }
            },

            clearNewComment() {
                this.newComment.comment = null;
                this.clearAttachedFile();
            },

            scrollToComment(commentId) {
                const commentsBlock = this.$refs.comments;
                const commentElement = document.getElementById(
                    `comment-${commentId}`,
                );

                if (commentElement) {
                    const commentsBlockRect =
                        commentsBlock.getBoundingClientRect();

                    commentsBlock.scrollTo({
                        top: commentElement.offsetTop - commentsBlockRect.top,
                        behavior: "smooth",
                    });

                    this.scrolledComment = null;
                } else {
                    commentsBlock.scrollTo({
                        top: 0,
                        behavior: "smooth",
                    });
                }
            },

            scrollCommentsToBottom() {
                const commentsBlock = this.$refs.comments;
                commentsBlock.scrollTo({
                    top: commentsBlock.scrollHeight,
                    behavior: "smooth",
                });
            },

            handleScroll() {
                const commentsBlock = this.$refs.comments;
                const scrollTop = commentsBlock.scrollTop;

                this.showScrollToBottomButton =
                    scrollTop + 100 <
                    commentsBlock.scrollHeight - commentsBlock.clientHeight;

                if (scrollTop === 0 && this.currentPage < this.lastPage) {
                    this.loadMoreComments();
                }
            },

            formatDate(date) {
                return moment(date).format("dddd, DD MMM. YYYY");
            },

            handleActiveTab() {
                const commentsBlock = this.$refs.comments;

                this.$nextTick(() => {
                    commentsBlock.scrollTop = commentsBlock.scrollHeight;
                });

                commentsBlock.addEventListener("scroll", this.handleScroll);

                this.$once("hook:beforeDestroy", function () {
                    commentsBlock.removeEventListener(
                        "scroll",
                        this.handleScroll,
                    );
                });
            },

            initComments() {
                this.comments = _.cloneDeep(
                    this.providerComments.data.reverse(),
                );
                this.currentPage = this.providerComments.current_page;
                this.lastPage = this.providerComments.last_page;
            },

            handleErrorMessage(errors) {
                for (const errorsName in errors) {
                    if (errors.hasOwnProperty(errorsName)) {
                        errors[errorsName].forEach((error) => {
                            setTimeout(() => {
                                this.$message({
                                    type: "error",
                                    message: error,
                                    duration: 10000,
                                });
                            }, 300);
                        });
                    }
                }
            },

            setupCommentTabClickListener() {
                const commentTabClickListener = () => {
                    setTimeout(() => this.handleActiveTab(), 100);
                };

                const commentTab = document.querySelector("#tab-comments");

                commentTab.addEventListener("click", commentTabClickListener);

                this.$once("hook:beforeDestroy", function () {
                    commentTab.removeEventListener(
                        "click",
                        commentTabClickListener,
                    );
                });
            },
        },

        watch: {
            comments() {
                if (this.scrolledComment) {
                    setTimeout(() => {
                        this.scrollToComment(this.scrolledComment);
                    }, 100);
                }
            },
        },

        mounted() {
            this.scrolledComment = this.scrolledCommentId;

            this.setupCommentTabClickListener();

            window.onload = () => {
                this.initComments();
                this.handleActiveTab();

                this.pageLoaded = true;
            };
        },
    };
</script>

<style scoped lang="scss">
    .comments-block {
        height: 76vh;
        border: 1px solid #ddd;
        background: white;
        border-radius: 4px;
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        justify-content: end;
        z-index: 1000;

        .comments {
            position: relative;
            display: flex;
            height: 100%;
            flex-direction: column;
            overflow: auto;
            scrollbar-width: none;

            .days-date {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                font-weight: bold;
                background: #dcdcdc;
                border-top: 1.5px dashed #ddd;

                hr {
                    position: absolute;
                    width: 100%;
                    margin: 0;
                    z-index: -1;
                }

                div {
                    padding: 20px 10px;
                }
            }

            &::-webkit-scrollbar {
                display: none;
            }
        }

        .scroll-to-bottom-button-container {
            position: sticky;
            bottom: 10px;
            display: flex;
            justify-content: end;
            padding-right: 10px;
            width: 100%;

            .scroll-to-bottom-button {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 30px;
                width: 30px;
                border-radius: 100px;
                border: none;
                box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            }
        }

        .no-comments-block {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            height: 100%;
        }

        .custom-textarea {
            height: 80px;
            border-radius: 0;
        }

        .custom-textarea.file-is-attached {
            padding-top: 25px;
        }

        .paperclip-button {
            height: 80px;
            border-radius: 4px 0 0 4px;
            font-size: 25px;
            padding: 0;

            label {
                display: flex;
                justify-items: center;
                align-items: center;
                cursor: pointer;
                height: 100%;
                width: 100%;
                margin: 0;
                padding: 8px;
            }
        }

        .submit-button {
            height: 80px;
            width: 73px;
            border-radius: 0 4px 4px 0;
        }

        .save-loader {
            width: 36px;
            height: 36px;
        }

        .attached-file {
            position: absolute;
            top: 5px;
            margin: 0 5px;
            max-width: calc(100% - 10px);
            padding-right: 10px;

            .file-info {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .delete-file-button {
                cursor: pointer;

                &:hover {
                    color: #ddd;
                }
            }
        }
    }
</style>
