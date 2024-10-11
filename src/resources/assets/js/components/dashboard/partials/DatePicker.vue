<template>
    <el-date-picker
        v-if="inited"
        class="date-filter date-filter-2"
        v-model="date"
        :name="name"
        :format="date_format"
        :value-format="date_format"
        :editable="false"
        :clearable="false"
        :picker-options="pickerOptions"
    />
</template>

<script>
export default {
    props: {
        name: {
            type: String,
            default: "date",
        },
        defaultValue: {
            type: String,
            default: moment().format("MM/DD/YYYY"),
        },
        dateFormat: {
            type: String,
            default: "MM/dd/yyyy",
        },
        fromDate: {
            type: String,
        }
    },
    data: () => ({
        inited: false,
        date: null,
        date_format: null,
        pickerOptions: {},
    }),
    mounted() {
        const self = this;
        this.date = this.defaultValue;
        this.date_format = this.dateFormat;
        this.pickerOptions = {
            disabledDate(time) {
                if (self.fromDate) {
                    return time.getTime() < Date.parse(self.fromDate);
                }

                let nowDate = new Date();
                let monthAgo = new Date();
                monthAgo.setMonth(monthAgo.getMonth() - 1);
                monthAgo.setDate(0);
                return time.getTime() > nowDate.getTime() || time.getTime() <= monthAgo.getTime();
            },
        };
        this.inited = true;
        this.$nextTick(() => {
            this.$el.querySelector("input").classList.add("form-control");
        });
    },
};
</script>

<style>
    .form-control.el-input__inner {
        background-color: #ffffff;
    }
</style>
