<template>
    <div style="display:inline-block; margin-right: 20px;">
        <label v-if="label">{{label}}</label>
        <select class="form-control provider-select" v-model="selected_provider_id">
            <option value="-1">All</option>
            <option v-for="provider in provider_list" :value="provider.id">{{provider.provider_name}}</option>
        </select>
    </div>
</template>

<script>
    export default {

        props: ['label'],

        created() {
            this.$store.dispatch('getProviderList');
            this.is_mounted_onchange = true;
            this.selected_provider_id = this.$parent.getSelectedProviderId();
        },

        data() {
            return {
                selected_provider_id: null,
                is_mounted_onchange: false
            }
        },

        computed: {
            provider_list() {
                return this.$store.state.provider_list;
            }
        },

        methods: {},

        watch: {
            selected_provider_id() {
                if(!this.is_mounted_onchange) {
                    this.$parent.setSelectedProviderId(this.selected_provider_id);
                } else {
                    this.is_mounted_onchange = false;
                }

            }
        }
    }
</script>

<style scoped>

</style>