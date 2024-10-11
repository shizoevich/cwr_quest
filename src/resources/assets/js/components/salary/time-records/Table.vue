<template>
    <div class="salary-table">
        <el-table
                :data="tableData"
                :span-method="spanMethod"
                border
                :summary-method="getSummaries"
                show-summary
                style="width: 100%">
            <el-table-column
                    type="index"
                    width="50">
                <template slot-scope="scope">
                    <div class="column-content-center">
                        {{scope.$index + 1}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    prop="date"
                    label="Date"
                    width="200">
                <template slot-scope="scope">
                    {{dateText(scope.row.date)}}
                </template>
            </el-table-column>
            <el-table-column
                    prop="patient_name"
                    label="Patient"
                    min-width="150">
                <template slot-scope="scope">
                    <div class="patient-cell">
                        <el-link :href="`/chart/${scope.row.patient_id}`" type="primary" target="_blank">
                            {{scope.row.patient_name}}
                        </el-link>
                        <div class="patient-cell__summary" v-if="isVisits">{{missingProgressNote(scope.row)}}</div>
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    v-if="isAmount"
                    prop="amount"
                    label="Amount"
                    header-align="center"
                    width="120">
                <template slot-scope="scope">
                    <div class="column-content-center">
                        ${{tableData[scope.$index].amount}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    v-if="isOvertime"
                    prop="is_overtime"
                    label="Overtime"
                    header-align="center"
                    width="120">
                <template slot-scope="scope">
                    <div class="column-content-center">
                        <span>{{tableData[scope.$index].is_overtime ? 'Yes' : 'No'}}</span>
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    label="Remove"
                    header-align="center"
                    width="120"
                    v-if="is_admin || is_secretary"
                    >
                <template slot-scope="scope">
                    <div class="column-content-center">
                        <el-popconfirm @confirm="removeLine(tableData, scope.$index, true)"
                                       title="Are you sure to delete this?">
                            <el-button type="danger" icon="el-icon-delete" slot="reference" 
                                       :disabled="!isEditingAllowed" plain circle/>
                        </el-popconfirm> 
                    </div>
                </template>
            </el-table-column>
        </el-table>
        <div class="added-button-wrapper" v-if="therapist_manage_timesheet">
            <el-button @click="addLine" type="primary" icon="el-icon-plus" :disabled="!isEditingAllowed" plain circle/> 
        </div>
        <div class="time-records-new-table" v-if="newTableData.length">
            <el-table
                    :data="newTableData"
                    :span-method="spanMethod"
                    border
                    style="width: 100%">
                <el-table-column
                        prop="date"
                        label="Date"
                        min-width="150">
                    <template slot-scope="scope">
                        <el-date-picker
                                format="MM/dd/yyyy"
                                :disabled="!isEditingAllowed"
                                value-format="yyyy/MM/dd"
                                v-model="newTableData[scope.$index].date"
                                :picker-options="datePickerOptions"
                                :class="{'is-error': isInvalidField(newTableData[scope.$index].is_error, newTableData[scope.$index].date)}"
                                type="date"
                                placeholder="Pick a day">
                        </el-date-picker>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="patient_name"
                        label="Patient"
                        min-width="150">
                    <template slot-scope="scope">
                        <el-select
                                v-model="newTableData[scope.$index].patient_id"
                                :disabled="!isEditingAllowed"
                                :filter-method="filterPatientHandler"
                                v-el-select-lazy:patientData.getPatientForAppointments="loadMoreList"
                                :class="{'is-error': isInvalidField(newTableData[scope.$index].is_error, newTableData[scope.$index].patient_id)}"
                                filterable
                                placeholder="">
                            <el-option
                                    v-for="patient in patientList"
                                    :key="patient.id"
                                    :value="patient.id"
                                    :label="patient.full_name">
                            </el-option>
                        </el-select>
                    </template>
                </el-table-column>
                <el-table-column
                        v-if="isAmount"
                        prop="amount"
                        label="Amount"
                        header-align="center"
                        width="150">
                    <template slot-scope="scope">
                        <div class="column-content-center">
                            <el-input-number
                                    v-model="newTableData[scope.$index].amount"
                                    :disabled="!isEditingAllowed"
                                    :class="{'is-error': isInvalidField(newTableData[scope.$index].is_error, newTableData[scope.$index].amount)}"
                                    :min="0"
                                    :step="1"
                                    :precision="2"
                                    :controls="false">
                            </el-input-number>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        v-if="isTelehealth"
                        prop="is_telehealth"
                        label="Telehealth"
                        header-align="center"
                        width="100">
                    <template slot-scope="scope">
                        <div class="column-content-center">
                            <el-checkbox :disabled="!isEditingAllowed"
                                         v-model="newTableData[scope.$index].is_telehealth"/>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        v-if="isOvertime"
                        prop="is_overtime"
                        label="Overtime"
                        header-align="center"
                        width="100">
                    <template slot-scope="scope">
                        <div class="column-content-center">
                            <el-checkbox :disabled="!isEditingAllowed"
                                         v-model="newTableData[scope.$index].is_overtime"/>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        label="Remove"
                        header-align="center"
                        width="100">
                    <template slot-scope="scope">
                        <div class="column-content-center">
                            <el-popconfirm @confirm="removeLine(newTableData, scope.$index, false)"
                                           title="Are you sure to delete this?">
                                <el-button :disabled="!isEditingAllowed" type="danger" icon="el-icon-delete"
                                           slot="reference" plain circle/>
                            </el-popconfirm>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
        </div>
    </div>
</template>

<script>
    /**
     * If you need to add another field, it will also need to be duplicated in the handlerNewTableData method
     */
    const EMPTY_TABLE_ROW = {
        "date": "",
        "patient_name": "",
        "patient_id": "",
        "is_telehealth": false,
        "is_overtime": false,
        "is_united": false,
        "is_error": false,
    };
    const INIT_TABLE_FORM_DATA = {delete: [], edit: [], create: []};

    export default {
        name: 'TimeRecordsTable',
        props: {
            isEditingAllowed: {
                type: Boolean,
                default: true
            },
            isOvertime: {
                type: Boolean,
                default: false
            },
            isAmount: {
                type: Boolean,
                default: false
            },
            isStepSaved: {
                type: Boolean,
                default: false
            },
            isDateCount: {
                type: Boolean,
                default: false
            },
            isTelehealth: {
                type: Boolean,
                default: false
            },
            isVisits: {
                type: Boolean,
                default: false
            },
            initTableData: {
                type: Array,
                default() {
                    return [];
                }
            },
            billingPeriod: {
                type: Object,
                default() {
                    return {};
                }
            },
            invalidFields: {
                type: Array,
                default() {
                    return [];
                }
            },
        },
        data() {
            return {
                tableFormData: _.cloneDeep(INIT_TABLE_FORM_DATA),
                tableData: [],
                newTableData: [],
                patientData: {
                    pageIndex: 1,
                    pageSize: 20,
                    lastPageIndex: 20,
                    list: [],
                },
                datePickerOptions: {},
                rowId: 0,
                therapist_manage_timesheet:false,
            }
        },
        directives: {
            elSelectLazy: {
                bind(el, binding) {
                    const SELECTWRAP_DOM = el.querySelector('.el-select-dropdown .el-select-dropdown__wrap');
                    SELECTWRAP_DOM.addEventListener('scroll', function () {
                        const condition = this.scrollHeight - this.scrollTop <= this.clientHeight;
                        if (condition) {
                            binding.value(Object.keys(binding.modifiers)[0], binding.arg);
                        }
                    });
                }
            }
        },
        watch: {
            initTableData() {
                this.tableData = this.initTableData;
            },
            invalidFields(value) {
                this.newTableData.forEach(item => {
                    item.is_error = value.indexOf(item.rowId) > -1;
                })
            },
            isStepSaved(value) {
                if (value) {
                    this.newTableData = [];
                    this.tableFormData = _.cloneDeep(INIT_TABLE_FORM_DATA);
                }
            },
            newTableData: {
                handler(value) {
                    this.handlerNewTableData(value);
                },
                deep: true
            },
            tableFormData: {
                handler(value) {
                    this.$emit('changeTable', value)
                },
                deep: true
            },
        },
        computed: {
            patientList() {
                let num = this.patientData.pageIndex * this.patientData.pageSize;
                return this.patientData.list.filter((ele, index) => {
                    return index < num;
                })
            },
            is_admin() {
                return this.$store.state.isUserAdmin;
            },
            
            is_secretary() {
                return this.$store.state.isUserSecretary;
            },
        },
        methods: {
            initialDatePickerOption() {
                let self = this;
                this.datePickerOptions = {
                    disabledDate(time) {
                        return time.getTime() < moment(self.billingPeriod.start_date).format('x') || time.getTime() > moment(self.billingPeriod.end_date).format('x')
                    }
                }
            },
            initPatientList() {
                this.$store.dispatch('getPatientForAppointments', {
                    page: this.patientData.pageIndex,
                    limit: this.patientData.pageSize,
                }).then(({data}) => {
                    this.patientData.list = data.patients.data;
                    this.patientData.lastPageIndex = data.patients.last_page;
                })
            },
            spanMethod({row, columnIndex}) {
                let count = 1;
                if (columnIndex === 1) {
                    if (row.is_united) {
                        return [0, 0];
                    }
                    this.tableData.forEach(() => {
                        if (this.tableData[this.tableData.indexOf(row)] !== -1 && this.tableData[this.tableData.indexOf(row) + count]) {
                            if (this.tableData[this.tableData.indexOf(row) + count].is_united) {
                                count++;
                            }
                        }
                    })
                    return [count, 1];
                }
            },
            removeLine(table, index, isTableData) {
                if (isTableData) {
                    this.tableFormData.delete.push(table[index].id);
                }
                this.changedRowIsUnited(table, index);
                this.$delete(table, index);
            },
            addLine() {
                this.rowId++;
                EMPTY_TABLE_ROW.rowId = this.rowId;
                this.newTableData.push(_.cloneDeep(EMPTY_TABLE_ROW))
            },
            getSummaries({columns, data}) {
                const sums = [];
                columns.forEach((column, index) => {
                    if (index === 1) {
                        sums[index] = 'Total';
                        return;
                    }
                    let count = 0,
                        amount = 0;
                    if(this.isOvertime) sums[3] = count;
                    if(this.isAmount) sums[3] = `$${amount}`;
                    data.forEach(item => {
                        if (this.isOvertime && item.is_overtime) {
                            count++;
                            sums[3] = count;
                        }
                        if (this.isAmount) {
                            amount+= Number(item.amount);
                            sums[3] = `$${amount.toFixed(2)}`;
                        }
                    })
                    sums[2] = data.length;
                });
                return sums;
            },
            changedRowIsUnited(table, index) {
                if(table[index + 1]) {
                    table[index + 1].is_united = false;
                }
            },
            missingProgressNote(row) {
                if (row.is_progress_note_missing) {
                    return row.is_initial ? '(Missing Initial Assessment)' : '(Missing Progress Note)';
                }
                return '';
            },
            isInvalidField(isError, value) {
                return isError && !value;
            },
            handlerIsOvertime(row, initOvertimeValue) {
                let editRow = {id: row.id, is_overtime: row.is_overtime},
                    onlyChangesIndex = this.tableFormData.edit.findIndex(item => item.id === editRow.id);
                if (onlyChangesIndex > -1) {
                    this.tableFormData.edit.splice(onlyChangesIndex, 1, editRow);
                    if (editRow.is_overtime === initOvertimeValue) {
                        this.tableFormData.edit.splice(onlyChangesIndex, 1);
                    }
                    return
                }
                this.tableFormData.edit.push(editRow);
            },
            handlerNewTableData(value) {
                let newValue = [];
                value.forEach(item => {
                    let newItem = {
                        date: item.date ? moment(item.date).format('YYYY-MM-DD') : '',
                        billing_period_id: this.billingPeriod.id,
                        patient_id: item.patient_id,
                        rowId: item.rowId,
                    };
                    if (this.isOvertime) newItem.is_overtime = item.is_overtime;
                    if (this.isTelehealth) newItem.is_telehealth = item.is_telehealth;
                    if (this.isAmount) newItem.amount = item.amount;
                    newValue.push(newItem);
                })
                this.tableFormData.create = newValue;
            },
            updateLazyLoadList(dispatchName, dataName) {
                let payload = {
                    page: this[dataName].pageIndex,
                    limit: this[dataName].pageSize
                };
                this.$store.dispatch(dispatchName, payload).then(({data}) => {
                    let dataList = dataName === 'patientData' ? data.patients.data : data.data;
                    this[dataName].list = _.uniqWith(this[dataName].list.concat(dataList), _.isEqual);
                })
            },
            loadMoreList(dispatchName, dataName) {
                this[dataName].pageIndex++;
                if (this[dataName].pageIndex <= this[dataName].lastPageIndex) {
                    this.updateLazyLoadList(dispatchName, dataName);
                }
            },
            filterPatientHandler(query) {
                this.filterLazyLoadHandler(query, 'getPatientForAppointments', 'patientData', 'initPatientList')
            },
            filterLazyLoadHandler(query, dispatchName, dataName, initFunctionName) {
                if (query !== '') {
                    let payload = {
                        limit: this[dataName].pageSize,
                        search_query: query
                    };
                    this.$store.dispatch(dispatchName, payload).then(({data}) => {
                        this[dataName].list = dataName === 'patientData' ? data.patients.data : data.data;
                    })
                } else {
                    this[dataName].pageIndex = 1;
                    this[initFunctionName]();
                }
            },
            emitChangeTable() {
                this.$emit('changeTable', this.tableFormData)
            },
            dateText(date) {
                let count = 1;
                this.tableData.forEach(item => {
                    if (item.date === date && item.is_united) {
                        count++;
                    }
                })
                if (this.isDateCount) {
                    return `${date} (${count} ${count > 1 ? 'Visits' : 'Visit'})`;
                }
                return date;
            },

            getValueTherapistManageTimesheet() {
                return axios({
                method: 'get',
                url: '/api/system/therapist-custom-timesheet', 
              }).then(response => {
                this.therapist_manage_timesheet = response.data.therapist_custom_timesheet;
              });
            }
        },
        mounted() {
            this.initPatientList();
            this.initialDatePickerOption();
            this.emitChangeTable();
            this.getValueTherapistManageTimesheet();
        }
    }
</script>

<style lang="scss">

    .time-records-table {
        .el-input,
        .el-input-number,
        .el-select {

            &.is-error {
                .el-input__inner {
                    border: 1px solid #F56C6C;
                }
            }
        }

        .el-link {
            text-decoration: none;
        }

        .el-select {
            width: 100%;
        }

        .el-table--enable-row-hover .el-table__body tr:hover > td {
            background-color: transparent;
        }

        .column-content-center {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .added-button-wrapper {
            margin: 20px 0 30px;
            padding-right: 40px;
            display: flex;
            justify-content: flex-end;
        }
    }
</style>
