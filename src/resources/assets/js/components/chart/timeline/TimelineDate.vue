<template>
    <div class="dropdown scroll-dropdown" :id="item.type + item.model.id" v-if="item.type === 'date'">
        <button id="dLabel" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="btn btn-primary btn-dropdown bg-green" :class="isScrollLoading && 'scroll-dropdown__disabled'">
            <span class="btn__text">
                {{ getFormattedDateWithDayOfWeek(item.model.created_at) }}
            </span>
            <span class="btn__caret scroll-dropdown__caret">
                <pageloader v-if="isScrollLoading" addClasses="scroll-dropdown__loader"></pageloader>
                <img v-else class="" src="/images/icons/icon-caret.svg" />
            </span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dLabel">
            <li class="dropdown-menu__item" v-for="(scrollItem) in scrollDropdown" :key="scrollItem.id">
                <span
                    v-if="scrollItem.id !== 3"
                    class="dropdown-menu__item_text"
                    role="button"
                    @click="scrollItem.action"
                >
                    {{ scrollItem.label }}
                </span>
                <div class="position-relative" v-else>
                    <span
                        class="dropdown-menu__item_text"
                        role="button"
                        @click.stop.prevent="focusDatepicker"
                    >
                        {{ scrollItem.label }}
                    </span>
                    <el-date-picker
                        ref="datePicker"
                        class="scroll-date-picker"
                        type="date"
                        format="MM/dd/yyyy"
                        value-format="yyyy-MM-dd"
                        @input="(val) => dateInputDecorator(val, scrollItem.action)"
                    >
                    </el-date-picker>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>

export default {
    name: "timeline-date",
    props: {
        item: Object,
        isScrollLoading: Boolean,
        getFormattedDateWithDayOfWeek: Function,
        scrollDropdown: Array,
    },
    data() {
        return {

        }
    },
    methods: {
        focusDatepicker() {
            if (!this.$refs || !this.$refs.datePicker) {
                return;
            }

            if (this.$refs.datePicker.length) {
                this.$refs.datePicker[0].focus();
            } else {
                this.$refs.datePicker.focus();
            }
        },
        dateInputDecorator(value, action) {
            document.body.click(); // click to body to close dropdown
            action(value);
        }
    }
}
</script>

<style scoped></style>