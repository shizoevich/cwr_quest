export default {
    methods : {
        getProviderName(note) {
            let providerName = note.provider_name;
            if (providerName !== null && providerName !== undefined) {
                return providerName;
            } else if (note.full_admin_name !== null && note.full_admin_name !== undefined) {
                return note.full_admin_name;
            } else if (note.firstname && note.lastname) {
                return `${note.firstname} ${note.lastname}`;
            }
            return 'Admin';
        },
    },
    computed: {
        provider() {
            return this.$store.state.currentProvider;
        }
    },
}