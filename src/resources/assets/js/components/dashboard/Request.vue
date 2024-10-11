<template>
    <div class="vue-wrapper">
        <div class="container">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>Method</label>
                                <select v-model="method" class="form-control">
                                    <option value="get">get</option>
                                    <option value="post">post</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label>URL</label>
                                <input type="text" class="form-control" v-model="url">
                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Payload (JSON)</label>
                        <textarea cols="30" rows="10" class="form-control" v-model="payload"></textarea>
                    </div>

                    <div class="form-group text-right">
                        <button class="btn btn-primary" @click.prevent="sendRequest()" :disabled="submitting">Submit</button>
                    </div>

                    <div class="form-group">
                        <label>Response</label>
                        <div class="response-body" v-html="response"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                method: 'get',
                url: '',
                payload: '',
                response: '',
                submitting: false,
            };
        },

        methods: {
            sendRequest() {
                if(!this.submitting) {
                    this.submitting = true;
                    axios({
                        method: this.method,
                        url: this.url,
                        data: JSON.parse(this.payload)
                    }).then(response => {
                        this.response = "Status Code: " + response.status + "<br>" + JSON.stringify(response.data);
                        this.submitting = false;
                    }).catch(error => {
                        this.response = error;
                        this.submitting = false;
                    });
                }
            }
        },
    }
</script>

<style scoped>
    .response-body {
        width: 100%;
        height: 250px;
        border: 1px solid #a3aebc;
        border-radius: 4px;
        overflow: auto;
        background: #f3f3f3;
    }
</style>