<template>
    <div class="comment" :class="{ 'own-comment': ownComment, scrolled: isScrolledComment }">
        <div class="comment-link text-primary">
            <div v-if="copiedLink !== getCommentLink(comment.id)" class="link-icon">
                <el-tooltip content="Copy link to comment" effect="dark" placement="top">
                    <i v-if="copiedLink !== getCommentLink(comment.id)" @click="copyLink(comment.id)"
                        class="fa fa-link"></i>
                </el-tooltip>
            </div>
            <el-tooltip v-else content="Copied!" effect="dark" placement="top">
                <div class="copying-successful-icon">
                    <i class="fa fa-check"></i>
                </div>
            </el-tooltip>
        </div>
        <div class="comment-header">
            <div class="name">
                {{ comment.admin.firstname }} {{ comment.admin.lastname }}
            </div>
            <div class="time">{{ getFormattedTime(comment.created_at) }}</div>
        </div>
        <div class="comment-body">
            <div v-if="comment.aws_file_name" class="file">
                <div>
                    <i class="fa fa-file text-primary"></i>
                    <span>{{ comment.original_file_name }}</span>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a v-if="isFileHasPreview(comment.aws_file_name)"
                        @click.prevent="previewFile(comment.aws_file_name)" href=""
                        class="cursor-pointer"><small>Preview</small></a>
                    <a href="" @click.prevent="downloadFile(comment.aws_file_name)"
                        class="cursor-pointer"><small>Download</small></a>
                </div>
            </div>
            <span v-if="comment.comment">{{ comment.comment }}</span>
        </div>
    </div>
</template>

<script>
import FileInfo from '../../mixins/file-info';

    export default {
        props: {
            ownComment: {
                type: Boolean,
                required: true,
            },

            isScrolledComment: {
                type: Boolean,
                required: true,
            },

            comment: {
                type: Object,
                required: true,
            },
        },

        data() {
            return {
                copiedLink: null,
            };
        },

        mixins: [FileInfo],

        methods: {
            getFormattedTime(datetime) {
                return moment(datetime, "YYYY-MM-DD HH:mm:ss").format(
                    "hh:mm A",
                );
            },

            previewFile(fileName) {
                window.open("/provider/preview-file/" + fileName, "_blank");
            },

            downloadFile(docName) {
                window.open("/provider/download-file/" + docName, "_blank");
            },

            copyLink(commentId) {
                const commentLink = this.getCommentLink(commentId);

                navigator.clipboard.writeText(commentLink).then(() => {
                    this.copiedLink = commentLink;
                    const self = this;

                    setTimeout(() => {
                        self.copiedLink = null;
                    }, 3000);
                });
            },

            getCommentLink(commentId) {
                const { origin, pathname } = window.location;

                return `${origin}${pathname}?tab=tab_comments&comment_id=${commentId}`;
            },
        },
    };
</script>

<style scoped lang="scss">
    .comment {
        position: relative;
        padding: 0 15px 15px 15px;
        border-top: 1.5px dashed #ddd;

        .comment-link {
            position: absolute;
            right: 0;
            top: 2px;
            display: flex;
            justify-content: end;
            height: 18px;
            width: 18px;
            opacity: 0;
            transition: 0.3s ease;

            .link-icon {
                border-radius: 100px;
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
                cursor: pointer;
            }

            .copying-successful-icon {
                border-radius: 100px;
                background: green;
                flex-shrink: 0;
                width: 13px;
                height: 13px;
                margin-top: 2px;
                margin-right: 4px;
                display: flex;
                justify-content: center;
                align-items: center;
                color: white;
                font-size: 10px;
                cursor: pointer;
                font-size: 9px;
            }
        }

        .comment-header {
            margin-top: 15px;
            display: flex;
            flex-direction: column;

            .name {
                font-weight: bold;
            }

            .time {
                font-size: 12px;
            }
        }

        .comment-body {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;

            .file {
                display: flex;
                flex-direction: column;
                white-space: nowrap;
                overflow: hidden;
                max-width: 100%;
                background: rgb(240, 240, 240);
                width: fit-content;
                padding: 10px;
                border-radius: 4px;

                div {
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
            }
        }

        &:hover {
            background-color: rgb(250, 250, 250);

            .comment-link {
                opacity: 1;
            }
        }
    }

    .comment.own-comment {
        .comment-header {
            align-items: end;
        }

        .comment-body {
            align-items: end;
            text-align: right;
        }
    }

    .comment.scrolled {
        background: #0000ff0d;

        &:hover {
            background: #0000ff0d !important;
        }
    }
</style>
