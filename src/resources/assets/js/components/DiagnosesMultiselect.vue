<template>
    <multiselect
        :class="customClass"
        v-model="selected_diagnoses"
        :options="diagnoses"
        label="full_name"
        track-by="id"
        :multiple="true"
        :searchable="true"
        :loading="is_diagnoses_loading"
        :internal-search="false"
        :showLabels="false"
        :max="maxLength"
        :placeholder="placeholder"
        @search-change="searchDiagnoses"
    >
        <span slot="noResult">No diagnoses found.</span>
    </multiselect>
</template>

<script>
    import Multiselect from 'vue-multiselect';

    export default {
        components: { Multiselect },

        props: {
            selectedDiagnoses: {
                type: Array,
                required: true,
            },
            placeholder: {
                type: String,
                default: '',
            },
            customClass: {
                type: String,
                default: '',
            },
            maxLength: {
                type: Number,
                default: null,
            }
        },

        data() {
            return {
                is_diagnoses_loading: false,
                selected_diagnoses: this.selectedDiagnoses,
                diagnoses: [],
            };
        },

        mounted() {
            this.searchDiagnoses();
        },

        methods: {
            searchDiagnoses(query = '') {
                this.is_diagnoses_loading = true;
                axios.get('/api/system/diagnoses/autocomplete?q=' + query)
                    .then(response => {
                        this.diagnoses = response.data.diagnoses || [];
                    })
                    .catch(() => {
                        //
                    })
                    .finally(() => {
                        this.is_diagnoses_loading = false;
                    });
            },
        },

        watch: {
            selected_diagnoses() {
                this.$emit('setDiagnoses', this.selected_diagnoses);
            },
            selectedDiagnoses() {
                this.selected_diagnoses = this.selectedDiagnoses || [];
            },
        }
    }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

<style scoped></style>