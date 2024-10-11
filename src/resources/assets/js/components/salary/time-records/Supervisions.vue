<template>
    <div class="supervisions">
        <el-table
            v-if="formData"
            :data="formData"
            border
            class="supervisions-table"
            style="width: 100%; padding-bottom: 10px"
        >
            <el-table-column type="index" width="50">
                <template slot-scope="scope">
                    <div class="column-content-center">
                        {{ scope.$index + 1 }}
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                width="200px"
                prop="provider_name"
                label="Provider"
            >
            </el-table-column>
            <el-table-column label="Supervision (hours)" prop="hours" width="200px">
                <template slot-scope="scope">
                    <el-input-number
                        v-model="scope.row.supervision_hours"
                        :min="0"
                        :max="6"
                        :controls="false"
                        class="form-field form-field-number"
                        :disabled="!isEditingAllowed"
                        @change="handleInputNumberChange(scope.$index)"
                    />
                </template>
            </el-table-column>
            <el-table-column label="Comment" prop="comment">
                <template slot-scope="scope">
                    <el-input
                        v-if="isSupervisor"
                        :id="'comment-textarea-' + scope.$index"
                        type="textarea"
                        rows="auto"
                        class="comment"
                        :class="{'is-error': invalidFields.includes(`comment.${scope.row.provider_id}`)}"
                        :disabled="!isEditingAllowed"
                        v-model="scope.row.comment"
                        @hook:mounted="updateTextareaHeight(scope.$index)"
                        @input="updateTextareaHeight(scope.$index)"
                        @blur="commentBlur"
                    />
                    <div v-else class="comment">{{ scope.row.comment || "-" }}</div>
                </template>
            </el-table-column>
        </el-table>
    </div>
</template>

<script>
export default {
    name: "Supervision",
    props: {
        initFormData: {
            type: Array,
            required: true,
        },
        changeData: {
            type: Function,
            required: true,
        },
        isEditingAllowed: {
            type: Boolean,
            default: true,
        },
        invalidFields: {
            type: Array,
            default() {
                return [];
            }
        },
    },
    data() {
        return {
            page: "timesheet",
            formData: null,
        };
    },
    watch: {
        initFormData: {
            handler(value) {
                if (!this.formData) {
                    const formData = [];
                    value.forEach((el) => {
                        formData.push({
                            provider_id: el.provider_id,
                            supervisor_id: el.supervisor_id,
                            provider_name: el.provider_name,
                            supervision_hours: el.supervision_hours,
                            comment: el.comment
                        });
                    });
                    this.formData = formData;
                }
            },
            deep: true,
        },
        formData: {
            handler(value) {
                this.changeData(value);
            },
            deep: true,
        },
    },
    mounted() {
        this.$store.dispatch('getUserRoles');
    },
    computed: {
        isSupervisor() {
            return this.$store.state.isUserSupervisor;
        },
        isUserAdmin() {
            return this.$store.state.isUserAdmin;
        },
        isUserSecretary() {
            return this.$store.state.isUserSecretary;
        },
        isRemainingSickHoursSet() {
            return (
                this.remainingSickHours !== null &&
                this.remainingSickHours !== undefined
            );
        },
        sickTimeMax() {
            if (!this.isEditingAllowed || !this.isRemainingSickHoursSet) {
                return Infinity;
            }

            return this.remainingSickHours;
        },
    },
    methods: {
        isInvalidField(checkboxName) {
            return (
                !this.formData[checkboxName] &&
                this.requiredFields.indexOf(checkboxName) > -1
            );
        },
        updateTextareaHeight(index) {
            this.$nextTick(() => {
                const textarea = document.querySelector(`#comment-textarea-${index}`);

                if (textarea) {
                    const minHeight = 40;
                    textarea.style.height = '1px';
                    textarea.style.minHeight = '40px';

                    const currentScrollHeight = textarea.scrollHeight;

                    if (textarea.offsetHeight < minHeight) {
                        textarea.style.height = `${minHeight}px`;
                    }

                    if (currentScrollHeight > textarea.offsetHeight) {
                        textarea.style.height = `${currentScrollHeight}px`;
                    }
                }
            });
        },
        commentBlur() {
            this.$emit('comment-blur');
        },
        handleInputNumberChange(index) {
            if (!this.formData[index].supervision_hours) {
                this.formData[index].supervision_hours = "0";
            }
        }
    },
};
</script>

<style lang="scss">
.supervisions-table {
    .el-table__row {
        vertical-align: top;
    }

    .cell {
        padding-top: 5px;
        padding-bottom: 5px;
    }

    .el-textarea__inner {
        height: 40px;
        min-height: 40px;
        line-height: 1.75;
        resize: none;
    }

    .el-textarea__inner:disabled {
        color: inherit;
        background-color: inherit;
    }

    .comment {
        width: 100%;
        word-break: break-word;
    }
    
    .el-input,
    .el-input-number,
    .el-select,
    .el-textarea {
        &.is-error {
            .el-input__inner, .el-textarea__inner {
                border: 1px solid #F56C6C;
            }
        }
    }
}
</style>
