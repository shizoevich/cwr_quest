<!--Form Name: "Kaiser TPI"-->
<template>
    <div style="height: 100%;">
        <router-link :to="{ path: '/forms/patient-' + this.$route.params.id }" class="btn btn-lg btn-success btn-back">Back</router-link>
        <div class="iframe-center-container">
            <iframe :src="iframeSrc" class="kayser-iframe" ref="kayser-iframe"></iframe>
        </div>
    </div>
</template>

<script>
    export default {
        computed: {
            patient() {
                return this.$store.state.currentPatient;
            },

            iframeSrc() {
                if (this.isValidSubscriberId()) {
                    return 'https://epchangewithinreach-tpi.polestarapp.com/register/patient_details?check_mrn=true&mrn=' + parseInt(this.patient.subscriber_id);
                }
            }

        },
        mounted() {
            this.fetchData();
        },
        methods: {
            fetchData() {
                let id = this.$route.params.id;
                this.$store.dispatch('getPatient', {id: id}).then(error => {
                    if (error) {
                        if (error.status === 403 || error.status === 404) {
                            this.$router.push({path: '/forms/404'});
                        }
                    }
                });
            },
            isValidSubscriberId() {
                return this.patient && this.patient.subscriber_id && /^\d+$/.test(this.patient.subscriber_id);
            },
        }
    }
</script>

<style scoped>
    .kayser-iframe {
        width: 100%;
        margin: 20px auto;
        position: relative;
        display: block;
        height: calc(100% - 40px);
    }
    .btn-back {
        margin: 20px 0 0 20px;
    }
</style>