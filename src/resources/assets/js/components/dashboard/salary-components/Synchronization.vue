<template>
    <div class="modal modal-vertical-center fade" data-backdrop="static" data-keyboard="false"
         id="sync-visits-modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="radio">
                        <label><input type="radio" v-model="sync_by" value="date"> Date</label>

                        <ElDatePicker class="date-filter date-filter-2"
                                      id="sync_date"
                                      v-model="sync_date"
                                      :format="date_format"
                                      :value-format="date_format"
                                      :editable="false"
                                      :clearable="false"
                                      v-if="sync_by === 'date'"/>
                    </div>

                    <div class="radio">
                        <label><input type="radio" v-model="sync_by" value="month"> Month</label>
                        <ElDatePicker class="date-filter date-filter-2"
                                      id="sync_month"
                                      v-model="sync_month"
                                      format="MMMM yyyy"
                                      :value-format="date_format"
                                      type="month"
                                      :editable="false"
                                      :clearable="false"
                                      v-if="sync_by === 'month'"/>
                    </div>
                    <div class="radio">
                        <label><input type="radio" v-model="sync_by" value="date_range"> Date Range</label>
                    </div>
                    <template v-if="sync_by === 'date_range'">
                        <div class="form-group">
                            <label>From</label>
                            <ElDatePicker class="date-filter date-filter-2"
                                          id="sync_date_range_from"
                                          v-model="sync_start_date"
                                          :format="date_format"
                                          :value-format="date_format"
                                          :editable="false"
                                          :clearable="false"
                                          />
                        </div>
                        <div class="form-group">
                            <label>To</label>
                            <ElDatePicker class="date-filter date-filter-2"
                                          id="sync_date_range_to"
                                          v-model="sync_end_date"
                                          :format="date_format"
                                          :value-format="date_format"
                                          :editable="false"
                                          :clearable="false"
                                          />
                        </div>
                    </template>

<!--                    <div class="radio">-->
<!--                        <label><input type="radio" v-model="sync_by" value="therapist"> Therapist</label>-->
<!--                        <select class="form-control" id="sync_provider" v-if="sync_by === 'therapist'" v-model="sync_provider" style="max-width:170px;display:inline-block;">-->
<!--                            <option v-for="provider in providers" :value="provider.officeally_id">{{provider.provider_name}}</option>-->
<!--                        </select>-->
<!--                    </div>-->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary"
                            @click.prevent="syncVisits()"
                            :disabled="!sync_by">
                        Sync
                    </button>
                    <button type="button" class="btn btn-default" @click.prevent="closeModal()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {

        data() {
            return {
                providers: [],
                date_format: 'MM/dd/yyyy',

                sync_by: null,
                sync_date: null,
                sync_month: null,
                sync_provider: null,
                sync_start_date: null,
                sync_end_date: null,
            }
        },

        mounted() {
            this.$store.dispatch('getProviderList').then(response => {
                if(response.status === 200) {
                    this.providers = response.data;
                }
            });
        },

        methods: {
            closeModal() {
                $('#sync-visits-modal').modal('hide');
                this.sync_by = null;
                this.clearData();
            },

            clearData() {
                this.sync_date = null;
                this.sync_month = null;
                this.sync_provider = null;
            },

            validateForm() {
                let is_valid = true;
                switch(this.sync_by) {
                    case 'date':
                        if(!this.sync_date) {
                            is_valid = false;
                            $('#sync_date').addClass('input-error');
                        }
                        break;
                    case 'month':
                        if(!this.sync_month) {
                            is_valid = false;
                            $('#sync_month').addClass('input-error');
                        }
                        break;
                    case 'therapist':
                        if(!this.sync_provider) {
                            is_valid = false;
                            $('#sync_provider').addClass('input-error');
                        }
                        break;
                    case 'date_range':
                        if(!this.sync_start_date) {
                          is_valid = false;
                          $('#sync_date_range_from').addClass('input-error');
                        }
                        if (!this.sync_end_date) {
                          is_valid = false;
                          $('#sync_date_range_to').addClass('input-error');
                        }
                        break;
                    default:
                        return false;
                }

                return is_valid;
            },

            syncVisits() {
                if(!this.validateForm()) {
                    return false
                }
                let payload = {
                    sync_by: this.sync_by,
                    sync_date: this.sync_date,
                    sync_month: this.sync_month,
                    sync_provider: this.sync_provider,
                    sync_start_date: this.sync_start_date,
                    sync_end_date: this.sync_end_date,
                };
                this.$store.dispatch('syncVisits', payload);
                this.closeModal();
                window.location.href = window.location.href;
            },
        },

        watch: {
            sync_by() {
                this.clearData();
            },

            sync_date() {
                $('#sync_date').removeClass('input-error');
            },

            sync_month() {
                $('#sync_month').removeClass('input-error');
            },

            sync_provider() {
                $('#sync_provider').removeClass('input-error');
            },
            sync_start_date() {
                $('#sync_date_range_from').removeClass('input-error');
            },
            sync_end_date() {
                $('#sync_date_range_to').removeClass('input-error');
            },
        },
    }
</script>

<style scoped>
    .radio label {
        margin-right: 15px;
    }
</style>