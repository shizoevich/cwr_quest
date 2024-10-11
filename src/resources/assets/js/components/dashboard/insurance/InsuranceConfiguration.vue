<template>
    <el-tabs tab-position="left" type="border-card" class="insurance-configuration" :before-leave="scrollToTop">
        <el-tab-pane v-for="insurance in insurances" :label="insurance.insurance" v-bind:key="insurance.id" >
            <insurance-plans :plans="insurance.plans"></insurance-plans>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import InsurancePlans from "./InsurancePlans";
import VueScrollTo from "vue-scrollto";

export default {
    data() {
        return {
            insurances: []
        };
    },

    components: {InsurancePlans},
    beforeMount() {
        axios.get('/api/system/insurances').then(response => {
            this.insurances = response.data.insurances;
        });
    },

    methods: {
        scrollToTop() {
            VueScrollTo.scrollTo(document.querySelector("html"), 1000, {
                container: "body",
                duration: "1000",
                easing: "ease",
                offset: 0,
                force: true,
            });
        },
    },
}
</script>

<style lang="scss" scoped>
.insurance-configuration {
    display: flex;
    overflow: visible;

    .el-tabs__header {
        float: none
    }
}
</style>