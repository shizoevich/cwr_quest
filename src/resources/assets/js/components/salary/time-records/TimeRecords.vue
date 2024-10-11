<template>
    <div class="page-time-records" v-loading.fullscreen.lock="isLoading">
        <div class="container">
            <div class="multistep-form multistep-form--success">
                <template v-if="steps.length > 0">
                    <div class="multistep-form-progress progress" v-if="steps.length > 1">
                        <div class="multistep-form-progress-bar progress-bar"
                             role="progressbar"
                             aria-valuenow="100"
                             aria-valuemin="0"
                             aria-valuemax="100"
                        ></div>
                    </div>
                    <div class="multistep-form-steps" :class="{ 'is-single': steps.length === 1 }">
                        <template v-for="(step, index) in steps">
                            <div class="step-container" :class="{ active: currentStep === index }">
                                <div class="step" :class="{active: currentStep === index,completed: step.completed}">
                                    <div class="step-number">
                                        <span>{{ index + 1 }}</span>
                                        <i class="el-icon-check"></i>
                                    </div>
                                </div>
                                <div class="step-label">
                                    Step <span class="index">{{ index + 1 }}</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <div class="multistep-form-content">
                    <h3 class="step-subtitle">
                        Pay Period: {{ billingPeriod.date }}
                    </h3>
                    <h2 class="step-heading">
                        {{ steps && steps.length ? `${steps[currentStep].title}` : '' }}
                    </h2>
                    <div class="step-content">
                        <time-records-table v-show="steps[currentStep] && steps[currentStep].name === 'visits'"
                                            @changeTable="changeStepData($event, 'visits')"
                                            :invalid-fields="tableFormDataError.visits"
                                            :billing-period="billingPeriod"
                                            :init-table-data="visitsData"
                                            :is-step-saved="isStepSaved('visits')"
                                            :is-editing-allowed="isEditingAllowed"
                                            :is-amount="false"
                                            :is-telehealth="true"
                                            :is-overtime="true"
                                            :is-date-count="true"
                                            :is-visits="true"/>
                        <time-records-table v-show="steps[currentStep] && steps[currentStep].name === 'cancellation'"
                                            @changeTable="changeStepData($event, 'cancellation')"
                                            :is-editing-allowed="isEditingAllowed"
                                            :is-step-saved="isStepSaved('cancellation')"
                                            :invalid-fields="tableFormDataError.cancellation"
                                            :billing-period="billingPeriod"
                                            :init-table-data="cancellationsData"
                                            :is-amount="true"
                                            :is-overtime="false"/>
                        
                        <supervisions v-show="steps[currentStep] && steps[currentStep].name === 'supervisions'"
                            :init-form-data="supervisionsData"
                            :changeData="changeSupervisionsData"
                            :is-editing-allowed="isEditingAllowed"
                            :invalid-fields="tableFormDataError.supervisions"
                        />

                        <sick-time
                            v-show="steps[currentStep] && steps[currentStep].name === 'sick_time'"
                            :init-form-data="sickTimeInitData"
                            :required-fields="requiredSickTimeFields"
                            :billing-period="billingPeriod"
                            :is-editing-allowed="isEditingAllowed"
                            @changeData="changeSickTime"
                        />
                    </div>
                </div>
                <div class="multistep-form-controls forms" :class="{'justify-end': currentStep === 0,'justify-between': currentStep > 0}">
                    <el-button type="success" plain @click="changingStep(false)" v-if="currentStep > 0" class="ml-0">
                        Previous
                    </el-button>
                    <el-button 
                        v-if="currentStep < steps.length - 1"
                        type="success"
                        class="ml-auto"
                        @click="nextStep"
                    >
                        Next
                    </el-button>
                    <el-button type="success" @click="saveSteps" v-else-if="isEditingAllowed">
                        Confirm & Submit
                    </el-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import onbeforeunload from "../../../helpers/onbeforeunload";
    import handlerServerError from "./../../../helpers/handlerServerError";
    import TimeRecordsTable from "./Table";
    import SickTime from "./SickTime";
    import Supervisions from "./Supervisions";
    import VueScrollTo from "vue-scrollto";

    export default {
        name: 'TimeRecords',
        components: {TimeRecordsTable, SickTime, Supervisions},
        data() {
            return {
                isLoading: false,
                formData: {
                    pay_period: 1,
                },
                currentStep: 0,
                visitsData: [],
                cancellationsData: [],
                supervisionsData: [],
                billingPeriod: {},
                steps: [],
                tableFormData: {
                    visits: {},
                    cancellation: {},
                    supervisions: {},
                },
                tableFormDataError: {
                    visits: [],
                    cancellation: [],
                    supervisions: [],
                },
                sickTimeData: {},
                sickTimeInitData: {},
                requiredSickTimeFields: [],
                isEditingAllowed: true,
            }
        },
        watch: {
            currentStep() {
                this.scrollTop();
            },
        },
        computed: {
            isSupervisor() {
                return this.$store.state.isUserSupervisor;
            },
        },
        mounted() {
            this.$store.dispatch('getUserRoles').then(res => {
                this.initWindowOnbeforeunload();
                this.initSteps(res.data.isSupervisorOrAdmin);
                this.initStepData();
                this.initTimesheetConfirmation();
            });
        },
        destroyed() {
            this.destroyWindowOnbeforeunload();
        },
        methods: {
            initSteps(isSupervisor) {
                let steps = [
                    {
                        name: "visits",
                        title: "Visits",
                        completed: false,
                        saveDispatchName: 'saveStepVisits',
                        getData: () => {
                            this.initTableData('getSalaryVisitsData', 'visitsData')
                        }
                    },
                    {
                        name: "cancellation",
                        title: "Late Cancellation Fee Charges",
                        saveDispatchName: 'saveStepCancellation',
                        completed: false,
                        getData: () => {
                            this.initTableData('getSalaryCancellationsData', 'cancellationsData')
                        }
                    },
                    {
                        name: "supervisions",
                        title: "Supervision",
                        completed: false,
                        saveDispatchName: 'saveStepSupervisions',
                        getData: () => {
                            this.initTableData('getSalarySupervisionsData', 'supervisionsData')
                        },
                    },
                    {
                        name: "sick_time",
                        title: "",
                        completed: false,
                        getData: () => {
                            this.initSickTimeData();
                        }
                    }
                ];

                if (!isSupervisor) {
                    steps = steps.filter(step => step.name !== "supervisions");
                }

                this.steps = steps;
            },

            initStepData() {
                if (!this.$route.query.step) {
                    let query = Object(Object.assign(this.$route.query, {step: this.steps[this.currentStep].name}))
                    this.$router.push({query: query})
                }
                for (let i = 0; i < this.steps.length; i++) {
                    let step = this.steps[i];
                    if (step.name === this.$route.query.step) {
                        step.getData();
                        break;
                    }
                    step.completed = true;
                    this.currentStep++;
                }
            },
            initTableData(dispatchName, tableData) {
                this.isLoading = true;
                this.$store.dispatch(dispatchName)
                    .then(({data}) => {
                        this.isEditingAllowed = data.is_editing_allowed;
                        this.billingPeriod = this.changedBillingPeriod(data.billing_period);
                        this[tableData] = this.changedTableData(data.data)
                    })
                    .catch(() => {
                        this.$message({
                            type: 'error',
                            message: 'Oops, something went wrong!',
                            duration: 10000,
                        });
                    })
                    .finally(() => this.isLoading = false)
            },
            initSickTimeData() {
                this.isLoading = true;
                this.$store.dispatch('initSickTimeBillingPeriods')
                    .then(({data}) => {
                        this.isEditingAllowed = data.is_editing_allowed;
                        if (data.timesheet) {
                            this.sickTimeInitData = data.timesheet;
                        }

                        this.billingPeriod = this.changedBillingPeriod(data.billing_period);
                    })
                    .catch(() => {
                        this.$message({
                            type: 'error',
                            message: 'Oops, something went wrong!',
                            duration: 10000,
                        });
                    })
                    .finally(() => this.isLoading = false)
            },
            initTimesheetConfirmation() {
                if (this.$route.query.force_redirected && Number(Number(this.$route.query.force_redirected) === 1))
                    this.$alert('Please fill out the timesheet for last week to continue using the EHR system. Thank you The CWR Admin Team', 'Attention', {
                        confirmButtonText: 'OK',
                    });
            },
            initWindowOnbeforeunload() {
                window.onbeforeunload = onbeforeunload;
            },
            destroyWindowOnbeforeunload() {
                window.onbeforeunload = null;
            },
            changedTableData(value) {
                let newData = [];
                if (value.length) {
                    value.forEach((item) => {
                        item.map((row, index) => {
                            row.is_united = index > 0;
                            row.patient_name = row.first_name + ' ' + row.last_name;
                            row.date = moment(row.date).format('MM/DD/yyyy')
                            newData.push(row);
                        })
                    });
                }
                return newData;
            },
            changedBillingPeriod(period) {
                let billingPeriodData = {
                    start_date: moment(period.start_date).format('MM/DD/YYYY'),
                    end_date: moment(period.end_date).format('MM/DD/YYYY'),
                    id: period.id,
                };
                if (period.name === 'monthly') {
                    billingPeriodData.date = moment(billingPeriodData.start_date).format('MMMM YYYY');
                    return billingPeriodData;
                }
                billingPeriodData.date = billingPeriodData.start_date + ' - ' + billingPeriodData.end_date;
                return billingPeriodData;
            },
            changingStep(isNext = true) {
                this.steps[this.currentStep].completed = isNext;
                isNext ? this.currentStep++ : this.currentStep--;
                this.steps[this.currentStep].getData();
                this.$router.push({query: {step: this.steps[this.currentStep].name}})
            },
            changeStepData(stepData, stepName) {
                this.tableFormData[stepName] = stepData
            },
            changeSupervisionsData(data) {
                this.changeStepData({ supervisions: data }, 'supervisions');
            },
            changeSickTime(data) {
                this.sickTimeData = data;
            },
            validationStep(stepName) {
                if (stepName === 'supervisions') {
                    return this.validationStepSupervisions();
                }
                
                let invalidFields = [];
                if (this.tableFormData[stepName].create && this.tableFormData[stepName].create.length) {
                    this.tableFormData[stepName].create.forEach((item) => {
                        for (const itemKey in item) {
                            if (item[itemKey] === '' || item[itemKey] === null || item[itemKey] === undefined) {
                                if (invalidFields.indexOf(item.rowId) === -1) {
                                    invalidFields.push(item.rowId);
                                }
                            }
                        }
                    })
                }
                this.tableFormDataError[stepName] = invalidFields;
                return this.tableFormDataError[stepName].length === 0;
            },
            validationStepSupervisions() {
                let invalidFields = [];
                if (this.tableFormData['supervisions'].supervisions && this.tableFormData['supervisions'].supervisions.length) {
                    this.tableFormData['supervisions'].supervisions.forEach((item) => {
                        for (const itemKey in item) {
                            if (item[itemKey] === '' || item[itemKey] === null || item[itemKey] === undefined) {
                                const rowId = `comment.${item.provider_id}`;
                                if (invalidFields.indexOf(rowId) === -1) {
                                    invalidFields.push(rowId);
                                }
                            }
                        }
                    });
                }
                this.tableFormDataError['supervisions'] = invalidFields;
                return this.tableFormDataError['supervisions'].length === 0;
            },
            scrollTop() {
                this.$nextTick(() => {
                    VueScrollTo.scrollTo(document.querySelector(".page-time-records"), 1000, {
                        container: "body",
                        duration: "1000",
                        easing: "ease",
                        offset: 0,
                        force: true,
                    });
                });
            },
            isStepSaved(stepName) {
                return !!this.steps && !!this.steps.length && this.steps[this.currentStep].name === stepName && this.steps[this.currentStep].completed;
            },
            nextStep() {
                let currentStep = this.steps[this.currentStep];
                if (!this.isEditingAllowed) {
                    this.changingStep(true);
                    return;
                }
                if (this.validationStep(currentStep.name)) {
                    this.isLoading = true;
                    let payload = Object.assign(this.tableFormData[currentStep.name], {billing_period_id: this.billingPeriod.id})
                    this.$store.dispatch(currentStep.saveDispatchName, payload)
                        .then(() => {
                            this.changingStep(true)
                        })
                        .catch((error) => {
                            handlerServerError(error, this)
                        })
                        .finally(() => this.isLoading = false)
                    return;
                }
                this.$message({
                    type: 'error',
                    message: 'Please fill in all required fields!',
                    duration: 10000,
                });
            },
            saveSteps() {
                this.isLoading = true;
                this.sickTimeData.billing_period_id = this.billingPeriod.id;
                this.$store.dispatch('saveStepSickTime', this.sickTimeData)
                    .then(() => {
                        this.$router.push('/salary/time-records/thanks');
                    })
                    .catch((error) => {
                        handlerServerError(error, this)
                    })
                    .finally(() => this.isLoading = false)
            },
        },
    }
</script>

<style lang="scss">
    .page-time-records {
        padding: 60px 0 80px;

        .multistep-form {

            .step-heading {
                font-size: 20px;
                margin-bottom: 30px;

                @media (min-width: 576px) {
                    font-size: 22px;
                    margin-bottom: 45px;
                }
            }

            .step-subtitle {
                font-size: 16px;
                margin-bottom: 20px;
            }

            .step-container {
                background: transparent;
            }

            .step-content {
                position: relative;

                &::before {
                    content: '';
                    position: absolute;
                    top: -15px;
                    left: -15px;
                    right: -15px;
                    bottom: -15px;
                    background: #fff;
                    border: 1px solid #EBEEF5;
                    border-radius: 2px;
                    z-index: -2;
                }
            }

            .step {

                &.completed {
                    .el-icon-check {
                        opacity: 1;
                    }

                    span {
                        opacity: 0;
                    }
                }

                &.active {
                    svg,
                    .el-icon-check {
                        opacity: 0;
                    }

                    span {
                        opacity: 1;
                    }
                }
            }

            &-controls {

                @media (max-width: 992px) {
                    margin-top: 30px;
                }
            }
        }
    }
</style>