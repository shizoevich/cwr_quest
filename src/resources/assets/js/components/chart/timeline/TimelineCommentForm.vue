<template>
    <div class="patient-document-comments">
        <form class="form-inline" @submit.prevent>
            <div class="form-group">
                <div
                    ref="input"
                    :id="formId"
                    class="form-control document-comment-input"
                    :class="{error: isError}"
                    contenteditable="true"
                    @keydown.enter.prevent=""
                >
                </div>
                <button type="button" class="btn btn-primary" @click.prevent="storeComment()">
                    Add comment
                </button>

                <span v-if="isError" class="error">{{ errorMessage }}</span>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        name: 'timeline-comment-form',
        props: {
            isError: Boolean,
            errorMessage: String,
            formId: String,
        },
        data() {
            return {
                comment: '',
            }
        },
        methods: {
            storeComment() {
                if (!this.$refs || !this.$refs.input) {
                    return;
                }

                const value = this.$refs.input.innerHTML;
                this.$emit('input', value);
                this.comment = '';
                this.$refs.input.innerHTML = '';
            },
        }
    }
</script>

<style>
    .patient-document-comments .form-group {
        width: 100%;
    }

    .patient-document-comments .form-group input {
        width: calc(100% - 123px);
    }

    .error {
        color: red;
        border-color: red;
    }

    @media (max-width: 1100px) {
        .patient-document-comments .form-group input {
            width: 100%;
        }

        .patient-document-comments .form-group button {
            margin: 10px auto;
        }
    }

    .document-comment-input {
        width: calc(100% - 125px) !important;
    }


</style>
