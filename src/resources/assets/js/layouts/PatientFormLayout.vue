<template>
    <div class="patient-form patient-form-start">
        <div class="patient-form__header">
            <img
                    class="patient-form__header-logo"
                    src="/images/cwr-logo.png"
                    alt="CWR logo"
                    title="Change Within Reach"
            />
        </div>
        <div class="patient-form__body" :class="{ loading: loading }">
            <div class="container">
                <template v-if="loading">
                    <pageloader class="loader"/>
                </template>
                <template v-else-if="isExpired || expired">
                    <div class="alert alert-danger" role="alert">
                        The link has expired.
                    </div>
                </template>
                <template v-else-if="!hash_valid">
                    <div class="alert alert-danger" role="alert">
                        Documents not found.
                    </div>
                </template>
                <template v-else>
                    <slot
                            name="content"
                            v-if="(patientForms && patientForms.length > 0) || $route.name === 'secure-download-forms'"
                    ></slot>
                    <template v-else>
                        <div class="alert alert-danger" role="alert">
                            By this request there are no documents to fill in. Please, check
                            the link in the email or request the documents once again.
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "PatientFormLayout",
        data: () => ({
            loading: false,
            expiring_at: null,
            hash_valid: true,
            expired: false,
        }),
        computed: {
            patientForms() {
                return this.$store.state.patientForms;
            },
            isExpired() {
                if (!this.expiring_at) {
                    return false;
                }

                return this.expiring_at.isBefore(this.$moment());
            },
        },
        methods: {
            getPatientFormsData() {
                this.loading = true;
                this.$store
                    .dispatch("getPatientFormsData", {
                        hash: this.$router.currentRoute.params.hash,
                    })
                    .then((response) => {
                        this.expiring_at = this.$moment(
                            response.data.document_request.expiring_at
                        );
                        this.$emit("loaded", response.data.document_request);
                        this.loading = false;
                    })
                    .catch(() => {
                        this.loading = false;
                    });
            },
            getSecuredPatientFormsData(password) {
                this.loading = true;
                this.$emit("invalidPassword", false);
                this.$store
                    .dispatch("getSecuredPatientFormsData", {
                        hash: this.$router.currentRoute.params.hash,
                        password: password
                    })
                    .then((response) => {
                        this.expiring_at = this.$moment(
                            response.data.document_request.expiring_at
                        );
                        this.$emit("loaded", response.data.document_request);
                        this.$emit("showDownloadPage");
                    })
                    .catch((e) => {
                        if (e.response.status === 401) {
                            this.$emit("invalidPassword", true);
                        }
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
            checkHash() {
                this.loading = true;
                this.$store
                    .dispatch("checkSharedHash", {
                        hash: this.$router.currentRoute.params.hash,
                    })
                    .then((response) => {
                        this.hash_valid = true;
                        this.expired = response.data.expired;
                    }).catch((e) => {
                    this.hash_valid = false;
                })
                    .finally(() => {
                        this.loading = false;
                    });
            },
        },
        beforeMount() {
            if (this.$route.name === 'secure-download-forms') {
                this.checkHash();
            } else {
                this.getPatientFormsData();
            }
        },
        mounted() {
            document.querySelector("body").style.backgroundColor = "#ffffff";
        },
    };
</script>

<style scoped></style>
