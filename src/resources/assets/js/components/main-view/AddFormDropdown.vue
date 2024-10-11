<template>
    <div class="btn-group view-note pull-right">
        <div class="dropdown">
            <button
                class="btn btn-warning add-note-btn dropdown-toggle patient-info-block-btn"
                type="button"
                data-submenu
                data-toggle="dropdown"
                @click="initSubmenu" 
            >
                Add Form
            </button>

            <ul class="dropdown-menu pull-right">
                <li
                    v-for="(formTemplate, index) in filteredFormTemplates"
                    :key="`${formTemplate.title}-${index}`"
                    class="dropdown-submenu"
                >
                    <a v-if="firstLevelFormList.includes(formTemplate.title)" href="#">
                        {{ formTemplate.title }}
                    </a>

                    <ul class="dropdown-menu">
                        <li
                            v-for="(submenu1, index) in formTemplate.childs"
                            :key="`${submenu1.title}-${index}`"
                            :class="{'dropdown-submenu': !submenu1.uri && !submenu1.slug}"
                        >
                            <a v-if="!submenu1.slug" href="#">
                                {{ submenu1.title }}
                            </a>
                            <a v-else href="#" @click.prevent="showDocument(submenu1.slug)">
                                {{ submenu1.title }}
                            </a>
                            <ul class="dropdown-menu">
                                <li v-if="formTemplate.title == 'Initial Assessment' && submenu1.title == 'Kaiser'">
                                    <a href="#" style="color:red;cursor:default;" @click.stop.prevent="">
                                        Kaiser requires Initial Assessments to be filed in the Lucet (Tridiuum) system. <br/>
                                        Within 24 hours after filing, IA forms will be syncronized with our EHR. <br/>
                                        You must file initial assessment no later than 72 hours after the date of service.
                                    </a>
                                </li>

                                <li v-else-if="formTemplate.title == 'Discharge Summary' && submenu1.title == 'Kaiser'">
                                    <a href="#" style="color:red;cursor:default;" @click.stop.prevent="">
                                        Kaiser requires Discharge Summaries to be filed in the Lucet (Tridiuum) system. <br/>
                                        Within 24 hours after filing, DS forms will be syncronized with our EHR.
                                    </a>
                                </li>

                                <li v-else v-for="(submenu2, index) in submenu1.childs" :key="`${submenu2.title}-${index}`">
                                    <a href="#" @click.prevent="showDocument(submenu2.slug)">
                                        {{ submenu2.title }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            formTemplates: {
                type: Object,
                default: () => ({}),
            }
        },
        data() {
            return {
                firstLevelFormList: [
                    'Initial Assessment',
                    'Discharge Summary',
                    'Additional Forms',
                    'Request for Reauthorization'
                ]
            };
        },
        computed: {
            filteredFormTemplates() {
                return this.filterDeletedFormTemplates(this.formTemplates);
            }
        },
        methods: {
            initSubmenu() {
                $('[data-submenu]').submenupicker();
            },
            showDocument(slug) {
                this.$emit('show-document', slug);
            },
            filterDeletedFormTemplates(templates) {
                let filteredTemplates = [];

                Object.values(templates).forEach((item) => {
                    if (item.deleted_at) {
                        return;
                    }

                    if (item.childs) {
                        item.childs = this.filterDeletedFormTemplates(item.childs);
                    }

                    filteredTemplates.push(item);
                });

                return filteredTemplates;
            }
        },
    }
</script>

<style lang="scss" scoped>
    .add-note-btn {
        width: 178px;
    }

    .dropdown-menu {
        z-index: 10000;
    }

    @media (min-width: 768px) {
        .dropdown-submenu .dropdown-menu {
            right: 100%;
            left: auto;
        }
    }
</style>

