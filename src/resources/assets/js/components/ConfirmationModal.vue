<template>
    <div class="modal modal-vertical-center fade"
            data-backdrop="static"
            data-keyboard="false"
            id="week-confirmation-attention"
            role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" aria-label="Close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-head">Next Week Availability</h4>
                </div>
                <div class="modal-body">
                    <p>
                        Please submit availability for the week of {{ confirmationWeekRange }}
                    </p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary" href="/chart/calendar">Availability</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BootstrapModal from '../mixins/bootstrap-modal';
import { formatWeek } from "../helpers/date";

export default {
    mixins: [
        BootstrapModal,
    ],

    computed: {
        confirmationWeek() {
            if (moment().isoWeekday() >= 4) {
                return parseInt(moment().format('w')) + 1;
            }
            
            return parseInt(moment().format('w'));
        },

        confirmationWeekRange() {
            let startDate = moment().isoWeekday() >= 4 ? moment().add(1, 'week').startOf('isoWeek') : moment().startOf('isoWeek');

            return formatWeek(startDate, startDate.clone().endOf('isoWeek'));
        },

        confirmationLoad()  {
            return this.$store.state.confirmationLoad;
        },

        confirmationStatus() {
            return this.$store.state.confirmationStatus;
        },
    },
    methods: {
        checkWeekConfirmation() {
            this.$store.dispatch('weekConfirmationStatus', {
                week: this.confirmationWeek,
                year: moment().format('Y')
            });
        },
        toggleModal() {
            if(this.$route.path == '/chart/calendar') {
                this.closeModal();
                return;
            }

            if(!this.confirmationStatus) {
                this.openWithoutOverlapping();
            } else {
                this.closeModal();
            }
        },
        openModal() {
            $('#week-confirmation-attention').modal('show');
        },
        closeModal() {
            $('#week-confirmation-attention').modal('hide');
        }
    },
    mounted() {
        if(this.$route.path.includes('update-notifications/history')) {
            return;
        }

        this.checkWeekConfirmation();
        this.$store.subscribe((mutation, state) => {
            if(mutation.type == 'setConfirmationStatus') {
                this.toggleModal();
            }
        })
    }
}
</script>

