<template>
    <div
        id="patient-credit-card-list-modal"
        class="modal modal-vertical-center fade"
        data-backdrop="static"
        data-keyboard="false"
        role="dialog"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Credit cards:</h5>
                </div>
                <div class="modal-body">
                    <ul>
                        <li v-for="(card, index) in cards" :key="index" :class="{'text-red': isExpired(card)}">
                            {{ formatCard(card) }}
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        cards: {
            type: Array,
            default: () => []
        },
        formatter: {
            type: Function
        },
        isExpired: {
            type: Function
        }
    },
    methods: {
        formatCard(card) {
            if (this.formatter) {
                return this.formatter(card);
            }

            return `**** **** **** ${card.last_four}`;
        }
    }
};
</script>

<style scoped></style>
  