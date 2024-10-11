<template>
    <el-collapse-item
        class="collapse-document__item timesheets-list-item"
        :class="{'timesheets-list-item__not-signed': !isSinged, 'timesheets-list-item__clickable': !!item.id}"
        :disabled="true"
        @click.native="handleClick"
    >
        <template slot="title">
            <div class="collapse-document__item-title">
                <span>{{item.provider_name}}</span>
                <div class="collapse-document__item-title__status" v-if="isReviewed">
                    {{reviewedDate}}
                </div>
                <div class="collapse-document__item-title__status" v-else>
                    {{signedDate}}
                </div>
            </div>
        </template>
    </el-collapse-item>
</template>

<script>
    export default {
        name: 'TimesheetsListItem',
        props: {
            item: {
                type: Object,
                default() {
                    return {}
                }
            },
            billingPeriodId: {
                type: Number || null,
                default: null
            }
        },
        computed: {
            isSinged() {
                return this.item.signed_at !== null;
            },
            isReviewed() {
                return this.item.reviewed_at !== null;
            },
            signedDate() {
                if (this.isSinged) {
                    return `Signed at ${moment(this.item.signed_at).format('MM/DD/YYYY')}`
                }
                return 'Not Signed'
            },
            reviewedDate() {
                return `Reviewed at ${moment(this.item.reviewed_at).format('MM/DD/YYYY')}`
            }
        },
        methods: {
            handleClick() {
                if (!this.item.id) {
                    return;
                }

                this.$router.push(`/dashboard/timesheets/${this.item.id}`);
            }
        }
    }
</script>

<style lang="scss">
    .timesheets-list-item {

        .collapse-document__item-title {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;

            &__status {
                font-weight: bold;
                color: #67C23A;
            }
        }

        &__not-signed {
            .collapse-document__item-title {
                &__status {
                    color: #F56C6C;
                }
            }
        }

        &__clickable {
            .el-collapse-item__header {
                cursor: pointer !important;
            }
        }
    }
</style>