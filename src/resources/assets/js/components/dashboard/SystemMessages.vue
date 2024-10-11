<template>
    <div class="modal modal-vertical-center fade" id="system-message" data-backdrop="static" data-keyboard="false" v-if="messages" style="z-index:5001">
        <div class="modal-dialog" :class="modal_class">
            <div class="modal-content">
                <div class="modal-header" v-if="messages[current_message_index] && messages[current_message_index].title">
                    <button type="button" class="close" @click.prevent="closeMessagesDialog(false)" title="Close">&times;</button>
                    <h4 class="modal-title" v-html="messages[current_message_index].title"></h4>
                </div>
                <div class="modal-body">
                    <div class="row" v-if="messages[current_message_index]">
                        <div class="col-xs-12" v-html="messages[current_message_index].text"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <label class="pull-left">
                        <input type="checkbox" v-model="dont_show_later">
                        Do not show again
                    </label>
                    <button class="btn btn-default" @click.prevent="nextMessage()" v-if="this.current_message_index < this.messages.length - 1">Next</button>
                    <button class="btn btn-default" @click.prevent="closeMessagesDialog(true)" v-else>Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            page: String
        },
        data() {
            return {
                messages: [],
                current_message_index: 0,
                dont_show_later: false,
            };
        },

        mounted() {
            this.$store.dispatch('getSystemMessages', this.page).then(response => {
                if(response.status === 200) {
                    this.messages = response.data;
                    if(this.messages && this.messages.length > 0) {
                        $(document).ready(function() {
                            $('#system-message').modal('show');
                        });
                    }
                }
            });
        },

        computed: {
            modal_class() {
                if(this.messages && this.messages[this.current_message_index] && this.messages[this.current_message_index].modal_class) {
                    return this.messages[this.current_message_index].modal_class;
                }

                return "";
            }
        },

        watch: {
        },

        methods: {

            setReaded() {
                if(this.dont_show_later) {
                    axios({
                        method: 'post',
                        url: '/system-messages/set-readed',
                        data: {
                            messageId: this.messages[this.current_message_index].id
                        }
                    });
                }
                this.dont_show_later = false;
            },

            nextMessage() {
                this.setReaded();
                this.current_message_index++;
            },

            closeMessagesDialog(setReaded) {
                if(setReaded) {
                    this.setReaded();
                }
                
                $('#system-message').modal('hide');
                this.messages = [];
                this.current_message_index = 0;
            }
        }
    }
</script>