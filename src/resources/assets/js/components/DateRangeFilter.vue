<template>
    <form action="" method="post">
        <input type="hidden" name="_token" :value="csrfToken">
        <div class="form-group date-filter-item">
            <label>Filter By</label>
            <select class="form-control" v-model="selected_filter_type" name="selected_filter_type">
                <option value="1">Date</option>
                <option value="2">Date Range</option>
                <option value="3">Month</option>
            </select>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 1 || selected_filter_type == 2">
            <label v-if="selected_filter_type == 1">Date</label>
            <label v-if="selected_filter_type == 2">From</label>
            <ElDatePicker class="date-filter date-filter-2"
                          v-model="date_from"
                          name="date_from"
                          :format="date_format"
                          :value-format="date_format"
                          :editable="false"
                          :clearable="false"/>
        </div>

        <div class="form-group date-filter-item" v-if="selected_filter_type == 3">
            <label>Month</label>
            <ElDatePicker class="date-filter date-filter-2"
                    v-model="month"
                    name="month"
                    format="dd MMMM yyyy"
                    value-format="dd MMMM yyyy"
                    type="month"
                    :editable="false"
                    :clearable="false"/>
        </div>


        <div class="form-group date-filter-item" v-if="selected_filter_type == 2">
            <label>To</label>
            <ElDatePicker class="date-filter date-filter-2"
                          v-model="date_to"
                          name="date_to"
                          :format="date_format"
                          :value-format="date_format"
                          :editable="false"
                          :clearable="false"/>
        </div>

        <div class="form-group inline-block" style="position:relative;">
            <button class="btn btn-primary" style="width:92px;position:absolute;top:-9px;">Show</button>
        </div>
    </form>
</template>
<script>
    export default {

        props: [
            'csrfToken',
            'propMonth',
            'propFilterType',
            'propDateFrom',
            'propDateTo',
            'dateToStep',
        ],

        data() {
            return {
                selected_filter_type: 3,
                date_from: null,
                date_to: null,
                date_format: 'MM/dd/yyyy',
                month: null,
            }
        },

        mounted() {
            if(this.propDateFrom) {
                this.date_from = this.propDateFrom;
            } else {
                this.date_from = new Date();
            }

            if(this.propFilterType) {
                this.selected_filter_type = this.propFilterType;
            }
            if(this.propMonth) {
                this.month = this.propMonth;
            }

            if(this.propDateTo) {
                this.date_to = this.propDateTo;
            } else {
                this.date_to = this.getDateTo();
            }
        },

        methods: {
            getDateTo() {
                let dateStep = 1;
                if(this.dateToStep !== null && this.dateToStep !== undefined) {
                    return this.date_from;
                }
                let tmp = this.date_from;
                let result = new Date(tmp.setMonth(tmp.getMonth() + dateStep));
                tmp.setMonth(tmp.getMonth() - dateStep);
                return result;
            }
        },

        computed: {},
    }
</script>

<style scoped>
    .date-filter-item {
        width: 170px;
        display: inline-block;
        margin-right: 10px;
    }
</style>