<template>
    <div>
        <div class="filters-list">
            <div class="filters-list-item">
                <label for="date">Date</label>
                <ElDatePicker class="date-filter date-filter-2"
                              @change="setAppointmentsFilter"
                              v-model="filters.date"
                              id="date"
                              :format="dateFormat"
                              :value-format="dateFormat"
                              :editable="false"
                              :clearable="false"/>
            </div>
            <div class="filters-list-item">
                <label for="providersId">Therapist</label>
                <el-select id="providersId" v-model="filters.providers_id" default-first-option filterable
                           placeholder="All" style="width: 100%" @change="setAppointmentsFilter">
                    <el-option :value="null" label="All">
                    </el-option>
                    <el-option
                            v-for="provider in providerList"
                            :key="provider.id"
                            :label="provider.provider_name"
                            :value="provider.id">
                    </el-option>
                </el-select>
            </div>
            <div class="filters-list-item">
                <div class="filters-list-item__title">
                    Visit Type
                </div>
                <el-checkbox :indeterminate="visitTypeData.isIndeterminate"
                             v-model="visitTypeData.checkAll"
                             @change="filterRuleCheckAll(visitTypeData)">
                    All
                </el-checkbox>
                <el-checkbox-group v-model="filters.visit_type"
                                   @change="filterRuleCheckbox(visitTypeData)">
                    <el-checkbox v-for="visit_type in visitTypes"
                                 @change="setAppointmentsFilter"
                                 :label="visit_type.id"
                                 :key="visit_type.id">
                        {{visit_type.visit_type }}
                    </el-checkbox>
                </el-checkbox-group>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'FiltersList',
        data() {
            return {
                dateFormat: 'MM/dd/yyyy',
                providerList: [],
                offices: [],
                visitTypes: [{id: 'in_person', visit_type: 'In Person'}, {id: 'virtual', visit_type: 'Virtual'}],
                officeData: {
                    name: 'officeData',
                    filterOptions: [],
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: 'offices_id'
                },
                visitTypeData: {
                    name: 'visitTypeData',
                    filterOptions: [],
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: 'visit_type'
                },
                filters: {
                    date: null,
                    providers_id: null,
                    offices_id: [],
                    visit_type: ['in_person', 'virtual'],
                }
            }
        },
        computed: {
            providerName() {
                let currentProvider = this.providerList.find(item => item.id === this.filters.providers_id);
                return currentProvider ? currentProvider.provider_name : null;
            }
        },
        methods: {
            setAppointmentsFilter() {
                this.$emit('changeFilter', true)
                this.$store.dispatch('getEHRAppointments', this.filters)
                    .then(({data}) => {
                        this.$emit('setFilter', {
                            appointments: data.appointments,
                            filters: Object.assign(this.filters, {provider_name: this.providerName})
                        });
                        this.$emit('changeFilter', false);
                        this.$emit('updateAppointmentStatistic', data.appointments);
                    });
            },
            initDataTime() {
                this.filters.date = moment().format('MM/DD/YYYY');
                this.$emit('setFilter', {
                    appointments: [],
                    filters: Object.assign(this.filters, { provider_name: this.providerName })
                });
            },
            initDataProviders() {
                this.$store.dispatch('getProviderList').then(response => {
                    this.providerList = response.data;
                });
            },
            initDataOffices() {
                this.$store.dispatch('getOffices').then(response => {
                    this.offices = response.data;
                    this.officeData.filterOptions = Array.from(response.data, item => item.id);
                    this.filters.offices_id = this.officeData.filterOptions;
                });
            },
            initDataVisitType() {
                this.visitTypeData.filterOptions = Array.from(this.visitTypes, item => item.id);
                this.filters.visit_type = this.visitTypeData.filterOptions;
            },
            filterRuleCheckAll({checkAll, filterName, name}) {
                this.filters[filterName] = checkAll ? this[name].filterOptions : [];
                this[name].isIndeterminate = false;
                this.setAppointmentsFilter();
            },
            filterRuleCheckbox({filterName, name}) {
                let checkedCount = this.filters[filterName].length;
                this[name].checkAll = checkedCount === this[name].filterOptions.length;
                this[name].isIndeterminate = checkedCount > 0 && checkedCount < this[name].filterOptions.length;
            },
            handlePrevDayClick() {
                const prevDayDate = moment(this.filters.date, "MM/DD/YYYY").subtract(1, 'days').format("MM/DD/YYYY");
                this.filters.date = prevDayDate;
                this.setAppointmentsFilter();
            },
            handleNextDayClick() {
                const nextDayDate = moment(this.filters.date, "MM/DD/YYYY").add(1, 'days').format("MM/DD/YYYY");
                this.filters.date = nextDayDate;
                this.setAppointmentsFilter();
            },
            handleCurrentDayClick() {
                const currentDate = moment().format("MM/DD/YYYY");
                this.filters.date = currentDate;
                this.setAppointmentsFilter();
            },
            setupDayClickHandlers() {
                this.$parent.$on('prev-day-click', this.handlePrevDayClick);
                this.$parent.$on('next-day-click', this.handleNextDayClick);
                this.$parent.$on('current-day-click', this.handleCurrentDayClick);
            }
        },
        mounted() {
            this.initDataTime();
            this.initDataProviders();
            this.initDataOffices();
            this.initDataVisitType();
            this.setupDayClickHandlers();
        }
    }
</script>

<style lang="scss" scoped>
    .filters-list {

        &-item {
            margin-bottom: 20px;

            &__title {
                font-weight: 600;
                margin-bottom: 10px;
            }

            .el-checkbox-group {
                display: flex;
                flex-direction: column;
            }
        }
    }
</style>
