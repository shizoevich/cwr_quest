<template>
    <li :class="'filter-collapse' + (show ? ' active' : '')">
        <div @click="toggleShow">
            <div class="filter-collapse__caption">
                <label class="filter-collapse__label">{{ label }}</label>
                <i class="filter-collapse__caret el-icon-arrow-up"></i>
            </div>
        </div>
        <div
            v-show="show"
            class="filter-collapse__body"
        >
            <slot/>
        </div>
    </li>
</template>

<script>
export default {
    name: 'FilterCollapse',
    props: {
        label: {
            required: true,
            type: String,
        }
    },
    data() {
        return {
            show: false,
        }
    },
    methods: {
        toggleShow() {
            this.show = !this.show
        }
    }
}
</script>

<style scoped>
.filter-collapse__caption {
    position: relative;
    border-bottom: 1px solid #DCDFE6;
    padding-bottom: 3px;
    margin-bottom: 12px;
    cursor: pointer;
}

.filter-collapse__caption:hover {
    border-color: #C0C4CC;
}

.filter-collapse.active .filter-collapse__caption {
    border-color: #000;
}

.filter-collapse__label {
    cursor: pointer;
}

.filter-collapse__caret {
    -webkit-transition: -webkit-transform .3s;
    transition: -webkit-transform .3s;
    transition: transform .3s;
    transition: transform .3s, -webkit-transform .3s;
    -webkit-transform: rotateZ(180deg);
    transform: rotateZ(180deg);
    position: absolute;
    right: 0;
    top: 4px;
}

.filter-collapse.active .filter-collapse__caret {
    -webkit-transform: rotateZ(0);
    transform: rotateZ(0);
}
</style>