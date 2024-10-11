<template>
    <div class="form-list-input">
        <div class="form-list-input__field" :class="{'has-error': hasError}">
            <input
                class="form-control long"
                v-model="inputValue"
                :id="id"
                type="text"
                :placeholder="placeholder"
                :name="name"
                :disabled="disabled"
                @keydown.enter.prevent="addListItem"
            />
            <button
                class="form-list-input__btn"
                :class="{'hidden': !inputValue}"
                type="button"
                @click.prevent="addListItem"
                :disabled="disabled"
            >
                <img src="/images/icons/icon-add.svg" alt="plus icon">
            </button>
        </div>
        <form-tag-list
            :tags="listData"
            @remove="removeFromListById"
        />
    </div>
</template>

<script>
    import FormTagList from "./FormTagList";

    export default {
        name: "FormListInput",
        components: {
            FormTagList,
        },
        props: {
            placeholder: {
                type: String,
            },
            id: {
                type: [String, Number],
            },
            name: {
                type: String,
            },
            list: {
                type: Array,
            },
            disabled: {
                type: Boolean,
            },
            hasError: {
                type: Boolean,
            }
        },
        data: () => ({
            listData: [],
            inputValue: '',
        }),
        watch: {
            list() {
                this.listData = this.list;
            },
            listData() {
                this.$emit('change', this.listData);
            }
        },
        computed: {
            maxId() {
                if (this.listData.length < 1) {
                    return 0;
                }
                let idList = this.listData.map(item => item.id);
                return Math.max.apply(null, idList);
            }
        },
        methods: {
            removeFromListById(id) {
                let itemIndex = this.listData.findIndex((item) => item.id === id);
                this.listData.splice(itemIndex, 1);
            },
            addListItem() {
                if (this.inputValue) {
                    let nextId = this.maxId + 1;
                    this.listData.push({
                        id: nextId,
                        text: this.inputValue,
                    });
                    this.inputValue = '';
                }
            }
        },
        mounted() {
            this.listData = this.list;
        }
    }
</script>

<style scoped>

</style>