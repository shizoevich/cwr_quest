<template>
    <el-dialog
        title="Create Visits"
        :visible.sync="showDialog"
        :close-on-click-modal="false"
        v-loading.fullscreen.lock="isLoading"
        class="salary-dialog bootstrap-modal">
        <div class="salary-dialog-body" style="word-break:break-word;">
            <h4 style="margin-top:0;">Are you sure you want to create visit(s) for <b>{{selectedAppointmentIds.length || 0}}</b> appointment(s)?</h4>
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>DOS</th>
                    <th>Patient Name</th>
                    <th>Therapist Name</th>
                    <th>Supervisor Name</th>
                    <th>Insurance</th>
                    <th>Reason for Visit</th>
                </tr>
                </thead>
                <tbody>
                <template v-for="(appointment, index) in appointments">
                    <tr>
                        <td>{{ index + 1}}</td>
                        <td>{{ $moment(appointment.date_of_service).format('MM/DD/YYYY hh:mm A') }}</td>
                        <td>
                            <a :href="`${'/chart/'+appointment.patient_id}`" target="_blank">{{ appointment.patient_name }}</a>
                            (<a :href="`${office_ally_href+appointment.external_patient_id}`" target="_blank">OA</a>)
                        </td>
                        <td>{{ appointment.therapist_name }}</td>
                        <td>
                            <span v-if="appointment.supervisor">{{ appointment.supervisor.provider_name }}</span>
                            <span v-else>-</span>    
                        </td>
                        <td>{{ appointment.insurance }}</td>
                        <td>{{ appointment.reason_for_visit }}</td>
                    </tr>
                    <tr>
                        <td colspan="7" style="border-top:none;">
                            <template v-if="appointment.change_pos">
                                <el-checkbox v-model="appointment.accept_change_pos">Change POS from '<b>{{ appointment.change_pos.from }}</b>' to '<b>{{ appointment.change_pos.to }}</b>'</el-checkbox>
<!--                                <br/>-->
                            </template>
                            <template v-if="appointment.change_modifier_a">
                                <el-checkbox v-model="appointment.accept_change_modifier_a">Change Modifier A from '<b>{{ appointment.change_modifier_a.from }}</b>' to '<b>{{ appointment.change_modifier_a.to }}</b>'</el-checkbox>
<!--                                <br/>-->
                            </template>
                            <template v-if="appointment.change_cpt">
                                <el-checkbox v-model="appointment.accept_change_cpt">Change CPT from '<b>{{ appointment.change_cpt.from }}</b>' to '<b>{{ appointment.change_cpt.to }}</b>'</el-checkbox>
<!--                                <br/>-->
                            </template>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
        <div class="salary-dialog-footer">
            <el-button type="primary" @click="createVisits">Create Visits</el-button>
            <el-button @click="closeDialog()">Cancel</el-button>
        </div>
    </el-dialog>
</template>

<script>
import {OFFICE_ALLY_BASE_HREF} from "../../settings";
export default {
    name: 'CompletedAppointmentsInfoModal',
    props: {
        isShowDialog: {
            type: Boolean,
            default: false,
        },
        selectedAppointmentIds: {
            type: Array,
            default: [],
        },
    },

    data() {
        return {
            isLoading: false,
            appointments: [],
            office_ally_href: OFFICE_ALLY_BASE_HREF,
        }
    },

    computed: {
        showDialog: {
            get() {
                return this.isShowDialog;
            },
            set(value) {
                if (!value) {
                    this.$emit('closeDialog');
                }
            }
        },
    },

    methods: {
        closeDialog() {
            this.showDialog = false;
        },
        createVisits() {
            this.isLoading = true;
            
            let appointments = this.appointments.map((appointment) => {
                return {
                    'id': appointment.id,
                    'accept_change_cpt': appointment.accept_change_cpt,
                    'accept_change_pos': appointment.accept_change_pos,
                    'accept_change_modifier_a': appointment.accept_change_modifier_a,
                    'supervisor_id': appointment.supervisor && appointment.supervisor.id,
                };
            });
            this.$store.dispatch('createVisit', {appointments})
                .then(() => {
                    window.location.reload();
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
    },

    mounted() {
        this.isLoading = true;
        axios.get('/api/appointment-dirties', {
            params: {
                ids: this.selectedAppointmentIds,
            }
        })
            .then(response => {
                this.appointments = response.data.dirties || [];
            })
            .finally(() => {
                this.isLoading = false;
            });
    },
}
</script>

<style scoped>

</style>