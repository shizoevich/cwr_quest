<template>
    <div class="form-group" style="display:inline-block;">
        <label
            v-for="(status, index) in patient_statuses"
            class="patient-status-checkbox"
            :class="{hidden: isInvisible(status)}"
            :style="{color: getColor(status)}"
        >
            <input type="checkbox" v-model="selected_statuses[index]">
            {{ status.status }}
        </label>
    </div>
</template>

<script>
    export default {
        props: [
            'withColors',
            'invisibleStatuses',
            'invisibleStatusesForDoctors',
        ],

        mounted() {
            this.getPatientStatuses();

            let st = this.$parent.getSelectedStatuses();
            this.is_mounted_onchange = true;
            if (!st || !st.length) {
                let statuses = this.patient_statuses;
                for (let i = 0; i < statuses.length; i++) {
                    this.is_mounted_onchange = true;
                    this.selected_statuses[i] = true;
                }

            } else {
                this.selected_statuses = st;
            }
            if (st !== this.selected_statuses) {
                this.$parent.setSelectedStatuses(this.selected_statuses, false);
                this.is_mounted_onchange = false;
            }
        },

        data() {
            return {
                selected_statuses: [],
                is_mounted_onchange: false
            }
        },

        computed: {
            patient_statuses() {
                return this.$store.state.patient_statuses;
            },

            is_admin() {
                return this.$store.state.isUserAdmin;
            },
        },

        methods: {
            getPatientStatuses() {
                if (this.patient_statuses.length) {
                    return;
                }

                this.$store.dispatch('getPatientStatuses');
            },

            
            setSelectedStatuses(dataset, silent = false) {
                if (silent) {
                    this.is_mounted_onchange = true;
                }
                
                const statuses = this.patient_statuses;
                const selected_statuses = [];

                for (let i = 0; i < statuses.length; i++) {
                    selected_statuses.push(dataset.indexOf(statuses[i]['id']) !== -1);
                }

                this.selected_statuses = selected_statuses;
            },

            getColor(status) {
                if (this.withColors === 'true') {
                    return '#' + status.hex_color;
                }
                return '#000000';
            },

            isInvisible(status) {
                if (this.invisibleStatuses && Array.isArray(this.invisibleStatuses)) {
                    return this.invisibleStatuses.indexOf(status.status) !== -1;
                }

                if (this.invisibleStatusesForDoctors && Array.isArray(this.invisibleStatusesForDoctors)) {
                    return !this.is_admin && this.invisibleStatusesForDoctors.indexOf(status.status) !== -1;
                }

                return false;
            }
        },

        watch: {
            selected_statuses() {
                if (!this.is_mounted_onchange) {
                    this.$parent.setSelectedStatuses(this.selected_statuses, true);
                } else {
                    this.is_mounted_onchange = false;
                }
            },
        }
    }
</script>

<style scoped>
    label.patient-status-checkbox {
        margin-right: 10px;
        font-weight: normal;
    }
</style>