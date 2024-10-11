<template>
    <div class="requested-documents" :class="{'is-compact': compact}" v-if="patientForms.length > 0">
        <h3 class="requested-documents__title" v-if="title">
            {{ title }}
        </h3>
        <ol class="requested-documents__list" >
            <template v-for="(form, index) in patientForms">
                <li :key="form.type ? `${form.type.name}-${index}` : `${form.name}-${index}`" class="requested-documents__list-item list-item">
                    <div class="list-item__title">
                                <span class="list-item__index">
                                    {{ index + 1 }}
                                </span>
                        <p class="list-item__title-text">
                            {{ form.type ? form.type.title : form.title }}
                            <span v-if="form.type && !form.type.patient_can_skip_form" class="text-red"> *</span>
                        </p>
                    </div>

                    <ul class="unordered"
                        v-if="(form.type && form.type.name || form.name) === 'confidential_information' && form.metadata && form.metadata.exchange_with">
                        <li v-for="(key, index) in form.metadata.exchange_with" :key="`${key}-${index}`">
                            {{ key }}
                        </li>
                    </ul>
                    <ul class="unordered"
                        v-if="(form.type && form.type.name || form.name) === 'supporting_documents'">
                        <li v-for="(key, index) in form.metadata.documents" :key="`${key}-${index}`">
                            {{ key }}
                        </li>
                    </ul>
                </li>
            </template>
        </ol>
    </div>
</template>

<script>
    export default {
        name: "RequestedDocuments",
        props: {
            patientForms: {
                type: Array,
                default: [],
            },
            title: {
                type: String,
            },
            compact: {
                type: Boolean,
            }
        }
    }
</script>

<style scoped>

</style>