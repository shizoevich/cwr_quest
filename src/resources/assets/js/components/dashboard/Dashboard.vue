<template>
    <div id="page-content-wrapper">
        <div id="page-content" class="content-with-footer">
            <doctors-availability-alert v-if="!is_audit_mode" />

            <clock :with-wrapper="false"/>
            
            <div v-if="!is_audit_mode" class="dashboard-wrapper">
                <div class="dashboard-block-2">
                    <dashboard-missing-notes />
                </div>
                <div class="dashboard-block-2">
                    <dashboard-missing-initial-assessments />
                </div>
                <div class="dashboard-block-2">
                    <dashboard-reauthorization-requests />
                </div>
                <div v-if="is_admin" class="dashboard-block-2">
                   <dashboard-copay />
               </div>
               <div class="dashboard-block-2">
                    <dashboard-inactive-patients />
                </div>
                <div v-if="!is_admin" class="dashboard-block-2">
                    <dashboard-assigned-patients />
                </div>
            </div>

<!--            <div class="row" v-if="is_dashboard_visits_chart_visible">-->
<!--                <div class="col-xs-12">-->
<!--                    <dashboard-visits-chart />-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
</template>

<script>
    export default {

        data() {
            return {
                is_dashboard_visits_chart_visible: false,
            }
        },

        computed: {
            is_audit_mode() {
                return this.$store.state.is_audit_mode;
            },
            is_admin() {
                return this.$store.state.isUserAdmin;
            }
        },

        mounted() {
            // this.$store.dispatch('isSecretary').then(response => {
            //     if(response.status === 200) {
            //         this.is_dashboard_visits_chart_visible = !response.data;
            //     }
            // });
        }
    }
</script>