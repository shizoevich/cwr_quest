<template>
    <div>
        <div class="panel dashboard-panel" :class="getPanelClass()">
            <div class="panel-heading">
                <div class="panel-heading-container">
                    <div class="head-label">
                        <div class="head-label-container">
                            <div>Missing Progress Notes</div>
                            <a
                                href="/statistic/missing-notes"
                                target="_blank"
                                class="head-view-link"
                            >
                                Details
                            </a>
                        </div>
                    </div>
                    <div class="head-count">
                        <span v-if="missing_notes">
                            {{missing_notes_count}}
                        </span>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="padding-top: 0">
                <table class="table table-layout-fixed" v-if="missing_notes && missing_notes.length">
                    <thead class="sticky-thead">
                        <tr>
                            <th>Patient</th>
                            <th>Insurance</th>
                            <th v-if="is_admin">Provider</th>
                            <th width="80px">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in missing_notes">
                            <td class="panel-patient-name">
                                <router-link :style="{color: '#' + item.hex_color}" :to="`${'/chart/' + item.id}`" :title="item.patient_name">
                                    {{ item.first_name }} {{ item.last_name }}
                                </router-link>
                            </td>
                            <td>{{ item.primary_insurance || '-' }}</td>
                            <td v-if="is_admin">{{ item.provider_name || '-' }}</td>
                            <td class="count-col">{{ getMissingNotesCount(item) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="panel-loader-container dashboard-table-empty" v-else-if="loading">
                    <pageloader add-classes="panel-loader" />
                </div>
                <div class="dashboard-table-empty" v-else>
                    All progress notes are up to date
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                missing_notes: null,
                loading: false,
            };
        },

        mounted() {
            this.loading = true;
            this.$store.dispatch('getDashboardProviderMissingNotes').then(response => {
                if(response.status === 200) {
                    this.missing_notes = response.data;
                    this.loading = false;
                }
            });
        },

        computed: {
            missing_notes_count() {
                if(this.missing_notes && this.missing_notes.length) {
                    let count = 0;
                    for(let i in this.missing_notes) {
                        count += parseInt(this.missing_notes[i].missing_note_count);
                    }

                    return count;
                }

                return 0;
            },
            is_admin() {
                return this.$store.state.isUserAdmin;
            }
        },

        methods: {
            getPanelClass() {
                if(this.missing_notes && this.missing_notes.length) {
                    return 'panel-red';
                }

                return 'panel-green';
            },

            getMissingNotesCount(patient) {
                return patient.missing_note_count;
            },
        },
    }
</script>