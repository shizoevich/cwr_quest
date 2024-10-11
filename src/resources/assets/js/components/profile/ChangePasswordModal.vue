<template>
    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         id="pls-change-password" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <p>
                        To ensure compliance with HIPAA regulations and protect PHI of our patients, Change Within Reach,
                        Inc. requires its contractors to change passwords every 90 days. To continue using the system, please
                        change your password on or before <b>{{is_password_outdated.next_change_password_date}}</b>.
                        You have <b>{{is_password_outdated.days_left}}</b> days left to change the password to your account.
                    </p>
                </div>
                <div class="modal-footer">
                    <a href="/change-password" class="btn btn-primary" type="button">
                        Change Password<span v-if="is_password_outdated.days_left > 0"> Now</span>
                    </a>
                    <button class="btn btn-default" data-dismiss="modal" v-if="is_password_outdated.days_left > 0">
                        I&#39;ll Do It Later
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
//            this.$store.dispatch('isPasswordOutdated');
        },

        computed: {
            is_password_outdated() {
                return this.$store.state.is_password_outdated;
            }
        },

        watch: {
            is_password_outdated() {
                if(this.is_password_outdated) {
                    if(this.is_password_outdated.outdated || this.is_password_outdated.days_left <= 7) {
                        $('#pls-change-password').modal('show');
                    }
                }
            }
        }
    }
</script>