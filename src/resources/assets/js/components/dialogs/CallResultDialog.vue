<template>
    <el-dialog :visible="isVisible" @close="closeModal" class="custom-dialog">
        <loading-overlay v-if="loading" />

        <div slot="title">
            Call Comment
        </div>

        <div>
            <el-form :model="callInfo" :rules="formRules" ref="callResultForm">
                <el-form-item prop="comment" class="hide-required-icon">
                    <el-input type="textarea" v-model="callInfo.comment" rows="4" placeholder="Add your comment..." />
                </el-form-item>
            </el-form>
        </div>

        <div slot="footer">
            <el-button type="primary" @click="saveClick">Save</el-button>
        </div>
    </el-dialog>
</template>

<script>
export default {
    props: {
        isVisible: {
            type: Boolean,
            required: true,
        },
        handleClose: {
            type: Function,
            required: true,
        }
    },

    data() {
        return {
            callInfo: {
                comment: "",
            },
            formRules: {
                comment: [
                    { required: true, message: "The comment field is required", trigger: "change" },
                ],
            },
            loading: false,
        }
    },

    methods: {
        saveClick() {
            this.loading = true;

            this.$refs.callResultForm.validate(valid => {
                if (!valid) {
                    this.loading = false;

                    return;
                }
                this.updateCallInfo();
            });
        },

        updateCallInfo() {
            this.$store.dispatch('updateCallLog', this.callInfo)
                .then(() => {
                    this.closeModal();
                }).finally(() => {
                    this.loading = false;
                });
        },

        closeModal() {
            this.resetData();
            this.handleClose();
        },

        resetData() {
            this.callInfo = {
                comment: "",
            };
            this.$refs.callResultForm.resetFields();
        }
    }
}
</script>

<style lang="scss"></style>