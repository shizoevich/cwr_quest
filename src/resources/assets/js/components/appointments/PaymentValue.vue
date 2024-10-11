<template>
    <div>
        <template v-if="transactionValue">
            <div
                v-if="squareTransactionValue"
                class="text-center"
            >
                <span
                    :class="{
                        'text-green': isTransactionValuesEqual,
                        'text-yellow': !isTransactionValuesEqual,
                    }"
                >
                    ${{ transactionValue }}
                </span>
                
                <el-tooltip v-if="!isTransactionValuesEqual" class="item" effect="dark" placement="bottom">
                    <template #content>
                        <span v-if="transactionValuesDiff > 0">Partial pay (${{ squareTransactionValue }})</span>
                        <span v-else>Over charge (${{ squareTransactionValue }})</span>
                    </template>
                    <help />
                </el-tooltip>
            </div>

            <div
                v-else
                class="text-center"
            >
                <span class="text-red">${{ transactionValue }}</span>
            </div>
        </template>

        <div v-else class="text-center">-</div>
    </div>
</template>
  
<script>
export default {
    name: "PaymentValue",
    props: {
        transactionValue: {
            type: Number,
            default: 0
        },
        squareTransactionValue: {
            type: Number,
            default: 0
        }
    },

    computed: {
        transactionValuesDiff() {
            return this.transactionValue - this.squareTransactionValue;
        },
        isTransactionValuesEqual() {
            return this.transactionValuesDiff === 0;
        }
    },
};
</script>

<style scoped>

</style>
  