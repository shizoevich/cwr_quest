<template>
    <div class="form-group">
        <h4>Call Logs</h4>
        <table class="table table-striped table-call-log m-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Called by</th>
                    <th class="col-xs-6">Comment</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!logs">
                    <td colspan="4" class="text-center">
                        <el-spinner class="custom-spinner"></el-spinner>
                    </td>
                </tr>

                <template v-else-if="logs.length > 0">
                    <tr v-for="(log, index) in logs" :key="index">
                        <td>{{ getFormattedDateTime(log.created_at) }}</td>
                        <td>{{ log.call_status_title }}</td>
                        <td>{{ getCalledBy(log) }}</td>
                        <td>{{ log.comment }}</td>
                    </tr>
                </template>

                <tr v-else>
                    <td colspan="4">
                        <h5 class="text-center">
                            No logs
                        </h5>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import DatetimeFormated from '../mixins/datetime-formated';

export default {
    mixins: [
        DatetimeFormated,
    ],

    props: {
        logs: {
            type: Array | null,
            required: true,
        }
    },

    methods: {
        getCalledBy(log) {
            const { provider, meta } = log.user;

            if (provider) {
                return provider.provider_name;
            }

            return `${meta.firstname} ${meta.lastname}`;
        }
    }
}
</script>

<style lang="scss">
.custom-spinner {
    svg {
        width: 40px !important;
        height: 40px !important;

        circle {
            stroke: rgb(64, 158, 255) !important;
        }
    }
}
</style>