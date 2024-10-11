<template>
    <div>
        <el-dialog
                title="Help"
                :visible.sync="dialogVisible"
        class="help-video-modal bootstrap-modal">
            <div class="help-video-modal-body">
                <video ref="videoPlayer" class="video-js"></video>
            </div>
        </el-dialog>

    </div>
</template>

<script>
    import videojs from 'video.js';
    import 'video.js/dist/video-js.min.css';

    export default {
        name: 'HelpVideo',
        props: {
            showDialog: {
                type: Boolean,
                default: false,
            },
            videoOptions: {
                type: Object,
                default() {
                    return {};
                }
            }
        },
        data() {
            return {
                player: null,
            }
        },
        computed: {
            dialogVisible: {
                get() {
                    return this.showDialog
                },
                set(value) {
                    if (!value) {
                        this.$emit('closeDialog')
                    }
                }
            },
        },
        methods: {
          initialVideo() {
              this.$nextTick(() => {
                  this.player = videojs(this.$refs.videoPlayer, this.videoOptions, function onPlayerReady() {
                      console.log('onPlayerReady', this);
                  })
              })
          },
        },
        mounted() {
            this.initialVideo();
        },
        beforeDestroy() {
            if (this.player) {
                this.player.dispose()
            }
        }
    }
</script>

<style lang="scss">
    .help-video-modal {

        .el-dialog {
            width: 100%;
            max-width: 700px;
        }

        &-body {
            display: flex;
            justify-content: center;
        }

        .video-js {

            .vjs-big-play-button {
                height: 45px;
                width: 75px;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);

                .vjs-icon-placeholder {

                    &:before {
                        width: 30px;
                        height: 30px;
                        line-height: 1;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                    }
                }
            }
        }
    }
</style>