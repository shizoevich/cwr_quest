<template>
    <el-dialog
            title="Additional Compensation"
            :visible.sync="showDialog"
            :close-on-click-modal="false"
            v-loading.fullscreen.lock="isLoading"
            class="salary-dialog bootstrap-modal">
        <div class="salary-dialog-body">
            <el-form :rules="formRule" ref="patientForm" :model="formData">
                <div class="form-row">
                    <h4 v-if="billing_period" style="margin-top:0;">
                        Billing Period:
                        <b>
                            <template v-if="billing_period.type.name === 'monthly'">
                                {{ $moment(billing_period.start_date).format('MMMM YYYY') }}
                            </template>
                            <template v-else>
                                {{ $moment(billing_period.start_date).format('MM/DD/YYYY') }} - {{ $moment(billing_period.end_date).format('MM/DD/YYYY') }}
                            </template>
                        </b>
                    </h4>
                </div>
                <div class="form-row">
                    <el-table class="table-additional"
                              :border="true"
                              :data="formData.additional"
                              style="width: 100%">
                        <el-table-column
                                prop="title"
                                class-name="table-additional__title"
                                label=""
                                min-width="250">
                        </el-table-column>
                        <el-table-column
                                prop="visit_count"
                                label="# of Visit"
                                width="180">
                            <template slot-scope="scope">
                                <el-form-item prop="visit_count" v-if="isShowOfVisits(scope.$index)">
                                    <el-form-item prop="notes">
                                        <el-input v-model="formData.additional[scope.$index].additional_data.visit_count"
                                                  class="form-field"></el-input>
                                    </el-form-item>
                                </el-form-item>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="paid_fee"
                                label="Amount Paid"
                                width="180">
                            <template slot-scope="scope">
                                <el-form-item prop="paid_fee">
                                    <el-input-number
                                            v-model="formData.additional[scope.$index].paid_fee"
                                            :min="formData.additional[scope.$index].is_other ? -9999 : 0"
                                            :step="1"
                                            :precision="2"
                                            :controls="false"
                                            class="form-field form-field-number">
                                    </el-input-number>
                                </el-form-item>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="notes"
                                label="Notes"
                                width="180">
                            <template slot-scope="scope">
                                <el-form-item prop="notes">
                                    <el-input v-model="formData.additional[scope.$index].notes"
                                              class="form-field"></el-input>
                                </el-form-item>
                            </template>
                        </el-table-column>
                        <el-table-column
                                fixed="right"
                                label="Control"
                                header-align="center"
                                width="80">
                            <template slot-scope="scope">
                                <div class="column-remove-line">
                                    <template v-if="!scope.row.is_other">
                                        <el-tooltip effect="dark" content="Clear"
                                                    placement="top">
                                            <el-button @click.prevent="clearRow(scope.$index)" plain
                                                       icon="el-icon-refresh-right" circle/>
                                        </el-tooltip>
                                    </template>
                                    <template v-else>
                                        <el-tooltip effect="dark" content="Remove line"
                                                    placement="top">
                                            <el-button type="danger"
                                                       @click.prevent="removeRow(scope.$index)" plain
                                                       icon="el-icon-delete" circle/>
                                        </el-tooltip>
                                    </template>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div class="table-additional-control">
                        <el-button type="primary" @click="addedRow" icon="el-icon-plus" plain
                                   circle></el-button>
                    </div>
                </div>
            </el-form>
        </div>
        <div class="salary-dialog-footer">
            <el-button type="primary" @click="save">Save</el-button>
            <el-button @click="closeDialog">Cancel</el-button>
        </div>
    </el-dialog>
</template>

<script>
    const defaultAdditionalRow = {
        title: 'Other',
        paid_fee: null,
        notes: '',
        is_other: true,
        additional_data: {visit_count: null},
    };
    export default {
        name: 'SalaryManagementDialog',
        props: {
            isShowDialog: {
                type: Boolean,
                default: false,
            },
            billingPeriodId: {
                type: Number,
                required: true,
            },
            providerId: {
                type: Number,
                required: true,
            }
        },
        data() {
            return {
                isLoading: false,
                formData: {
                    additional: [],
                },
                formRule: {},
                additionalTableTitles: ['Monthly Meeting Attendance', 'Overtime Compensation', 'Supervision', 'Late Appt. Cancellation'],
                billing_period: null,
                therapist_manage_timesheet:false,
            }
        },
        computed: {
            showDialog: {
                get() {
                    return this.isShowDialog;
                },
                set(value) {
                    if (!value) {
                        this.$emit('closeDialog');
                    }
                }
            },
            isRemoveRowDisabled() {
                let otherLine = this.formData.additional.filter(item => item.is_other)
                return !Boolean(otherLine) || otherLine.length === 1;
            }
        },
        methods: {
            save() {
                this.isLoading = true;
                axios.post(`/api/providers/${this.providerId}/additional-compensation`, {
                    billing_period_id: this.billingPeriodId,
                    additional_compensation: this.formData.additional,
                }).then(() => {
                    window.location.reload();
                }).catch(() => {
                    this.isLoading = false;
                });
            },
            initTableData() {
                this.isLoading = true;
                axios.get(`/api/providers/${this.providerId}/additional-compensation`, {
                  params: {
                    billing_period_id: this.billingPeriodId,
                  }
                }).then((response) => {
                    this.formData.additional = response.data.data;
                    this.billing_period = response.data.billing_period;
                }).finally(() => {
                    this.isLoading = false;
                });
            },
            addedRow() {
                this.formData.additional.push(_.cloneDeep(defaultAdditionalRow))
            },
            removeRow(index) {
                this.$delete(this.formData.additional, index)
            },
            clearRow(index) {
                this.formData.additional[index].additional_data.visit_count = null;
                this.formData.additional[index].paid_fee = null;
                this.formData.additional[index].notes = '';
            },
            isShowOfVisits(index) {
                return this.formData.additional[index].slug !== 'monthly_meeting_attendance';
            },
            closeDialog() {
                this.showDialog = false;
            },
        },
        mounted() {
            this.initTableData();
        }
    }
</script>

<style lang="scss">
    .salary-dialog {

        .el-dialog__header {
            display: flex;
        }

        .el-dialog {
            width: 95%;
            max-width: 900px;
        }

        .form-item {

            &-period {
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                align-items: flex-start;

                @media (min-width: 768px) {
                    flex-direction: row;
                    justify-content: center;
                    align-items: center;
                }

                .el-form-item__label {
                    margin-bottom: 0;
                    margin-right: 30px;
                }
            }
        }

        .table-additional {

            &.el-table {
                tbody, thead {

                    tr {
                        td, th {
                            border-bottom-width: 1px;
                        }
                    }
                }
            }

            .el-form-item {
                margin-bottom: 0;

                .el-input-number {
                    width: 100%;
                }
            }

            .column-remove-line {
                display: flex;
                justify-content: center;
                align-items: center;
            }

            &-control {
                display: flex;
                justify-content: flex-end;
                margin-top: 10px;
                padding-right: 20px;
            }

            &__title {
                font-size: 14px;
            }
        }

        &-footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
    }
</style>