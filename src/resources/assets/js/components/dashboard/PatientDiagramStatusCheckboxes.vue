<template>
    <div class="form-group" style="display:inline-block;">
        <label class="patient-status-checkbox" v-for="(status, index) in patient_statuses" :style="{color: getColor(status)}"
            :class="{hidden: (status.status == 'Unassign')}">
            <input type="checkbox" v-model="selected_diagram_statuses[index]">
            {{status.status}}
        </label>
    </div>
</template>

<script>
    export default {

        props: ['withColors'],

        created() {
            if(!this.patient_statuses) {
                this.$store.dispatch('getPatientStatuses');
            }
            let st = this.$parent.getSelectedDiagramStatuses();
            this.is_mounted_onchange = true;
            if(!st || !st.length) {
                let statuses = this.patient_statuses;
                for(let i = 0; i < statuses.length; i++) {
                    this.is_mounted_onchange = true;
                    this.selected_diagram_statuses[i] = true;
                }

            } else {
                this.selected_diagram_statuses = st;
            }
            if(st !== this.selected_diagram_statuses) {
                this.$parent.setSelectedDiagramStatuses(this.selected_diagram_statuses, false);
                this.is_mounted_onchange = false;
            }
        },

        data() {
            return {
                selected_diagram_statuses: [],
                is_mounted_onchange: false
            }
        },

        computed: {
            patient_statuses() {
                return this.$store.state.patient_statuses;
            }
        },

        methods: {
            getColor(status) {
                if(this.withColors === 'true') {
                    return '#'+status.hex_color;
                }
                return '#000000';
            }
        },

        watch: {
            selected_diagram_statuses() {
                if(!this.is_mounted_onchange) {
                    this.$parent.setSelectedDiagramStatuses(this.selected_diagram_statuses, true);
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