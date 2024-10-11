<template>
    <div>
        <div v-for="alert in alerts" v-if="!alert.status">
            <div class="alert alert-danger" role="alert" v-html="alert.message" v-if="alert.message && typeof alert.message === 'string'">
            </div>
            <div class="alert alert-danger" role="alert" v-html="item" v-for="item in alert.message"
                 v-else-if="alert.message && typeof alert.message === 'object'">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [],
        data() {
            return {
                show: false
            }
        },
        computed: {
            alerts() {
                return this.$store.state.alerts
            },
            is_admin() {
                return this.$store.state.isUserAdmin;
            },
        },
        methods: {},
        mounted() {
            this.$store.dispatch('checkProviderMissingNotes');
            this.$store.dispatch('checkProviderTherapistSurveyStatus');
            this.$store.dispatch('getUserRoles').then(() => {
               if(this.is_admin) {
                   this.$store.dispatch('getInvalidTridiuumCredentials');
               }
            });
        }
    }
</script>

<style scoped>

</style>