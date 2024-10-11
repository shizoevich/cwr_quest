<template>
    <provider-profile-checkbox
        class="form-group"
        label="Is Associate"
        :checked="checkboxChecked"
        :disabled="checkboxDisabled"
        :error="checkboxError"
        @change="change"
    ></provider-profile-checkbox>
</template>

<script>
import ProviderProfileCheckbox from "./ProviderProfileCheckbox";

export default {
    props: {
        providerId: {
            type: [String, Number]
        },
        providerName: {
            type: String,
        },
        checked: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        }
    },

    components: {
        ProviderProfileCheckbox
    },

    data() {
        return {
            checkboxChecked: false,
            checkboxDisabled: false,
            checkboxError: '',
        }
    },

    watch: {
        checked() {
            this.checkboxChecked = this.checked;
        },
        disabled() {
            this.checkboxDisabled = this.disabled;
        }
    },

    mounted() {
        this.checkboxChecked = this.checked;
        this.checkboxDisabled = this.disabled;
    },

    methods: {
        change(val) {
            this.$confirm(`Are you sure you want to ${val ? 'enable' : 'disable'} "Is Associate" status for ${this.providerName}?`, 'Warning', {
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                type: 'warning'
            })
                .then(() => {
                    this.checkboxDisabled = true;
                    this.checkboxError = '';
                    
                    const data = {
                        isAssociate: val,
                        providerId: this.providerId,
                    };

                    axios({
                        method: 'post',
                        url: '/dashboard/doctors/is-associate',
                        data: data,
                    })
                        .then(() => {
                            this.checkboxChecked = val;
                        })
                        .catch((error) => {
                            if (!error.response) {
                                return;
                            }
                            if (error.response.status === 401) {
                                window.location.href = window.location.href;
                                return;
                            }
                            let message = 'Whoops, looks like something went wrong.';
                            if (error.response.data.providerId) {
                                message = error.response.data.providerId[0];
                            } else if (error.response.data.userId) {
                                message = error.response.data.userId[0];
                            }
                            this.checkboxError = message;
                        })
                        .finally(() => {
                            this.checkboxDisabled = false;
                        });
                })
                    .catch(() => {
                        //
                    });
        },
    }
}
</script>