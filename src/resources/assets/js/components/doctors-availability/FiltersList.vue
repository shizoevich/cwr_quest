
<template>
    <ul class="sidebar-patients-list">
        <li><label for="week">Week:</label></li>
        <li>
            <el-date-picker
                v-model="week"
                id="week"
                type="week"
                :format="weekFormat"
                @change="changeWeek"
                :clearable="false"
                :picker-options="weekOption"
                placeholder="Pick a week">
            </el-date-picker>
        </li>
        <br />
        <li><label for="provider_id">Provider:</label></li>
        <li style="display: flex; align-items: center; gap: 5px;">
            <el-select
                name="provider_id"
                id="provider_id"
                v-model="filters.provider_id"
                filterable
                default-first-option
                placeholder="All"
                style="width: 100%; max-width: 270px;"
                @change="filterRuleSelect()">
                <el-option label="All" value="" />
                <el-option
                    v-for="provider in filtersValuesList.providers"
                    :value="provider.id"
                    :key="provider.id"
                    :label="provider.provider_name">
                </el-option>
            </el-select>
            <el-tooltip v-if="provider_insurances && provider_insurances.length" class="item" effect="dark" placement="bottom">
                <template #content>
                    <p>Provider Insurances:</p>
                    <ul style="padding-left: 25px;">
                        <li v-for="insurance in provider_insurances" :key="insurance.id">
                            {{ insurance.insurance }}
                        </li>
                    </ul>
                </template>
                <help />
            </el-tooltip>
        </li>
        <br />
        <li><label for="insurance_id">Insurances:</label></li>
        <li style="display: flex; align-items: center; gap: 5px;">
            <el-select
                name="insurance_id"
                id="insurance_id"
                v-model="filters.insurance_id"
                filterable
                default-first-option
                placeholder="All"
                style="width: 100%; max-width: 270px;"
                @change="filterRuleSelect()">
                <el-option label="All" value="" />
                <el-option
                    v-for="insurance in filtersValuesList.insurances"
                    :value="insurance.id"
                    :key="insurance.id"
                    :label="insurance.label">
                </el-option>
            </el-select>
            <el-tooltip v-if="insurance_providers && insurance_providers.length" class="item" effect="dark" placement="bottom">
                <template #content>
                    <p>Providers:</p>
                    <ul style="padding-left: 25px;">
                        <li v-for="provider in insurance_providers" :key="provider.id">
                            {{ provider.provider_name }}
                        </li>
                    </ul>
                </template>
                <help />
            </el-tooltip>
        </li>
        <br />

        <filter-collapse label="Lucet (Tridiuum):">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.kaiserType.isIndeterminate"
                v-model="filtersMeta.kaiserType.checkAll"
                @change="filterRuleCheckAll(filtersMeta.kaiserType)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.kaiser_types"
                @change="filterRuleCheckbox(filtersMeta.kaiserType)">
                <el-checkbox
                    v-for="type in filtersValuesList.kaiser_types"
                    :value="type"
                    :label="type"
                    :key="type"
                    class="filters-checkbox"
                >
                  {{ type }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Availability Type:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.availabilityType.isIndeterminate"
                v-model="filtersMeta.availabilityType.checkAll"
                @change="filterRuleCheckAll(filtersMeta.availabilityType)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.availability_types"
                @change="filterRuleCheckbox(filtersMeta.availabilityType)"
            >
                <el-checkbox
                    v-for="type in filtersValuesList.availability_types"
                    :value="type.id"
                    :label="type.id"
                    :key="type.id"
                    class="filters-checkbox"
                >
                    {{ type.type }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Availability Subtype:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.availabilitySubtype.isIndeterminate"
                v-model="filtersMeta.availabilitySubtype.checkAll"
                @change="filterRuleCheckAll(filtersMeta.availabilitySubtype)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.availability_subtypes"
                @change="filterRuleCheckbox(filtersMeta.availabilitySubtype)"
            >
                <el-checkbox
                    v-for="type in filtersValuesList.availability_subtypes"
                    :value="type.id"
                    :label="type.id"
                    :key="type.id"
                    class="filters-checkbox"
                >
                    {{ type.type }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Visit Type:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.visitType.isIndeterminate"
                v-model="filtersMeta.visitType.checkAll"
                @change="filterRuleCheckAll(filtersMeta.visitType)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.visit_types"
                @change="filterRuleCheckbox(filtersMeta.visitType)">
              <el-checkbox
                  v-for="type in filtersValuesList.visit_types"
                  :value="type.id"
                  :label="type.id"
                  :key="type.id"
                  class="filters-checkbox"
              >
                  {{ type.label }}
              </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Types of clients:">
          <el-checkbox
              class="filters-checkbox"
              :indeterminate="filtersMeta.clientsType.isIndeterminate"
              v-model="filtersMeta.clientsType.checkAll"
              @change="filterRuleCheckAll(filtersMeta.clientsType)"
          >
              All
          </el-checkbox>
          <el-checkbox-group
              v-model="filters.types_of_clients_id_all"
              @change="filterRuleCheckbox(filtersMeta.clientsType)"
          >
            <el-checkbox
                v-for="type in filtersValuesList.types_of_clients"
                :value="type.id"
                :label="type.id"
                :key="type.id"
                class="filters-checkbox"
            >
                {{ type.label }}
            </el-checkbox>
          </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Age groups:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.ageGroup.isIndeterminate"
                v-model="filtersMeta.ageGroup.checkAll"
                @change="filterRuleCheckAll(filtersMeta.ageGroup)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.age_group_id_all"
                @change="filterRuleCheckbox(filtersMeta.ageGroup)"
            >
                <el-checkbox
                    v-for="age_group in filtersValuesList.age_groups"
                    :value="age_group.id"
                    :label="age_group.id"
                    :key="age_group.id"
                    class="filters-checkbox"
                >
                    {{ age_group.label }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Ethnicities:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.ethnicity.isIndeterminate"
                v-model="filtersMeta.ethnicity.checkAll"
                @change="filterRuleCheckAll(filtersMeta.ethnicity)"
            >
              All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.ethnicities_id_all"
                @change="filterRuleCheckbox(filtersMeta.ethnicity)"
            >
                <el-checkbox
                    v-for="ethnicity in filtersValuesList['ethnicities']"
                    :value="ethnicity.id"
                    :label="ethnicity.id"
                    :key="ethnicity.id"
                    class="filters-checkbox"
                >
                  {{ ethnicity.label }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Languages:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.languages.isIndeterminate"
                v-model="filtersMeta.languages.checkAll"
                @change="filterRuleCheckAll(filtersMeta.languages)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.languages_id_all"
                @change="filterRuleCheckbox(filtersMeta.languages)"
            >
                <el-checkbox
                    v-for="language in filtersValuesList['languages']"
                    :value="language.id"
                    :label="language.id"
                    :key="language.id"
                    class="filters-checkbox"
                >
                    {{ language.label }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Patient categories:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.patientCategories.isIndeterminate"
                v-model="filtersMeta.patientCategories.checkAll"
                @change="filterRuleCheckAll(filtersMeta.patientCategories)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.patient_categories_id_all"
                @change="filterRuleCheckbox(filtersMeta.patientCategories)">
                <el-checkbox
                    v-for="category in filtersValuesList['patient_categories']"
                    :value="category.id"
                    :label="category.id"
                    :key="category.id"
                    class="filters-checkbox"
                >
                    {{ category.label }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Races:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.races.isIndeterminate"
                v-model="filtersMeta.races.checkAll"
                @change="filterRuleCheckAll(filtersMeta.races)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.races_id_all"
                @change="filterRuleCheckbox(filtersMeta.races)">
                <el-checkbox
                    v-for="race in filtersValuesList['races']"
                    :value="race.id"
                    :label="race.id"
                    :key="race.id"
                    class="filters-checkbox"
                >
                    {{ race.label }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Specialties:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.specialties.isIndeterminate"
                v-model="filtersMeta.specialties.checkAll"
                @change="filterRuleCheckAll(filtersMeta.specialties)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.specialties_id_all"
                @change="filterRuleCheckbox(filtersMeta.specialties)"
            >
                <el-checkbox
                    v-for="specialty in filtersValuesList['specialties']"
                    :value="specialty.id"
                    :label="specialty.id"
                    :key="specialty.id"
                    class="filters-checkbox"
                >
                    {{ specialty.label }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />

        <filter-collapse label="Types of therapy:">
            <el-checkbox
                class="filters-checkbox"
                :indeterminate="filtersMeta.treatmentTypes.isIndeterminate"
                v-model="filtersMeta.treatmentTypes.checkAll"
                @change="filterRuleCheckAll(filtersMeta.treatmentTypes)"
            >
                All
            </el-checkbox>
            <el-checkbox-group
                v-model="filters.treatment_types_id_all"
                @change="filterRuleCheckbox(filtersMeta.treatmentTypes)">
                <el-checkbox
                    v-for="treatmentType in filtersValuesList['treatment_types']"
                    :value="treatmentType.id"
                    :label="treatmentType.id"
                    :key="treatmentType.id"
                    class="filters-checkbox"
                >
                    {{ treatmentType.label }}
                </el-checkbox>
            </el-checkbox-group>
        </filter-collapse>
        <br />
    </ul>
</template>

<script>
import { OTHER_AVAILABILITY_SUBTYPE_ID } from "../../settings";
import FilterCollapse from "./FilterCollapse.vue";

export default {
    name: "FiltersList",
    components: {FilterCollapse},
    props: {
        filtersValue: {
            type: Object,
            default() {
                return {};
            },
        },
        startWeekDay: {
            type: Date,
            default: "",
        },
    },
    data() {
        return {
            ajax_filters_values: null,
            provider_insurances: null,
            insurance_providers: null,
            filters: {
                provider_id: "",
                office_id: "",
                insurance_id: "",
                kaiser_types: [...this.filtersValue.kaiser_types],
                visit_types: Array.from(
                    this.filtersValue.visit_types,
                    (item) => item.id
                ),
                types_of_clients_id_all: Array.from(
                    this.filtersValue.types_of_clients,
                    (item) => item.id
                ),
                age_group_id_all: Array.from(
                    this.filtersValue.age_groups,
                    (item) => item.id
                ),
                availability_types: Array.from(
                    this.filtersValue.availability_types,
                    (item) => item.id
                ),
                availability_subtypes: [OTHER_AVAILABILITY_SUBTYPE_ID],
                ethnicities_id_all: Array.from(
                    this.filtersValue.ethnicities,
                    (item) => item.id
                ),
                languages_id_all: Array.from(
                    this.filtersValue.languages,
                    (item) => item.id
                ),
                patient_categories_id_all: Array.from(
                    this.filtersValue.patient_categories,
                    (item) => item.id
                ),
                races_id_all: Array.from(
                    this.filtersValue.races,
                    (item) => item.id
                ),
                specialties_id_all: Array.from(
                    this.filtersValue.specialties,
                    (item) => item.id
                ),
                treatment_types_id_all: Array.from(
                    this.filtersValue.treatment_types,
                    (item) => item.id
                ),
            },
            filtersMeta: {
                kaiserType: {
                  name: "kaiserType",
                  filterOptions: [...this.filtersValue.kaiser_types],
                  checkAll: true,
                  isIndeterminate: false,
                  filterName: "kaiser_types",
                },
                visitType: {
                    name: "visitType",
                    filterOptions: Array.from(
                        this.filtersValue.visit_types,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "visit_types",
                },
                clientsType: {
                    name: "clientsType",
                    filterOptions: Array.from(
                        this.filtersValue.types_of_clients,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "types_of_clients_id_all",
                },
                ageGroup: {
                    name: "ageGroup",
                    filterOptions: Array.from(
                        this.filtersValue.age_groups,
                        (item) => item.id
                    ),
                    checkAll: true,
                    show: false,
                    isIndeterminate: false,
                    filterName: "age_group_id_all",
                },
                availabilityType: {
                    name: "availabilityType",
                    filterOptions: Array.from(
                        this.filtersValue.availability_types,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "availability_types",
                },
                availabilitySubtype: {
                    name: "availabilitySubtype",
                    filterOptions: Array.from(
                        this.filtersValue.availability_subtypes,
                        (item) => item.id
                    ),
                    checkAll: false,
                    isIndeterminate: true,
                    filterName: "availability_subtypes",
                },
                ethnicity: {
                    name: "ethnicity",
                    filterOptions: Array.from(
                        this.filtersValue.ethnicities,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "ethnicities_id_all",
                },
                languages: {
                    name: "languages",
                    filterOptions: Array.from(
                        this.filtersValue.languages,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "languages_id_all",
                },
                patientCategories: {
                    name: "patientCategories",
                    filterOptions: Array.from(
                        this.filtersValue.patient_categories,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "patient_categories_id_all",
                },
                races: {
                    name: "races",
                    filterOptions: Array.from(
                        this.filtersValue.races,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "races_id_all",
                },
                specialties: {
                    name: "specialties",
                    filterOptions: Array.from(
                        this.filtersValue.specialties,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "specialties_id_all",
                },
                treatmentTypes: {
                    name: "treatmentTypes",
                    filterOptions: Array.from(
                        this.filtersValue.treatment_types,
                        (item) => item.id
                    ),
                    checkAll: true,
                    isIndeterminate: false,
                    filterName: "treatment_types_id_all",
                },
            },
            week: "",
            weekOption: {
                firstDayOfWeek: 1,
            },
        };
    },
    computed: {
        filtersValuesList() {
            return this.ajax_filters_values != null
                ? this.ajax_filters_values
                : this.filtersValue;
        },
        offices() {
            return this.$store.state.offices;
        },
        weekFormat() {
            let startDateOfWeek = moment(this.week),
                lastDateOfWeek = moment(this.week).add(6, "d");
            return `[${startDateOfWeek.format("MMM")}] ${startDateOfWeek.format(
                "DD"
            )}  -  [${lastDateOfWeek.format("MMM")}] ${lastDateOfWeek.format(
                "DD"
            )}`;
        },
    },
    watch: {
        startWeekDay(value) {
            this.week = value;
        },
        'filters.provider_id'(val) {
            if (!val) {
                this.provider_insurances = null;
                return;
            }

            const payload = {
                providerId: val,
            };
            
            this.$store.dispatch('getProviderInsurances', payload)
                .then((response) => {
                    this.provider_insurances = response.data;
                })
                .catch((err) => {
                    this.provider_insurances = null;
                    console.error(err);
                });
        },
        'filters.insurance_id'(val) {
            if (!val) {
                this.insurance_providers = null;
                return;
            }

            const payload = {
                insuranceId: val,
            };

            this.$store.dispatch('getInsuranceProviders', payload)
                .then((response) => {
                    this.insurance_providers = response.data;
                })
                .catch((err) => {
                    this.insurance_providers = null;
                    console.error(err);
                })
        },
    },
    methods: {
        filterRuleSelect() {
            this.changeFilter();
        },

        filterRuleCheckAll({ checkAll, filterName, name }) {
            this.filters[filterName] = checkAll ? this.filtersMeta[name].filterOptions : [];
            this.filtersMeta[name].isIndeterminate = false;
            this.changeFilter();
        },

        filterRuleCheckbox({ filterName, name }) {
            let checkedCount = this.filters[filterName].length;
            this.filtersMeta[name].checkAll =
                checkedCount === this.filtersMeta[name].filterOptions.length;
            this.filtersMeta[name].isIndeterminate =
                checkedCount > 0 &&
                checkedCount < this.filtersMeta[name].filterOptions.length;
            this.changeFilter();
        },

        initStartOfWeek() {
            //  sets the week start date
            moment.updateLocale("en", { week: { dow: 1, doy: 7 } });
            this.week = moment().startOf("week").toString();
            moment.updateLocale("en", null);
        },

        changeWeek() {
            this.$emit("changeWeek", this.week);
        },

        changeFilter() {
            const filters = {
                provider_id: this.filters.provider_id,
                office_id: this.filters.office_id,
                insurance_id: this.filters.insurance_id,
            }

            for (const filterMeta of Object.values(this.filtersMeta)) {
                if (filterMeta.isIndeterminate) {
                    filters[filterMeta.filterName] = this.filters[filterMeta.filterName];
                }
            }

            this.$emit("changeFilter", filters);
        },
    },
    mounted() {
        this.initStartOfWeek();
        this.$store.dispatch("getOffices");

        // trigger filters change to reload data
        window.setTimeout(() => {
            this.changeFilter();
        }, 300);
    },
};
</script>

<style lang="scss" scoped>
.sidebar-patients-list {
    li {
        &:first-of-type {
            margin-top: 15px;
        }
    }
}

@media screen and (min-width: 930px) {
    .sidebar-patients-list {
        height: 790px;
    }
}
</style>

<style scoped>
.el-checkbox-group {
    display: flex;
    flex-direction: column;
}
</style>

<style>
.filters-checkbox {
    margin-bottom: 0;
    margin-right: 0;
}

.filters-checkbox .el-checkbox__label {
    width: calc(100% - 14px);
    text-overflow: ellipsis;
    overflow: hidden;
}

.filters-checkbox .el-checkbox__input {
    margin-bottom: 10px;
}
</style>
