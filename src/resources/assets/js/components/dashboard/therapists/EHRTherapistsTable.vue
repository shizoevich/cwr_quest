<template>
    <div class="ehr-therapists-table">
        <el-table
                :data="therapists"
                border
                row-class-name="record"
                height="calc(100vh - 145px)"
                style="width: 100%">
            <el-table-column
                    prop="provider_name"
                    label="Provider"
                    min-width="150">
                    <template slot-scope="scope">
                        <div class="break-word">{{ scope.row.provider_name }}</div>
                    </template>
            </el-table-column>
            <el-table-column
                    prop="email"
                    label="Work Email"
                    min-width="150">
            </el-table-column>
            <el-table-column
                    prop="insurances"
                    label="Insurances"
                    min-width="250">
                    <template slot-scope="scope">
                        <div v-if="scope.row.therapist_survey">
                            <span class="break-word">{{ scope.row.therapist_survey.insurances }}</span>
                        </div>
                    </template>
            </el-table-column>
            <el-table-column
                    prop="specialties"
                    label="Specialties"
                    min-width="300">
                    <template slot-scope="scope">
                        <div v-if="scope.row.therapist_survey">
                            <span class="break-word">{{ scope.row.therapist_survey.specialties }}</span>
                        </div>
                    </template>
            </el-table-column>
            <el-table-column
                    prop="age_groups"
                    label="Client Focus"
                    min-width="130">
                    <template slot-scope="scope">
                        <div v-if="scope.row.therapist_survey">
                            <span class="break-word">{{ scope.row.therapist_survey.age_groups }}</span>
                        </div>
                    </template>
            </el-table-column>
            <el-table-column
                    prop="treatment_types"
                    label="Type of Therapy"
                    min-width="150">
                    <template slot-scope="scope">
                        <div v-if="scope.row.therapist_survey">
                            <span class="break-word">{{ scope.row.therapist_survey.treatment_types }}</span>
                        </div>
                    </template>
            </el-table-column>
            <el-table-column
                    prop="types_of_clients"
                    label="Modality"
                    min-width="100">
                    <template slot-scope="scope">
                        <div v-if="scope.row.therapist_survey">
                            <span class="break-word">{{ scope.row.therapist_survey.types_of_clients }}</span>
                        </div>
                    </template>
            </el-table-column>
            <el-table-column
                    prop="languages_tridiuum"
                    label="Language(s)"
                    min-width="130">
                    <template slot-scope="scope">
                        <div v-if="scope.row.therapist_survey">
                            <span class="break-word">{{ scope.row.therapist_survey.languages_tridiuum }}</span>
                        </div>
                    </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script>
    export default {
        name: 'EHRTherapistsTable',

        props: {
            therapists: {
                type: Array,
                default: () => []
            },
        },
    }
</script>

<style lang="scss">
    $border: 1px solid rgb(215, 215, 215);

    .ehr-therapists-table {
        table {
            border-collapse: collapse !important;

            tbody {
    
                .record {
                    border-top: $border;
                    border-bottom: $border;
                }
            }
        }

        .el-link {
            text-decoration: none;
        }

        .el-table--enable-row-hover .el-table__body tr:hover > td {
            background-color: transparent;
        }

        .el-table thead {
            tr {
                border-bottom: $border;
            }

            .cell {
                padding-top: 10px;
                padding-bottom: 10px;
            }
        }
    }
    
    .break-word {
        word-break: break-word;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
</style>
