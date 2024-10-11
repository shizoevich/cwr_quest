<template>
    <!--Modals-->
    <div class="modal modal-vertical-center fade" data-backdrop="static"
         data-keyboard="false" id="are-you-still-here-modal" role="dialog" style="z-index:9999;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Are you still here?</h4>
                </div>
                <div class="modal-body">
                    No activity for {{ show_modal_timeout_mins }} minutes. Are you still here?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" @click.prevent="handleYesButtonClick">
                        Yes
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { LOGOUT_TIMEOUT_IF_MODAL_OPEN, STILL_HERE_MODAL_TIMEOUT } from '../settings';
import debounce from "../helpers/debounce";
import { eventBus } from '../app';

    export default {
        data() {
            return {
                showModalTimeout: STILL_HERE_MODAL_TIMEOUT,
                showModalTimeoutId: null,
                logoutTimeout: LOGOUT_TIMEOUT_IF_MODAL_OPEN,
                logoutTimeoutId: null,
                modal_has_shown: false,
            };
        },

        computed: {
            show_modal_timeout_ms() {
                return this.showModalTimeout * 1000;
            },

            show_modal_timeout_mins() {
                return Math.round(this.showModalTimeout / 60);
            },

            logout_timeout_ms() {
                return this.logoutTimeout * 1000;
            },
        },

        mounted() {
            this.setupTimers();

            localStorage.setItem('are_you_still_modal_has_shown', false.toString());

            window.addEventListener('storage', (el) => {
                if (el.key === 'are_you_still_modal_has_shown' && !JSON.parse(el.newValue)) {
                    this.closeModal();
                    this.resetTimer();
                }
            });

            eventBus.$on('reset-logout-timer', () => {
                this.resetTimer();
            });
        },

        methods: {
            startTimer() {
                this.showModalTimeoutId = setTimeout(this.handleShowModalTimeout, this.show_modal_timeout_ms);
            },

            resetTimer() {
                if (!this.modal_has_shown) {
                    localStorage.setItem('are_you_still_modal_has_shown', false.toString());

                    clearTimeout(this.showModalTimeoutId);
                    
                    if (this.logoutTimeoutId) {
                        clearTimeout(this.logoutTimeoutId);
                    }

                    this.startTimer();
                    this.debounceResetTimerOnServer();
                }
            },

            setupTimers() {
                $(document).on("keypress mousemove mousedown touchmove", this.resetTimer);
                this.startTimer();
            },

            handleShowModalTimeout() {
                localStorage.setItem('are_you_still_modal_has_shown', true.toString());
                $('#are-you-still-here-modal').modal('show');
                this.modal_has_shown = true;
                this.logoutTimeoutId = setTimeout(this.handleLogoutTimeout, this.logout_timeout_ms);
            },

            handleLogoutTimeout() {
                $('#logout-form').submit();
            },

            handleYesButtonClick () {
                this.$store.dispatch('sendEmptyRequest');
                this.closeModal();
                this.resetTimer();
                localStorage.setItem('are_you_still_modal_has_shown', false.toString());
            },

            closeModal() {
                $('#are-you-still-here-modal').modal('hide');
                this.modal_has_shown = false;
            },

            debounceResetTimerOnServer: debounce(function () {
                this.$store.dispatch('sendEmptyRequest');
            }, 30 * 1000),
        },
    }
</script>