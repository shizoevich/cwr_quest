<template>
    <div>
        <div class="filters-list">
            <div class="filters-list-item">
                <label for="providersId">Provider</label>
                <el-select id="providersId" v-model="filters.provider_id" default-first-option filterable
                            placeholder="All" style="width: 100%" @change="setFilter">
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
                <label for="insurances">Insurances</label>
                <el-select id="insurances" v-model="filters.insurances" default-first-option filterable multiple
                            placeholder="All" style="width: 100%" @change="setFilter">
                    <el-option
                            v-for="insurance in filterOptions.insurances"
                            :key="insurance.id"
                            :label="insurance.insurance"
                            :value="insurance.id">
                    </el-option>
                </el-select>
            </div>
            <div class="filters-list-item">
                <label for="specialties">Specialties</label>
                <el-select id="specialties" v-model="filters.specialties" default-first-option filterable multiple
                            placeholder="All" style="width: 100%" @change="setFilter">
                    <el-option
                            v-for="speciality in filterOptions.specialties"
                            :key="speciality.id"
                            :label="speciality.label"
                            :value="speciality.id">
                    </el-option>
                </el-select>
            </div>
            <div class="filters-list-item">
                <label for="clientFocus">Client Focus</label>
                <el-select id="clientFocus" v-model="filters.clientFocus" default-first-option filterable multiple
                            placeholder="All" style="width: 100%" @change="setFilter">
                    <el-option
                            v-for="focus in filterOptions.ageGroups"
                            :key="focus.id"
                            :label="focus.label"
                            :value="focus.id">
                    </el-option>
                </el-select>
            </div>
            <div class="filters-list-item">
                <label for="typeOfTherapy">Type of Therapy</label>
                <el-select id="typeOfTherapy" v-model="filters.typeOfTherapy" default-first-option filterable multiple
                            placeholder="All" style="width: 100%" @change="setFilter">
                    <el-option
                            v-for="therapy in filterOptions.treatmentTypes"
                            :key="therapy.id"
                            :label="therapy.label"
                            :value="therapy.id">
                    </el-option>
                </el-select>
            </div>
            <div class="filters-list-item">
                <label for="typesOfClients">Modality</label>
                <el-select id="typesOfClients" v-model="filters.modality" default-first-option filterable multiple
                            placeholder="All" style="width: 100%" @change="setFilter">
                    <el-option
                            v-for="client in filterOptions.typesOfClients"
                            :key="client.id"
                            :label="client.label"
                            :value="client.id">
                    </el-option>
                </el-select>
            </div>
            <div class="filters-list-item">
                <label for="languages">Language(s)</label>
                <el-select id="languages" v-model="filters.languages" default-first-option filterable multiple
                            placeholder="All" style="width: 100%" @change="setFilter">
                    <el-option
                            v-for="language in filterOptions.languages"
                            :key="language.id"
                            :label="language.label"
                            :value="language.id">
                    </el-option>
                </el-select>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'EHRTherapistsFilters',
        data() {
            return {
                isLoading: false,
                providerList: [],
                filterOptions: [],
                filters: {
                    provider_id: null,
                    insurances: null,
                    specialties: null,
                    clientFocus: null,
                    typeOfTherapy: null,
                    modality: null,
                    languages: null
                }
            }
        },
        mounted() {
            this.initOptionsData();
        },
        methods: {
            initOptionsData() {
                this.isLoading = true;

                let options = [];
                options.push(this.$store.dispatch('getProviderList')
                    .then(response => {
                        this.providerList = response.data || [];
                    }));
                options.push(this.$store.dispatch('getTherapistsFilterOptions')
                    .then((res) => {
                        this.filterOptions = res.data || [];
                    }));

                Promise.all(options)
                    .finally(() => {
                        this.isLoading = false;
                    });
            },

            setFilter() {
                this.$emit('changeFilters', this.filters);
            },
        },
    }
</script>

<style lang="scss" scoped>
    .filters-list {

        &-item {
            margin-bottom: 15px;

            & label {
                font-weight: 600;
                margin-bottom: 7px;
            }

            &:first-child{
                margin-top: 15px;
            }
        }
    }
</style>
