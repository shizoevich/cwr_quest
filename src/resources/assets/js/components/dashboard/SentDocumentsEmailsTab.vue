<template>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="inline-block" style="margin-right:15px;">
                        <label>Therapist Name</label>
                        <select class="form-control provider-select" v-model="selected_provider">
                            <option value="-1">All</option>
                            <option value="-7777">CWR Admin</option>
                            <option v-for="doctor in doctors" :value="doctor.id">{{doctor.provider_name}}</option>
                        </select>
                    </div>

                    <div class="inline-block">
                        <label>Status</label>
                        <select class="form-control provider-select" v-model="selected_sent_status">
                            <option value="-1">All</option>
                            <option v-for="status in sentStatuses" :value="status.id" class="doc-status">{{status.status}}</option>
                        </select>
                    </div>
                    <div class="table-responsive" v-if="!loading.emails">
                        <table class="table table-striped table-condensed" id="email-statistic-table">
                            <thead>
                            <tr>
                                <th>Therapist Name</th>
                                <th>Patient Name</th>
                                <th>Recipient</th>
                                <th>Document Name</th>
                                <th>Status</th>
                                <th># of Downloads</th>
                                <th>Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="item in statisticList">
                                <td>{{item.creator_name}}</td>
                                <td class="doc-name" :title="item.patient_name">
                                    <a :href="`${'/chart/' + item.patient_id}`">{{item.patient_name}}</a>
                                </td>
                                <td class="doc-name" :title="item.recipient">{{item.recipient}}</td>
                                <td class="doc-name" :title="item.document_name">{{item.document_name}}</td>
                                <td class="doc-status text-center">
                                    <span v-if="item.document_shared_log && item.document_shared_log.shared_status">
                                        {{item.document_shared_log.shared_status.status}}
                                    </span>
                                </td>
                                <td class="text-center">{{item.download_count}}</td>
                                <td style="white-space:nowrap;">
                                    <span v-show="false">
                                        {{item.sent_date_timestamp}}
                                    </span>
                                    {{item.sent_date}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>


<script>
    export default {
        props: [
            'statistic',
            'doctors',
            'sentStatuses',
        ],

        data() {
            return {
                selected_provider: -1,
                selected_sent_status: -1,
                ajax_statistic: null,
                table: null,
                loading: {
                    emails: false,
                },
            };
        },

        methods: {
            initTable() {
                console.log('init_table');
                window.setTimeout(() => {
                    if(this.table) {
                        this.table.destroy();
                    }
                    this.table = $('#email-statistic-table').DataTable({
                        'paging': true,
                        'pageLength': 15,
                        'lengthChange': false,
                        'searching': true,
                        'ordering': true,
                        'info': false,
                        'autoWidth': false,
                        order: [[6, 'desc']],
                        columns: [
                            { searchable: false },
                            null,
                            null,
                            null,
                            { searchable: false },
                            { searchable: false },
                            { searchable: false },
                        ]
                    });
                }, 500);
            },

            updateStatistic() {
                this.loading.emails = true;
                let payload = "?providerId="+this.selected_provider+"&sentStatusId="+this.selected_sent_status+'&m=email';
                this.$store.dispatch("getSentEmailsStatistic", payload).then(response => {
                    if(response.status === 200) {
                        this.ajax_statistic = response.data;
                    }
                    this.loading.emails = false;
                });
            }
        },

        created() {
           this.initTable();
        },

        computed: {
            statisticList() {
                return this.ajax_statistic !== null ? this.ajax_statistic : this.statistic;
            }
        },

        watch: {
            selected_provider() {
                this.updateStatistic();
            },

            selected_sent_status() {
                this.updateStatistic();
            },
            'loading.emails': function() {
                if(!this.loading.emails) {
                    this.initTable();
                }
            },
        },
    }
</script>

<style scoped>
    .doc-status {
        text-transform: capitalize;
    }

    .doc-name {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        max-width: 250px;
    }
</style>
