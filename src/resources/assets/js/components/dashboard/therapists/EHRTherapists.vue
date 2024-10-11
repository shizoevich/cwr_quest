<template>
    <div>
        <div id="page-sidebar" class="filters-sidebar">
            <div class="sidebar-wrap">
                <div class="sidebar-section sidebar-section-column">
                    <div class="sidebar-section-header">
                        Filters
                    </div>
                    <div class="sidebar-section-container">
                        <ehr-therapists-filters @changeFilters="changeFilters" />
                    </div>
                </div>
            </div>
        </div>
        <div id="page-content-wrapper" class="page-therapists" v-loading.fullscreen.lock="loading">
            <div id="page-content">
                <ehr-therapists-table :therapists="therapists" />
            </div>
        </div>
    </div>
</template>

<script>
    import EhrTherapistsFilters from "./EHRTherapistsFilters.vue";
    import EhrTherapistsTable from "./EHRTherapistsTable.vue";

    export default {
        components: {EhrTherapistsTable, EhrTherapistsFilters},
        data() {
            return {
                loading: false,
                therapists: [],
            }
        },
        mounted() {
            this.getTherapists();
        },
        methods: {
            changeFilters(filters) {
                this.getTherapists(filters);
            },
            getTherapists(filters = {}) {
                this.loading = true;
                this.$store.dispatch('getTherapists', filters)
                    .then(({ data }) => {
                        this.therapists = data.length ? data : [];
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        },
    }
</script>

<style lang="scss">
    .sidebar-section-collapse {

        .el-collapse-item__header {
            font-size: 16px;
        }
    }

    .page-therapists {
        .btn {
            &-primary {
                background: #409eff;
                border-color: #409eff;

                &:hover,
                &:focus {
                    background: #66b1ff;
                    border-color: #66b1ff;
                }
            }
        }

        .fc-prev-button,
        .fc-next-button {
            width: 40px;
            height: 40px;
        }

        .fc-today-button {
            height: 40px;
        }

        .help-icon {
            top: 65px;

            @media (max-width: 929px) {
                top: 20px;
            }
        }
    }
</style>

<style lang="scss" scoped>
    #page-content {
        padding-bottom: 0;
    }

    .sidebar-section {

        &-collapse {
            display: block;

            @media (min-width: 930px) {
                display: none;
            }
        }

        &-column {
            display: none;

            @media (min-width: 930px) {
                display: block;
            }
        }

        &-container {
            @media (min-width: 930px) {
                height: calc(100vh - 85px);
            }
        }
    }

    .schedule-appointments-wrapper {
        margin-bottom: 20px;
    }

    .statistic-cards {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;

        .statistic-card {
            background-color: white;
            border-radius: 4px;
            padding: 6px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 125px;
            border: 1px solid #EBEEF5;
        }
    }

    .selected-day {
        font-size: 1.75em;
        margin: 0;
    }
</style>
