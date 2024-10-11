<template>
    <div class="ehr-table" v-if="isTableCompleted">
        <el-table
                :data="tableData"
                border
                :span-method="spanMethod"
                @row-click="rowClick"
                :row-class-name="tableRowClassName"
                height="calc(100vh - 145px)"
                style="width: 100%">
            <el-table-column
                    fixed
                    prop="time"
                    label="Time"
                    width="100">
            </el-table-column>
            <el-table-column
                    prop="created_at"
                    label="Created at"
                    width="100">
                    <template slot-scope="scope">
                        <div v-if="scope.row.created_at">
                            {{ getFormattedDateTime(scope.row.created_at) }}
                        </div>
                    </template>
            </el-table-column>
            <el-table-column
                    prop="patient_name"
                    label="Patient"
                    min-width="150">
                <template slot-scope="scope">
                    <el-link :href="`/chart/${scope.row.patient_id}`" target="_blank">
                        {{scope.row.patient_name}}
                    </el-link>
                </template>
            </el-table-column>
            <el-table-column
                    prop="primary_insurance"
                    label="Insurance"
                    min-width="150">
            </el-table-column>
            <el-table-column
                    prop="provider_name"
                    label="Therapist"
                    min-width="150">
            </el-table-column>
            <el-table-column
                    prop="reason_for_visit"
                    label="Visit Type"
                    min-width="150">
            </el-table-column>
            <el-table-column
                    label="Status"
                    min-width="150">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status">
                            {{ getAppointmentStatusName(scope.row) }}
                            <el-tooltip v-if="scope.row.start_completing_date || scope.row.patient_requested_cancellation_at || scope.row.custom_notes" class="item" effect="dark" placement="bottom">
                                <template #content>
                                    <p v-if="scope.row.start_completing_date" style="margin:0;">
                                        Status changed at: {{ getFormattedDateTime(scope.row.start_completing_date) }}
                                    </p>
                                    <p v-if="scope.row.patient_requested_cancellation_at" style="margin:0;">
                                        Patient Requested Cancellation At: {{ getFormattedDateTime(scope.row.patient_requested_cancellation_at) }}
                                    </p>
                                    <p v-if="scope.row.custom_notes" style="margin:0;">
                                        Comment: {{ scope.row.custom_notes }}
                                    </p>
                                </template>
                                <help />
                            </el-tooltip>
                        </div>
                        <div v-if="scope.row.rescheduled_appointment_date">
                            ({{ scope.row.rescheduled_appointment_date }})
                        </div>
                    </template>
            </el-table-column>
            <el-table-column
                    fixed="right"
                    prop="action"
                    label="Action"
                    width="150">
                <template slot-scope="scope">
                    <div v-if="scope.row.patient_name">
                        <el-button size="small" @click="openTableModal(scope.row)" type="primary" icon="el-icon-edit"
                                   plain/>
                        <el-button size="small" @click="openModalRemove(scope.row)" type="danger" icon="el-icon-delete"
                                   plain/>
                    </div>
                    <div v-else-if="!scope.row.is_hidden">
                        <el-button size="small" @click="openTableModal(scope.row)" type="success" icon="el-icon-plus"
                                   plain/>
                    </div>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script>
    import EHRDataTable from './EHRTableData.json';
    import {Notification} from "element-ui";
    import DatetimeFormated from '../../../mixins/datetime-formated';
    import AppointmentStatus from '../../../mixins/appointment-status';

    export default {
        name: 'EhrTable',

        mixins: [DatetimeFormated, AppointmentStatus],

        props: {
            appointments: {
                type: Array,
                default: function () {
                    return []
                }
            },
        },
        
        data() {
            return {
                refresh: 0,
                isTableCompleted: false,
                tableData: [],
                emptyRowData: [],
                defaultTableData: EHRDataTable,
                tableDataAppointments: this.appointments,
                therapist_manage_timesheet:false,
            }
        },
        methods: {
            rowClick(row) {
                !row.patient_name ? this.openTableModal(row) : null
            },
            openModalRemove(data) {
                this.$confirm('Are you sure you want to delete? Please notice that you can edit a created appointment', 'Warning', {
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel',
                    type: 'warning'
                }).then(() => {
                    this.$emit('startRemoveAppointment')
                    this.$store.dispatch('removeEHRAppointments', data.id)
                        .then(() => {
                            Notification.success({
                                title: 'Success',
                                message: 'Appointment was deleted successfully',
                                type: 'success'
                            });
                            this.$emit('removeAppointment')
                        })
                })
            },
            openTableModal(data) {
                this.$emit('openTableModal', data)
            },
            spanMethod({row, columnIndex}) {
                let count = 1;
                if (columnIndex === 0) {
                    if (row.patient_name !== '' && !row.is_united) {
                        this.tableData.forEach(() => {
                            if (this.tableData[this.tableData.indexOf(row)] !== -1) {
                                if (this.tableData[this.tableData.indexOf(row) + count].is_united) {
                                    count++;
                                }
                            }
                        })
                        return [count, 1];
                    } else if (row.is_united) {
                        return [0, 0];
                    }
                }
            },
            tableRowClassName({row}) {
                if (row.patient_name === '') {
                    return row.className + ' empty-row';
                }
                return row.className;
            },
            updateRowClassNames() {
                const firstRecordClassName = "first-record";
                const lastRecordClassName = "last-record";

                this.tableData = this.tableData.map((row, index) => {
                    if (index === 0) {
                        row.className = firstRecordClassName;
                        return row;
                    }

                    if (index + 1 === this.tableData.length) {
                        row.className = lastRecordClassName;
                        return row;
                    }

                    row.className = "record";
                    const prevRow = this.tableData[index - 1];
                    const nextRow = this.tableData[index + 1];

                    if (prevRow && nextRow) {
                        if (row.time === nextRow.time && row.time !== prevRow.time) {
                            row.className = firstRecordClassName;
                        } else if (row.time === prevRow.time && row.time !== nextRow.time) {
                            row.className = lastRecordClassName;      
                        } else if (row.time === prevRow.time && row.time === nextRow.time) {
                            row.className = "";
                        }
                    }

                    return row;
                });
            },
            initDataTable() {
                this.$store.dispatch('getTableAppointments').then(({data}) => {
                    this.$emit('updateAppointmentStatistic', data.appointments);
                    data.appointments.length ? this.initTableDataAppointments(data.appointments) : this.tableDataAppointments = [];
                    this.tableData = [...this.defaultTableData, ...this.tableDataAppointments];
                    this.addedEmptyRowTable();
                    this.tableData = [...this.tableData, ...this.emptyRowData]
                    this.tableData.sort((a, b) => {
                        let now = moment().format('MMMM DD YYYY'),
                            bdt = Date.parse(now + ' ' + b.time),
                            adt = Date.parse(now + ' ' + a.time);
                        return adt - bdt;
                    });
                    this.removeStubRow();
                    this.isTableCompleted = true;

                    this.updateRowClassNames();
                });
            },
            refreshTable(dataAppointments) {
                dataAppointments.length ? this.initTableDataAppointments(dataAppointments) : this.tableDataAppointments = [];
                this.tableData = [...this.defaultTableData, ...this.tableDataAppointments];
                this.addedEmptyRowTable();
                this.tableData = [...this.tableData, ...this.emptyRowData]
                this.tableData.sort((a, b) => {
                    let now = moment().format('MMMM DD YYYY'),
                        bdt = Date.parse(now + ' ' + b.time),
                        adt = Date.parse(now + ' ' + a.time);
                    return adt - bdt;
                });
                this.removeStubRow();

                this.updateRowClassNames();
            },
            initTableDataAppointments(appointments) {
                this.tableDataAppointments = appointments.map(item => {
                    let timeInUts = moment.utc(item.time * 1000);
                    item.time = moment.tz(timeInUts, 'America/Los_Angeles').format('hh:mm A');
                    item.is_united = false;
                    return item;
                });
                this.tableDataAppointments.forEach((appointmentsItem, index) => {
                    if (this.tableDataAppointments[index + 1] && this.tableDataAppointments[index + 1].time === this.tableDataAppointments[index].time) {
                        this.tableDataAppointments[index + 1].is_united = true;
                    }
                })
            },
            removeStubRow() {
                this.tableData.forEach((item, index) => {
                    for (let i = 0; i < this.tableDataAppointments.length; i++) {
                        if (item.patient_name === '' && item.time === this.tableDataAppointments[i].time && !item.is_united) {
                            this.$delete(this.tableData, index)
                            break;
                        }
                    }
                })
            },
            addedEmptyRowTable() {
                this.emptyRowData = [];
                this.tableDataAppointments.forEach((item, index) => {
                    if ((this.tableDataAppointments[index + 1] && this.tableDataAppointments[index + 1].time !== this.tableDataAppointments[index].time) || !this.tableDataAppointments[index + 1]) {
                        let emptyTableRow = {
                            "time": item.time,
                            "patient_name": "",
                            "first_name": "",
                            "last_name": "",
                            "middle_initial": "",
                            "provider_name": "",
                            "reason_for_visit": "",
                            "is_united": true
                        };
                        this.emptyRowData.push(emptyTableRow);
                    }
                });
            },
        },
        watch: {
            appointments(val) {
                this.refreshTable(val);
            }
        },
        mounted() {
            this.$store.dispatch('getOtherCancelAppointmentStatuses');
            this.$store.dispatch('getRescheduleAppointmentStatuses');
            this.initDataTable();
        },
    }
</script>

<style lang="scss">
    $border: 1px solid rgb(215, 215, 215);

    .ehr-table {
        table {
            border-collapse: collapse !important;

            tbody {

                tr {
    
                    &:last-of-type {
                        display: none;
                    }
                }
    
                .record {
                    border-top: $border;
                    border-bottom: $border;
                }
    
                .first-record {
                    border-top: $border;
                }
    
                .last-record {
                    border-bottom: $border;
                }
    
                .el-table_1_column_1 {
                    vertical-align: top;
                }
            }
        }

        .el-link {
            text-decoration: none;
        }

        .el-table--enable-row-hover .el-table__body tr:hover > td {
            background-color: transparent;
        }

        .empty-row {
            background-color: rgba(#F5F7FA, 0.25);
        }
    }
</style>
